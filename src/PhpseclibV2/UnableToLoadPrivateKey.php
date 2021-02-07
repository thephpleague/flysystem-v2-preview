<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\PhpseclibV2;

use League\FlysystemV2Preview\FilesystemException;
use RuntimeException;

class UnableToLoadPrivateKey extends RuntimeException implements FilesystemException
{
    public function __construct(string $message = "Unable to load private key.")
    {
        parent::__construct($message);
    }
}
