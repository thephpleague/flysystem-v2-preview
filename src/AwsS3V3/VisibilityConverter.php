<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\AwsS3V3;

interface VisibilityConverter
{
    public function visibilityToAcl(string $visibility): string;
    public function aclToVisibility(array $grants): string;
    public function defaultForDirectories(): string;
}
