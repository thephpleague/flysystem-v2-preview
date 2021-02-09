<?php

declare(strict_types=1);

namespace League\FlysystemV2Preview;

use PHPUnit\Framework\TestCase;

class DirectoryAttributesTest extends TestCase
{
    /**
     * @test
     */
    public function exposing_some_values(): void
    {
        $attrs = new DirectoryAttributes('some/path');
        $this->assertTrue($attrs->isDir());
        $this->assertFalse($attrs->isFile());
        $this->assertEquals(StorageAttributes::TYPE_DIRECTORY, $attrs->type());
        $this->assertEquals('some/path', $attrs->path());
        $this->assertNull($attrs->visibility());
    }

    /**
     * @test
     */
    public function exposing_visibility(): void
    {
        $attrs = new DirectoryAttributes('some/path', Visibility::PRIVATE);
        $this->assertEquals(Visibility::PRIVATE, $attrs->visibility());
    }

    /**
     * @test
     */
    public function exposing_last_modified(): void
    {
        $attrs = new DirectoryAttributes('some/path', null, $timestamp = time());
        $this->assertEquals($timestamp, $attrs->lastModified());
    }

    /**
     * @test
     */
    public function serialization_capabilities(): void
    {
        $attrs = new DirectoryAttributes('some/path');
        $payload = $attrs->jsonSerialize();
        $attrsFromPayload = DirectoryAttributes::fromArray($payload);
        $this->assertEquals($attrs, $attrsFromPayload);
    }
}
