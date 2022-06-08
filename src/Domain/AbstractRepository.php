<?php

namespace Gebruederheitz\Wordpress\Domain;

use Gebruederheitz\SimpleSingleton\Singleton;
use Gebruederheitz\SimpleSingleton\SingletonInterface;
use WP_Post;
use Throwable;

/**
 * @phpstan-template T of StorableEntity
 * @template T of StorableEntity
 * @implements EntityRepository<T>
 * @phpstan-type ResultArray array<int, array{item: T, dirty: bool}>
 */
abstract class AbstractRepository extends Singleton implements
    EntityRepository,
    SingletonInterface
{
    /** @var string */
    public static $metaKey;

    /** @var ResultArray */
    protected $entities = [];

    /** @var class-string<T> FQCN of an entity class implementing StorableEntity */
    protected static $entityClass;

    /** @var bool */
    protected $hasRetrievedAll = false;

    /**
     * Retrieves a single entity item from the cache or database by its post ID.
     *
     * @param int|null $postId      The entity's associated post ID.
     * @param null     $isDirty     Will be set to true if the item has changes
     *                              that have not been persisted to the database
     *                              yet, false otherwise.
     *
     * @return T|null               The StorableEntity instance for the given
     *                              post ID on success, null otherwise.
     */
    public function find(?int $postId, &$isDirty = null): ?StorableEntity
    {
        return static::getById($postId, $isDirty);
    }

    /**
     * Retrieves all entity items from the cache or database.
     *
     * @return array<T>
     */
    public function findAll(): array
    {
        if (!$this->hasRetrievedAll) {
            $entities = $this->getAllFromDB();

            foreach ($entities as $id => $entity) {
                if (!isset($this->entities[$id])) {
                    $this->entities[$id] = $entity;
                }
            }
            $this->hasRetrievedAll = true;
        }

        return array_column($this->entities, 'item');
    }

    /**
     * Saves a StorableEntity to the repository cache and marks it as dirty. You
     * will need to explicitly call flush() to store it in the database.
     *
     * @param T $item  The RaceItem to be created/updated.
     *
     * @return $this Returns itself to allow chaining.
     */
    public function save(StorableEntity $item): EntityRepository
    {
        $id = $item->getPostId();
        $this->entities[$id] = [
            'dirty' => true,
            'item' => $item,
        ];

        return $this;
    }

    /**
     * Persists all changed items from the cache to the database. Returns itself
     * to allow method chaining.
     *
     * @return $this
     */
    public function flush(): EntityRepository
    {
        $dirtyEntries = array_filter($this->entities, function ($entity) {
            return $entity['dirty'] === true;
        });

        foreach ($dirtyEntries as $entry) {
            $this->persist($entry['item']);
            $entry['dirty'] = false;
        }

        return $this;
    }

    /**
     * Re-retrieve all items from the database. Any dirty items in the local
     * cache will be discarded – so make sure you call flush() for any relevant
     * changes before using this method.
     * Returns itself to allow method chaining.
     *
     * @return $this
     */
    public function refresh(): EntityRepository
    {
        $this->entities = static::getAllFromDB();
        $this->hasRetrievedAll = true;

        return $this;
    }

    /**
     * Retrieve all posts fetched by getPosts() directly from the database and
     * instantiate them into StorableEntities using entityFromPostId().
     *
     * @return ResultArray
     */
    protected static function getAllFromDB(): array
    {
        $result = [];

        $posts = static::getPosts();

        foreach ($posts as $post) {
            $entity = static::entityFromPostId($post->ID, $post);

            $result[$post->ID] = [
                'item' => $entity,
                'dirty' => false,
            ];
        }

        return $result;
    }

    /**
     * Helper to retrieve the post meta fields (array format) from the DB via
     * the post's ID.
     *
     * @return array<string, mixed>
     */
    protected static function getMetaValues(int $postId): array
    {
        $meta = get_post_meta($postId, static::$metaKey, true) ?? [];
        if (!is_array($meta)) {
            $meta = [];
        }
        return $meta;
    }

    /**
     * Retrieve a single StorableEntity from the cache if it exists or
     * otherwise from the database by its post ID by fetching the post and the
     * post meta and instantiating the Entity class.
     *
     * @param int|null $postId      The post ID of the entity.
     * @param null     $isDirty     Will be set to true if the item has changes
     *                              that have not been persisted to the database
     *                              yet, false otherwise.
     *
     * @return T|null               The StorableEntity instance for the given
     *                              post ID on success, null otherwise.
     */
    protected function getById(?int $postId, &$isDirty = null): ?StorableEntity
    {
        if (!$postId) {
            $isDirty = true;
            return new static::$entityClass();
        }

        if (isset($this->entities[$postId])) {
            $entry = $this->entities[$postId];
            $isDirty = $entry['dirty'];

            return $this->entities[$postId]['item'];
        } else {
            $isDirty = false;

            try {
                $entity = static::entityFromPostId($postId);
                $this->entities[$postId] = [
                    'item' => $entity,
                    'dirty' => false,
                ];

                return $entity;
            } catch (Throwable $e) {
                error_log(
                    'Failed to fetch entity in repository ' .
                        static::class .
                        ' from DB for post id ' .
                        $postId,
                );
                error_log('Original error message: ' . $e->getMessage());
                $entity = new static::$entityClass();
                $entity->setPostId($postId);
                return $entity;
            }
        }
    }

    /**
     * Retrieve all posts that will be processed in getAllFromDB(). Use this
     * method to fetch the list of posts relevant to you.
     * If you're using a custom post type with
     * Gebruederheitz\Wordpress\CustomPostType\PostType this method has a basic
     * implementation in CustomPostTypeRepository.
     *
     * @return array<WP_Post>
     */
    abstract protected static function getPosts(): array;

    /**
     * Instantiates an entity instance from a post ID.
     *
     * @param int $postId
     * @param ?WP_Post $post
     *
     * @return T An instance of the entity type defined in static::$entityClass
     */
    protected static function entityFromPostId(
        int $postId,
        WP_Post $post = null
    ) {
        if (empty($post)) {
            $post = get_post($postId);
        }

        $meta = static::getMetaValues($postId);

        return new static::$entityClass($post, $meta);
    }

    /**
     * Save a single StorableEntity's meta fields to the database.
     *
     * @param T $item The StorableEntity to be persisted to the DB.
     */
    protected function persist(StorableEntity $item): void
    {
        update_post_meta(
            $item->getPostId(),
            static::$metaKey,
            $item->toMetaValues(),
        );
    }
}
