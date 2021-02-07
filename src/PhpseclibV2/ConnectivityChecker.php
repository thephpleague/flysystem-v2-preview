<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\PhpseclibV2;

use phpseclib\Net\SFTP;

interface ConnectivityChecker
{
    public function isConnected(SFTP $connection): bool;
}
