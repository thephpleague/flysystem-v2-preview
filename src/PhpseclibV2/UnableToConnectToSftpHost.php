<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\PhpseclibV2;

use League\FlysystemV2Preview\FilesystemException;
use RuntimeException;

class UnableToConnectToSftpHost extends RuntimeException implements FilesystemException
{
    public static function atHostname(string $host): UnableToConnectToSftpHost
    {
        return new UnableToConnectToSftpHost("Unable to connect to host: $host");
    }
}
