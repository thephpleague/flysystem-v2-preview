<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\PhpseclibV2;

use League\FlysystemV2Preview\FilesystemException;
use RuntimeException;

class UnableToAuthenticate extends RuntimeException implements FilesystemException
{
    public static function withPassword(): UnableToAuthenticate
    {
        return new UnableToAuthenticate('Unable to authenticate using a password.');
    }

    public static function withPrivateKey(): UnableToAuthenticate
    {
        return new UnableToAuthenticate('Unable to authenticate using a private key.');
    }

    public static function withSshAgent(): UnableToAuthenticate
    {
        return new UnableToAuthenticate('Unable to authenticate using an SSH agent.');
    }
}
