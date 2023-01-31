<?php declare(strict_types=1);

namespace FetchApp\Tests;

use FetchApp\API\FetchApp;

final class APITest extends FetchAppBaseTest
{
    public function testClass(): void
    {
        $fetch = self::$fetch;
        $this->assertInstanceOf(
            FetchApp::class,
            $fetch
        );
    }
}
