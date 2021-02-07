<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

use RuntimeException;

class UnableToMakeConnectionPassive extends RuntimeException implements FtpConnectionException
{
}
