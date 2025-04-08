<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\TestCases;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Tracy\Debugger;
use UlovDomov\TestExtras\Database\DatabaseDumpProcessor;
use UlovDomov\TestExtras\Database\DatabaseLayerFactory;
use UlovDomov\TestExtras\Database\Fixtures\DatabaseFixture;
use UlovDomov\TestExtras\Database\Layers\DatabaseLayer;
use UlovDomov\TestExtras\Database\Migrations\MigrationsDriver;

require_once __DIR__ . '/BaseDITestCase.php';

abstract class BaseDatabaseTestCase extends BaseDITestCase
{
    private DatabaseLayer|null $databaseLayer = null;

    /**
     * @template T of DatabaseFixture
     *
     * @param class-string<T> $fixturesClass
     *
     * @return T
     */
    protected function getFixture(string $fixturesClass): DatabaseFixture
    {
        $fixture = parent::getFixture($fixturesClass);
        $fixture->save($this->getContainer());

        return $fixture;
    }

    protected function createContainer(array $configs = []): Container
    {
        $container = parent::createContainer($configs);

        try {
            $this->initializeConnection($container);

        } catch (\Throwable $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        }

        return $container;
    }

    protected function initializeConnection(Container $container): void
    {
        try {
            $this->getDatabaseLayer($container)->createAndUseDatabase();

            $this->importDatabase($container);

            \register_shutdown_function(function () use ($container): void {
                $this->getDatabaseLayer($container)->dropDatabase();
            });

        } catch (MissingServiceException | \Throwable $e) {
            throw new \LogicException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws MissingServiceException
     */
    private function importDatabase(Container $container): void
    {
        $load = true;

        if (!\defined('STDIN')) {
            \define('STDIN', \fopen('php://stdin', 'r'));
        }

        if ($this->isMigrationCacheNeedCreate($container)) {
            self::lock($this->getDatabaseLayer($container)->getCacheFile());

            /** @phpstan-ignore-next-line */
            if ($this->isMigrationCacheNeedCreate($container)) {
                if ($this->isMigrationCacheExists($container)) {
                    $this->removeMigrationCacheExists($container);
                }

                $load = false;

                $this->getMigrationsDriver($container)->runMigrations($container);

                DatabaseDumpProcessor::dumpDatabase($this->getDatabaseLayer($container));
            }
        }

        if ($load) {
            DatabaseDumpProcessor::importDatabase($this->getDatabaseLayer($container));
        }
    }

    /**
     * @throws MissingServiceException
     */
    private function getDatabaseLayer(Container $container): DatabaseLayer
    {
        if ($this->databaseLayer === null) {
            /** @var DatabaseLayerFactory $factory */
            $factory = $container->getByType(DatabaseLayerFactory::class);
            $this->databaseLayer = $factory->create('tf_test_' . \getmypid());

        }

        return $this->databaseLayer;
    }

    /**
     * @throws MissingServiceException
     */
    private function getMigrationsDriver(Container $container): MigrationsDriver
    {
        return $container->getByType(MigrationsDriver::class);
    }

    /**
     * @throws MissingServiceException
     */
    private function isMigrationCacheNeedCreate(Container $container): bool
    {
        return !$this->isMigrationCacheExists($container) ||
            !\str_starts_with(self::getLastLineFromFile($this->getDatabaseCacheFile($container)), '-- Dump completed');
    }

    /**
     * @throws MissingServiceException
     */
    private function isMigrationCacheExists(Container $container): bool
    {
        return \file_exists($this->getDatabaseCacheFile($container));
    }

    /**
     * @throws MissingServiceException
     */
    private function removeMigrationCacheExists(Container $container): void
    {
        \unlink($this->getDatabaseCacheFile($container));
    }

    private static function getLastLineFromFile(string $path): string
    {
        $line = '';
        $f = \fopen($path, 'r');

        if ($f === false) {
            return '';
        }

        $cursor = -1;

        \fseek($f, $cursor, \SEEK_END);
        $char = \fgetc($f);

        while ($char === "\n" || $char === "\r") {
            \fseek($f, $cursor--, \SEEK_END);
            $char = \fgetc($f);
        }

        while ($char !== false && $char !== "\n" && $char !== "\r") {
            $line = $char . $line;
            \fseek($f, $cursor--, \SEEK_END);
            $char = \fgetc($f);
        }

        \fclose($f);

        return $line;
    }

    /**
     * @throws MissingServiceException
     */
    private function getDatabaseCacheFile(Container $container): string
    {
        return self::getTempDir() . $this->getDatabaseLayer($container)->getCacheFile();
    }
}
