<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

use RuntimeException;

final class UnableToEnableUtf8Mode extends RuntimeException implements FtpConnectionException
{
}
