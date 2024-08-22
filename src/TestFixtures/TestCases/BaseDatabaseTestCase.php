<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\TestCases;

use Dibi\Connection;
use Dibi\Exception;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nextras\Migrations\Bridges\SymfonyConsole\ResetCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Process\Process;
use Tracy\Debugger;
use UlovDomov\Exceptions\LogicException;

require_once __DIR__ . '/BaseDITestCase.php';

abstract class BaseDatabaseTestCase extends BaseDITestCase
{
    private const string DATABASE_CACHE_FILE = '/database-dummy.sql';

    private string $databaseName;

    protected function refreshConnection(Container $container): Connection
    {
        try {
            // @var Connection $oldConnection */
            $connection = $container->getByType(Connection::class);
            $this->createDatabase($connection);
            $connection->__construct(
                ['database' => $this->databaseName] + $connection->getConfig(),
            );
            $connection->disconnect();

            $this->doSetupDatabase($connection, $container);

            return $connection;
        } catch (MissingServiceException | Exception $e) {
            self::fail($e->getMessage());
        }
    }

    protected function createContainer(array $configs = []): Container
    {
        $container = parent::createContainer($configs);

        try {
            $this->refreshConnection($container);

        } catch (\Throwable $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            throw LogicException::createFromPrevious($e);
        }

        return $container;
    }

    protected function doSetupDatabase(Connection $connection, Container $container): void
    {
        try {
            $this->importDatabase($connection, $container);

            \register_shutdown_function(function () use ($connection): void {
                $connection->query('DROP DATABASE IF EXISTS `' . $this->databaseName . '`');
            });

        } catch (Exception $e) {
            throw LogicException::createFromPrevious($e);
        }
    }

    private function createDatabase(Connection $connection): void
    {
        $this->databaseName = 'tf_test_' . \getmypid();

        try {
            $connection->query('DROP DATABASE IF EXISTS `' . $this->databaseName . '`');
            $connection->query(
                'CREATE DATABASE `' . $this->databaseName . '` ENCODING "UTF8" LC_COLLATE = "en_US.utf8" LC_CTYPE = "en_US.utf8"',
            );
        } catch (Exception $e) {
            Debugger::log($e, Debugger::EXCEPTION);
            self::fail('Error during database create: ' . $e->getMessage());
        }
    }

    private function importDatabase(Connection $connection, Container $container): void
    {
        $load = true;

        if (self::isMigrationCacheNeedCreate()) {
            self::lock('database-cache');

            if (self::isMigrationCacheNeedCreate()) {
                if (self::isMigrationCacheExists()) {
                    self::fail('Migration cache already exists');
                }

                $load = false;
                self::runMigrationsReset($container);

                try {
                    $process = $this->createProcess('db-export.sh', $connection);
                    $process->run();

                    if (!$process->isSuccessful()) {
                        Debugger::log($process->getOutput() . $process->getErrorOutput(), Debugger::WARNING);
                        self::fail('Problem with generating cache for database migrations in tests');
                    }
                } catch (\Throwable $e) {
                    throw LogicException::createFromPrevious($e);
                }
            }
        }

        if ($load) {
            $process = $this->createProcess('db-import.sh', $connection);
            try {
                $process->setTimeout(120);
                $process->run();

                if (!$process->isSuccessful()) {
                    Debugger::log($process->getOutput() . $process->getErrorOutput(), Debugger::WARNING);
                    self::fail('Problem with importing cache with database migrations in tests');
                }
            } catch (\Throwable $e) {
                throw LogicException::createFromPrevious($e);
            }
        }
    }

    private static function runMigrationsReset(Container $container): void
    {
        try {
            /** @var ResetCommand $resetCommand */
            $resetCommand = $container->getByType(ResetCommand::class);
            \ob_start();
            $resetCommand->run(new ArrayInput([]), new NullOutput());
            \ob_end_clean();
        } catch (MissingServiceException $e) {
            self::fail($e->getMessage());
        }
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

    private function createProcess(string $command, Connection $connection): Process
    {
        /** @var array<string> $config */
        $config = $connection->getConfig();

        return new Process(
            ['sh', __DIR__ . '/../../bin/' . $command, $config['password'], $config['host'], $config['username'], $this->databaseName],
            __DIR__ . '/../../',
        );
    }
}
