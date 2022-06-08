<?php

namespace Gebruederheitz\Wordpress\Domain;

/**
 * @phpstan-template T of StorableEntity
 * @template T of StorableEntity
 */
interface EntityRepository
{
    /** @return T|null */
    public function find(?int $postId, bool &$isDirty = null);

    /** @return array<T> */
    public function findAll(): array;

    /**
     * @param T $item
     *
     * @return EntityRepository<T>
     */
    public function save(StorableEntity $item): EntityRepository;

    /**
     * @return EntityRepository<T>
     */
    public function flush(): EntityRepository;

    /**
     * @return EntityRepository<T>
     */
    public function refresh(): EntityRepository;
}
