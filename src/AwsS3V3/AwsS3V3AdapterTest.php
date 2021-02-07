<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview\AwsS3V3;

use Aws\Result;
use Aws\S3\S3Client;
use Aws\S3\S3ClientInterface;
use Exception;
use Generator;
use League\FlysystemV2Preview\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\FlysystemV2Preview\Config;
use League\FlysystemV2Preview\FileAttributes;
use League\FlysystemV2Preview\FilesystemAdapter;
use League\FlysystemV2Preview\StorageAttributes;
use League\FlysystemV2Preview\UnableToCheckFileExistence;
use League\FlysystemV2Preview\UnableToDeleteFile;
use League\FlysystemV2Preview\UnableToMoveFile;
use League\FlysystemV2Preview\UnableToRetrieveMetadata;

/**
 * @group aws
 */
class AwsS3V3AdapterTest extends FilesystemAdapterTestCase
{
    /**
     * @var bool
     */
    private $shouldCleanUp = false;

    /**
     * @var string
     */
    private static $adapterPrefix = 'test-prefix';

    /**
     * @var S3ClientInterface|null
     */
    private static $s3Client;

    /**
     * @var S3ClientStub
     */
    private static $stubS3Client;

    public static function setUpBeforeClass(): void
    {
        static::$adapterPrefix = 'ci/' . bin2hex(random_bytes(10));
    }

    protected function tearDown(): void
    {
        if ( ! $this->shouldCleanUp) {
            return;
        }

        $adapter = $this->adapter();
        $adapter->deleteDirectory('/');
        /** @var StorageAttributes[] $listing */
        $listing = $adapter->listContents('', false);

        foreach ($listing as $item) {
            if ($item->isFile()) {
                $adapter->delete($item->path());
            } else {
                $adapter->deleteDirectory($item->path());
            }
        }

        self::$adapter = null;
    }

    private static function s3Client(): S3ClientInterface
    {
        if (static::$s3Client instanceof S3ClientInterface) {
            return static::$s3Client;
        }

        $key = getenv('FLYSYSTEM_AWS_S3_KEY');
        $secret = getenv('FLYSYSTEM_AWS_S3_SECRET');
        $bucket = getenv('FLYSYSTEM_AWS_S3_BUCKET');
        $region = getenv('FLYSYSTEM_AWS_S3_REGION') ?: 'eu-central-1';

        if ( ! $key || ! $secret || ! $bucket) {
            self::markTestSkipped('No AWS credentials present for testing.');
        }

        $options = ['version' => 'latest', 'credentials' => compact('key', 'secret'), 'region' => $region];

        return static::$s3Client = new S3Client($options);
    }

    /**
     * @test
     */
    public function writing_with_a_specific_mime_type(): void
    {
        $adapter = $this->adapter();
        $adapter->write('some/path.txt', 'contents', new Config(['ContentType' => 'text/plain+special']));
        $mimeType = $adapter->mimeType('some/path.txt')->mimeType();
        $this->assertEquals('text/plain+special', $mimeType);
    }

    /**
     * @test
     */
    public function listing_contents_recursive(): void
    {
        $adapter = $this->adapter();
        $adapter->write('something/0/here.txt', 'contents', new Config());
        $adapter->write('something/1/also/here.txt', 'contents', new Config());

        $contents = iterator_to_array($adapter->listContents('', true));

        $this->assertCount(2, $contents);
        $this->assertContainsOnlyInstancesOf(FileAttributes::class, $contents);
        /** @var FileAttributes $file */
        $file = $contents[0];
        $this->assertEquals('something/0/here.txt', $file->path());
        /** @var FileAttributes $file */
        $file = $contents[1];
        $this->assertEquals('something/1/also/here.txt', $file->path());
    }

    /**
     * @test
     */
    public function failing_to_delete_while_moving(): void
    {
        $adapter = $this->adapter();
        $adapter->write('source.txt', 'contents to be copied', new Config());
        static::$stubS3Client->failOnNextCopy();

        $this->expectException(UnableToMoveFile::class);

        $adapter->move('source.txt', 'destination.txt', new Config());
    }

