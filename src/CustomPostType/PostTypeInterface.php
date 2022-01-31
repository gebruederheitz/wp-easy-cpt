<?php

namespace Gebruederheitz\Wordpress\CustomPostType;

interface PostTypeInterface
{
    public static function getPostTypeName(): string;
    public static function getPrettyName(): string;
    public function usesGutenberg(): bool;
}
