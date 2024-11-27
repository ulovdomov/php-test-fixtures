<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Migrations;

use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use Nextras\Migrations\Bridges\SymfonyConsole\ResetCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class NextrasMigrationsDriver implements MigrationsDriver
{
    /**
     * @throws MissingServiceException
     */
    public function runMigrations(Container $container): void
    {
        /** @var ResetCommand $resetCommand */
        $resetCommand = $container->getByType(ResetCommand::class);
        \ob_start();
        $resetCommand->run(new ArrayInput([]), new NullOutput());
        \ob_end_clean();
    }
}
