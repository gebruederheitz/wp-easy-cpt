<?php

namespace Gebruederheitz\Wordpress\Domain;

use WP_Post;

class CustomPostTypeRepository extends AbstractRepository
{
    public static $metaKey;

    /** @var StorableEntity[] */
    protected $entities = [];

    /** @var string FQCN of an entity class implementing StorableEntity */
    protected static $entityClass;

    /** @var string FQCN of a post type class implementing PostTypeInterface */
    protected static $postTypeClass;

    /** @var bool */
    protected $hasRetrievedAll = false;

    /**
     * Retrieve all posts that will be processed in getAllFromDB(). By default,
     * it retrieves all posts matching static::$postTypeClass. If you're not
     * using this repository for custom post types need custom filtering or
     * ordering etc., you should override this method the fetch the list of
     * posts relevant to you
     *
     * @return WP_Post[]
     */
    protected static function getPosts(): array
    {
        return get_posts(
            [
                'post_type' => call_user_func([static::$postTypeClass, 'getPostTypeName']),
                'numberposts' => -1,
            ]
        );
    }
}
