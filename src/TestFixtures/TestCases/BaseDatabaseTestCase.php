<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\TestCases;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Tracy\Debugger;
use UlovDomov\TestFixtures\Database\DatabaseDumpProcessor;
use UlovDomov\TestFixtures\Database\DatabaseLayerFactory;
use UlovDomov\TestFixtures\Database\Fixtures\DatabaseFixture;
use UlovDomov\TestFixtures\Database\Layers\DatabaseLayer;
use UlovDomov\TestFixtures\Database\Migrations\MigrationsDriver;

require_once __DIR__ . '/BaseDITestCase.php';

abstract class BaseDatabaseTestCase extends BaseDITestCase
{
    private const DATABASE_CACHE_FILE = '/database-dummy.sql';

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
            self::fail($e->getMessage());
        }
    }

    /**
     * @throws MissingServiceException
     */
    private function importDatabase(Container $container): void
    {
        $load = true;

        if (self::isMigrationCacheNeedCreate()) {
            self::lock('database-cache');

            /** @phpstan-ignore-next-line */
            if (self::isMigrationCacheNeedCreate()) {
                if (self::isMigrationCacheExists()) {
                    self::fail('Migration cache already exists');
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

    private static function isMigrationCacheNeedCreate(): bool
    {
        return !self::isMigrationCacheExists() ||
            !\str_starts_with(self::getLastLineFromFile(self::getDatabaseCacheFile()), '-- Dump completed');
    }

    private static function isMigrationCacheExists(): bool
    {
        return \file_exists(self::getDatabaseCacheFile());
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

    private static function getDatabaseCacheFile(): string
    {
        return self::getTempDir() . self::DATABASE_CACHE_FILE;
    }
}
