<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Migrations;

use Nette\DI\Container;

interface MigrationsDriver
{
    public function runMigrations(Container $container): void;
}
