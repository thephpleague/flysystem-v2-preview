<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

interface ConnectivityChecker
{
    /**
     * @param resource $connection
     */
    public function isConnected($connection): bool;
}
