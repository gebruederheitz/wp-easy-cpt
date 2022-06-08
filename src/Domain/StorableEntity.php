<?php

namespace Gebruederheitz\Wordpress\Domain;

use WP_Post;

interface StorableEntity
{
    /**
     * @param WP_Post|null $post
     * @param array<string, mixed> $meta
     */
    public function __construct(WP_Post $post = null, array $meta = []);

    public function getPostId(): ?int;

    /**
     * @return StorableEntity|void
     */
    public function setPostId(int $postId);

    /**
     * @return array<string, mixed>
     */
    public function toMetaValues(): array;
}
