<?php declare(strict_types = 1);

namespace Tests\TestFixtures;

use Nette\Bootstrap\Configurator;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Libraries\TestBootstrap;
use Tests\Libraries\TestService;
use UlovDomov\TestFixtures\TestCases\BaseDITestCase;

final class BasicDITest extends BaseDITestCase
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
        /** @var TestService $testService */
        $testService = $this->getService(TestService::class);
        self::assertSame('bar', $testService->getBar());
    }

    protected function createConfigurator(): Configurator
    {
        return TestBootstrap::boot();
    }
}
