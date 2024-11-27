<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Fixtures;

use Dibi\Connection;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

abstract class DibiDatabaseFixture implements DatabaseFixture
{
    abstract public function persist(Connection $connection): void;

    /**
     * @throws MissingServiceException
     */
    final public function save(Container $container): void
    {
        /** @var Connection $connection */
        $connection = $container->getByType(Connection::class);
        $this->persist($connection);
    }
}
