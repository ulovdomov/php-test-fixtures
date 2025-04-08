<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\TestCases;

use App\Bootstrap;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use PHPUnit\Framework\AssertionFailedError;
use UlovDomov\TestExtras\Database\Fixtures\DatabaseFixture;

require_once __DIR__ . '/BaseUnitTestCase.php';

abstract class BaseDITestCase extends BaseUnitTestCase
{
    private Container|null $container = null;

    protected function tearDown(): void
    {
        parent::tearDown();
        // here destroy sessions and other if needed
    }

    /**
     * @template T of DatabaseFixture
     *
     * @param class-string<T> $fixturesClass
     *
     * @return T
     */
    protected function getFixture(string $fixturesClass): DatabaseFixture
    {
        try {
            /** @var T $fixture */
            $fixture = $this->getService($fixturesClass);
            $fixture->load();

            return $fixture;
        } catch (MissingServiceException $e) {
            self::fail($e->getMessage());
        }
    }

    protected function tryEnableTracy(bool $enable = false): void
    {
        parent::tryEnableTracy($enable);
    }

    protected function replaceService(string $name, object $service): void
    {
        $this->getContainer()->removeService($name);
        $this->getContainer()->addService($name, $service);
    }

    protected function getContainer(): Container
    {
        if ($this->container === null) {
            $this->container = $this->createContainer();
        }

        return $this->container;
    }

    protected function isContainerCreated(): bool
    {
        return $this->container !== null;
    }

    protected function refreshContainer(): void
    {
        $container = $this->getContainer();

        // here close sessions and other if needed

        $this->container = new $container();
        $this->container->initialize();
    }

    protected function createConfigurator(): Configurator
    {
        return Bootstrap::boot();
    }

    protected function setupConfigurator(Configurator $configurator): void
    {
        if (\file_exists(__DIR__ . '/../../config/test.neon')) {
            $configurator->addConfig(__DIR__ . '/../../config/test.neon');
        }

        if (\file_exists(__DIR__ . '/../../config/local.test.neon')) {
            $configurator->addConfig(__DIR__ . '/../../config/local.test.neon');
        }
    }

    /**
     * @param array<string> $configs
     */
    protected function createContainer(array $configs = []): Container
    {
        $config = $this->createConfigurator();

        $this->setupConfigurator($config);

        foreach ($configs as $file) {
            $config->addConfig($file);
        }

        return $config->createContainer();
    }

    /**
     * @template T of object
     *
     * @param  class-string<T> $type
     *
     * @throws AssertionFailedError
     */
    protected function getService(string $type, bool $throw = true): object|null
    {
        try {
            return $this->getContainer()->getByType($type, $throw);
        } catch (MissingServiceException) {
            self::fail(\sprintf('Service %s not found in DI container', $type));
        }
    }
}
