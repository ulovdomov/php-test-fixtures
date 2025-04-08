<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\TestCases;

use PHPUnit\Framework\TestCase;
use Tracy\Debugger;
use UlovDomov\TestExtras\Dumper;

if (\file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../../../../vendor/autoload.php';
}

abstract class BaseUnitTestCase extends TestCase
{
    private static string $logDir = __DIR__ . '/../../../../../../log';
    private static string $tempDir = __DIR__ . '/../../../../../../temp';

    protected function setUp(): void
    {
        parent::setUp();

        $this->tryEnableTracy();
    }

    protected static function setLogDir(string $logDir): void
    {
        self::$logDir = \rtrim($logDir, '/');
    }

    protected function setTempDir(string $tempDir): void
    {
        self::$tempDir = \rtrim($tempDir, '/');
    }

    protected static function lock(string $name): void
    {
        static $locks;
        $file = self::$tempDir . '/lock-' . \md5($name);

        if (!isset($locks[$file])) {
            $fp = \fopen($file, 'w');

            if ($fp === false) {
                self::fail(\sprintf('Can not create lock %s', $file));
            }

            \flock($locks[$file] = $fp, \LOCK_EX);

            \register_shutdown_function(static function () use ($fp, $file): void {
                /** @phpstan-ignore-next-line */
                if ($fp) {
                    \fclose($fp);
                }

                if (\file_exists($file)) {
                    \unlink($file);
                }
            });
        }
    }

    protected function tryEnableTracy(bool $enable = true): void
    {
        if (\class_exists(Debugger::class) && $enable) {
            Debugger::enable(logDirectory: self::$logDir);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \restore_error_handler();
        \restore_exception_handler();
    }

    public static function getTempDir(): string
    {
        return self::$tempDir;
    }

    public static function getLogDir(): string
    {
        return self::$logDir;
    }

    public static function dump(mixed $var): void
    {
        \file_put_contents(self::$logDir . '/dump' . \getmypid() . '.log', Dumper::toPhp($var) . "\n", \FILE_APPEND);
    }
}
