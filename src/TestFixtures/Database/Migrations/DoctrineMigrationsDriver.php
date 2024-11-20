<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Migrations;

use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class DoctrineMigrationsDriver implements MigrationsDriver
{
    /**
     * @throws MissingServiceException
     */
    public function runMigrations(Container $container): void
    {
        /** @var MigrateCommand $migrateCommand */
        $migrateCommand = $container->getByType(MigrateCommand::class);
        \ob_start();
        $migrateCommand->run(new ArrayInput([]), new NullOutput());
        \ob_end_clean();
    }
}
