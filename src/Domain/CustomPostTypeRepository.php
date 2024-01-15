<?php

namespace Gebruederheitz\Wordpress\Domain;

use Gebruederheitz\Wordpress\CustomPostType\PostTypeInterface;
use WP_Post;

/**
 * @phpstan-template T of StorableEntity
 * @template T of StorableEntity
 * @extends AbstractRepository<T>
 */
class CustomPostTypeRepository extends AbstractRepository
{
    /** @var class-string<PostTypeInterface> FQCN of a post type class implementing PostTypeInterface */
    protected static $postTypeClass;

    /**
     * Retrieve all posts that will be processed in getAllFromDB(). By default,
     * it retrieves all posts matching static::$postTypeClass. If you're not
     * using this repository for custom post types need custom filtering or
     * ordering etc., you should override this method the fetch the list of
     * posts relevant to you
     *
     * @param ?array<string, mixed> $customQuery
     *
     * @return WP_Post[]
     */
    protected static function getPosts(?array $customQuery = null): array
    {
        $baseQuery = [
            'post_type' => call_user_func([
                static::$postTypeClass,
                'getPostTypeName',
            ]),
            'numberposts' => -1,
        ];

        if ($customQuery) {
            $query = array_merge($baseQuery, $customQuery);
        } else {
            $query = $baseQuery;
        }

        return get_posts($query);
    }
}
