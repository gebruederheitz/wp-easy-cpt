<?php

namespace Gebruederheitz\Wordpress\Domain;

interface EntityRepository
{
    public function find(?int $postId, &$isDirty = null): ?StorableEntity;

    /**
     * @return StorableEntity[]
     */
    public function findAll(): array;

    public function save(StorableEntity $item): EntityRepository;

    public function flush(): EntityRepository;

    public function refresh(): EntityRepository;
}
