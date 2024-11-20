<?php declare(strict_types = 1);

namespace Tests\TestFixtures;

use UlovDomov\TestFixtures\TestCases\BaseUnitTestCase;

final class BasicTest extends BaseUnitTestCase
{
    protected function setUp(): void
    {
        self::setTempDir(__DIR__ . '/../../../temp');
        self::setLogDir(__DIR__ . '/../../../log');

        parent::setUp();
    }

    public function testBasic(): void
    {
        self::assertTrue(true);
    }
}
