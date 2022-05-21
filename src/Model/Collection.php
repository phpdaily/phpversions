<?php

declare(strict_types=1);

namespace App\Model;

use JDecool\Collection\Collection as BaseCollection;

/**
 * @template TKey
 * @template TValue
 */
class Collection
{
    public static function createEmpty(): self
    {
        return new self();
    }

    public static function fromIterable(iterable $data): self
    {
        $arr = [];
        foreach ($data as $key => $value) {
            $arr[$key] = $value;
        }

        return new self($arr);
    }

    private function __construct(
        private array $data = [],
    ) {
    }

    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function add($key, $value): void
    {
        $this->data[$key] = $value;
    }
}
