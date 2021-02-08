<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview;

use InvalidArgumentException as BaseInvalidArgumentException;

class InvalidStreamProvided extends BaseInvalidArgumentException implements FilesystemException
{
}
