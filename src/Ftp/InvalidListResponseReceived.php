<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

use League\FlysystemV2Preview\FilesystemException;
use RuntimeException;

class InvalidListResponseReceived extends RuntimeException implements FilesystemException
{
}
