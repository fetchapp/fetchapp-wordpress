<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;
use FetchApp\API\FileDetail;
use PHPUnit\Framework\TestCase;

final class FileTest extends FetchAppBaseTest
{
    public static $baseClass = FileDetail::class;

    public function testClass(): void
    {
        $item = self::testSingle();
        
        $this->assertInstanceOf(
            self::$baseClass,
            $item
        );
    }

    public function testSingle(): FileDetail
    {
        $fetch = self::$fetch;
        $files = $fetch->getFiles(); // Grabs all files

        $this->assertIsArray($files);
        $this->assertNotEmpty($files);

        $file = array_pop($files);

        $this->assertInstanceOf(
            self::$baseClass,
            $file
        );
        return $file;
    }

    public function testList(): void
    {
        $fetch = self::$fetch;

        $files = $fetch->getFiles(); // Grabs all files

        $this->assertIsArray($files);
        $this->assertNotEmpty($files);

        $files = $fetch->getFiles(2, 3); // Grabs files, 2 per page, page 3.

        $this->assertIsArray($files);
        $this->assertNotEmpty($files);
        $this->assertCount(2, $files);
    }

    public function testProductFiles(): void
    {
        $product = $this->_getTestProduct();

        $files = $product->getFiles();
        $this->assertIsArray($files);
        $this->assertNotEmpty($files);

        $this->assertInstanceOf(
            self::$baseClass,
            $files[0]
        );
    }

    public function testOrderItemFiles(): void
    {
        $item = $this->_getTestOrderItem();

        $files = $item->getFiles();
        $this->assertIsArray($files);
        $this->assertNotEmpty($files);

        $this->assertInstanceOf(
            self::$baseClass,
            $files[0]
        );
    }
}
