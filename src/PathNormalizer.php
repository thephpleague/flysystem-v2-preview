<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview;

interface PathNormalizer
{
    public function normalizePath(string $path): string;
}
