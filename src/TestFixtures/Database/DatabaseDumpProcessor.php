<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database;

use Symfony\Component\Process\Process;
use Tracy\Debugger;
use UlovDomov\TestFixtures\Database\Layers\DatabaseLayer;

final class DatabaseDumpProcessor
{
    private function __construct()
    {
        // static class
    }

    public static function dumpDatabase(DatabaseLayer $databaseLayer): void
    {
        try {
            $process = self::createProcess($databaseLayer->getDriver()->getExportCommand(), $databaseLayer);
            $process->run();

            if (!$process->isSuccessful()) {
                Debugger::log($process->getOutput() . $process->getErrorOutput(), Debugger::WARNING);
                throw new \LogicException('Problem with generating cache for database migrations in tests');
            }
        } catch (\Throwable $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public static function importDatabase(DatabaseLayer $databaseLayer): void
    {
        try {
            $process = self::createProcess($databaseLayer->getDriver()->getImportCommand(), $databaseLayer);
            $process->setTimeout(120);
            $process->run();

            if (!$process->isSuccessful()) {
                Debugger::log($process->getOutput() . $process->getErrorOutput(), Debugger::WARNING);
                throw new \LogicException('Problem with importing cache with database migrations in tests');
            }
        } catch (\Throwable $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private static function createProcess(string $command, DatabaseLayer $databaseLayer): Process
    {
        $config = $databaseLayer->getConfig();

        $cwd = \is_dir(__DIR__ . '/../../../temp') ? __DIR__ . '/../../../' : __DIR__ . '/../../../../../../';

        return new Process(
            ['sh', __DIR__ . '/bin/' . $command, $config['password'], $config['host'], $config['user'], $databaseLayer->getDatabaseName()],
            $cwd,
        );
    }

    public function __clone(): void
    {
        throw new \LogicException('Cloning is not allowed in static class.');
    }
}
