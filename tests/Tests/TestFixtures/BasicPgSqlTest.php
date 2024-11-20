<?php declare(strict_types = 1);

namespace Tests\TestFixtures;

use Nette\Bootstrap\Configurator;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Libraries\TestBootstrap;
use UlovDomov\TestFixtures\TestCases\BaseDatabaseTestCase;

final class BasicPgSqlTest extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        self::setTempDir(__DIR__ . '/../../../temp');
        self::setLogDir(__DIR__ . '/../../../log');

        parent::setUp();
    }

    #[RunInSeparateProcess]
    public function testBasic(): void
    {
        self::assertTrue(false);
    }

    protected function createConfigurator(): Configurator
    {
        return TestBootstrap::boot();
    }
}
