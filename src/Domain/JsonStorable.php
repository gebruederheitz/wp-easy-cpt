<?php

namespace Gebruederheitz\Wordpress\Domain;

interface JsonStorable
{
    public function jsonStringify(): string;

    public static function fromJson(string $json): JsonStorable;
}