    /**
     * @test
     */
    public function failing_to_delete_a_file(): void
    {
        $adapter = $this->adapter();
        static::$stubS3Client->throwExceptionWhenExecutingCommand('DeleteObject');

        $this->expectException(UnableToDeleteFile::class);

        $adapter->delete('path.txt');
    }

    /**
     * @test
     */
    public function fetching_unknown_mime_type_of_a_file(): void
    {
        $this->adapter();
        $result = new Result([
            'Key' => static::$adapterPrefix . '/unknown-mime-type.md5',
        ]);
        static::$stubS3Client->stageResultForCommand('HeadObject', $result);

        parent::fetching_unknown_mime_type_of_a_file();
    }

    /**
     * @test
     * @dataProvider dpFailingMetadataGetters
     */
    public function failing_to_retrieve_metadata(Exception $exception, string $getterName): void
    {
        $adapter = $this->adapter();
        $result = new Result([
             'Key' => static::$adapterPrefix . '/filename.txt',
        ]);
        static::$stubS3Client->stageResultForCommand('HeadObject', $result);

        $this->expectExceptionObject($exception);

        $adapter->{$getterName}('filename.txt');
    }

    public function dpFailingMetadataGetters(): iterable
    {
        yield "mimeType" => [UnableToRetrieveMetadata::mimeType('filename.txt'), 'mimeType'];
        yield "lastModified" => [UnableToRetrieveMetadata::lastModified('filename.txt'), 'lastModified'];
        yield "fileSize" => [UnableToRetrieveMetadata::fileSize('filename.txt'), 'fileSize'];
    }

    /**
     * @test
     */
    public function failing_to_check_for_file_existence(): void
    {
        $adapter = $this->adapter();

        static::$stubS3Client->throw500ExceptionWhenExecutingCommand('HeadObject');

        $this->expectException(UnableToCheckFileExistence::class);

        $adapter->fileExists('something-that-does-exist.txt');
    }

    /**
     * @test
     * @dataProvider casesWhereHttpStreamingInfluencesSeekability
     */
    public function streaming_reads_are_not_seekable_and_non_streaming_are(bool $streaming, bool $seekable): void
    {
        if (getenv('COMPOSER_OPTS') === '--prefer-lowest') {
            $this->markTestSkipped('The SDK does not support streaming in low versions.');
        }

        $adapter = $this->useAdapter($this->createFilesystemAdapter($streaming));
        $this->givenWeHaveAnExistingFile('path.txt');

        $resource = $adapter->readStream('path.txt');
        $metadata = stream_get_meta_data($resource);
        fclose($resource);

        $this->assertEquals($seekable, $metadata['seekable']);
    }

    public function casesWhereHttpStreamingInfluencesSeekability(): Generator
    {
        yield "not streaming reads have seekable stream" => [false, true];
        yield "streaming reads have non-seekable stream" => [true, false];
    }

    /**
     * @test
     * @dataProvider casesWhereHttpStreamingInfluencesSeekability
     */
    public function configuring_http_streaming_via_options(bool $streaming): void
    {
        $adapter = $this->useAdapter($this->createFilesystemAdapter($streaming, ['@http' => ['stream' => false]]));
        $this->givenWeHaveAnExistingFile('path.txt');

        $resource = $adapter->readStream('path.txt');
        $metadata = stream_get_meta_data($resource);
        fclose($resource);

        $this->assertTrue($metadata['seekable']);
    }

    protected static function createFilesystemAdapter(bool $streaming = true, array $options = []): FilesystemAdapter
    {
        static::$stubS3Client = new S3ClientStub(static::s3Client());
        /** @var string $bucket */
        $bucket = getenv('FLYSYSTEM_AWS_S3_BUCKET');
        $prefix = getenv('FLYSYSTEM_AWS_S3_PREFIX') ?: static::$adapterPrefix;

        return new AwsS3V3Adapter(static::$stubS3Client, $bucket, $prefix, null, null, $options, $streaming);
    }
}
