<?php

declare(strict_types=1);

namespace App;

interface Storage
{
    public function write(iterable $versions, iterable $releases): void;
}
