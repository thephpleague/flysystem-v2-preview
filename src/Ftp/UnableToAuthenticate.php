<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

use RuntimeException;

final class UnableToAuthenticate extends RuntimeException implements FtpConnectionException
{
    public function __construct()
    {
        parent::__construct("Unable to login/authenticate with FTP");
    }
}
