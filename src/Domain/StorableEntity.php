<?php

namespace Gebruederheitz\Wordpress\Domain;

use WP_Post;

interface StorableEntity
{
    public function __construct(WP_Post $post = null, $meta = []);

    public function getPostId(): ?int;

    public function toMetaValues(): array;
}
