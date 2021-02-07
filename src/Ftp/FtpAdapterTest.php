<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\Ftp;

use League\FlysystemV2Preview\FilesystemAdapter;

/**
 * @group ftp
 */
class FtpAdapterTest extends FtpAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        $options = FtpConnectionOptions::fromArray([
           'host' => 'localhost',
           'port' => 2121,
           'timestampsOnUnixListingsEnabled' => true,
           'root' => '/home/foo/upload/',
           'username' => 'foo',
           'password' => 'pass',
       ]);

        static::$connectivityChecker = new ConnectivityCheckerThatCanFail(new NoopCommandConnectivityChecker());

        return new FtpAdapter($options, null, static::$connectivityChecker);
    }
}
