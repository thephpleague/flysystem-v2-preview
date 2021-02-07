<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

interface ConnectionProvider
{
    /**
     * @return resource
     */
    public function createConnection(FtpConnectionOptions $options);
}
