<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

use League\FlysystemV2Preview\FilesystemAdapter;

/**
 * @group ftpd
 */
class FtpdAdapterTest extends FtpAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $options = FtpConnectionOptions::fromArray([
           'host' => 'localhost',
           'port' => 2122,
           'timestampsOnUnixListingsEnabled' => true,
           'root' => '/',
           'username' => 'foo',
           'password' => 'pass',
       ]);

        static::$connectivityChecker = new ConnectivityCheckerThatCanFail(new NoopCommandConnectivityChecker());

        return new FtpAdapter($options, null, static::$connectivityChecker);
    }
}
