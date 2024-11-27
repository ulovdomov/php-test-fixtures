<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\DI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use UlovDomov\TestFixtures\Database\DatabaseLayerFactory;
use UlovDomov\TestFixtures\Database\Drivers\DatabaseDriver;
use UlovDomov\TestFixtures\Database\Drivers\MySqlDriver;
use UlovDomov\TestFixtures\Database\Drivers\PgSqlDriver;
use UlovDomov\TestFixtures\Database\Layers\DibiLayer;
use UlovDomov\TestFixtures\Database\Layers\DoctrineLayer;
use UlovDomov\TestFixtures\Database\Migrations\DoctrineMigrationsDriver;
use UlovDomov\TestFixtures\Database\Migrations\MigrationsDriver;
use UlovDomov\TestFixtures\Database\Migrations\NextrasMigrationsDriver;

final class TestExtrasExtension extends CompilerExtension
{
    /**
     * @var array<string, string>
     */
    private static array $migrationDrivers = [
        'nextras' => NextrasMigrationsDriver::class,
        'doctrine' => DoctrineMigrationsDriver::class,
    ];

    /**
     * @var array<string, string>
     */
    private static array $databaseLayers = [
        'dibi' => DibiLayer::class,
        'doctrine' => DoctrineLayer::class,
    ];

    /**
     * @var array<string, string>
     */
    private static array $databaseDrivers = [
        'mysql' => MySqlDriver::class,
        'pgsql' => PgSqlDriver::class,
    ];

    public function getConfigSchema(): Schema
    {
        return Expect::structure([
            'migrations' => Expect::anyOf('doctrine', 'nextras')->required(),
            'database' => Expect::anyOf('mysql', 'pgsql')->required(),
            'databaseLayer' => Expect::anyOf('doctrine', 'dibi')->required(),
        ])->castTo('array');
    }

    public function beforeCompile(): void
    {
        $container = $this->getContainerBuilder();
        /** @var array<string> $config */
        $config = $this->config;

        $migrationDriver = self::$migrationDrivers[$config['migrations']]
            ?? throw new \LogicException(\sprintf('Invalid migration driver "%s".', $config['migrations']));
        $container->addDefinition($this->prefix('migrationDriver'))
            ->setType(MigrationsDriver::class)
            ->setFactory($migrationDriver);

        $databaseDriver = self::$databaseDrivers[$config['database']]
            ?? throw new \LogicException(\sprintf('Invalid database driver "%s".', $config['database']));
        $container->addDefinition($this->prefix('databaseDriver'))
            ->setType(DatabaseDriver::class)
            ->setFactory($databaseDriver);

        $databaseLayerClass = self::$databaseLayers[$config['databaseLayer']]
            ?? throw new \LogicException(\sprintf('Invalid database layer "%s".', $config['databaseLayer']));
        $container->addDefinition($this->prefix('databaseLayerFactory'))
            ->setFactory(DatabaseLayerFactory::class, [
                'databaseLayerClass' => $databaseLayerClass,
            ]);
    }
}
