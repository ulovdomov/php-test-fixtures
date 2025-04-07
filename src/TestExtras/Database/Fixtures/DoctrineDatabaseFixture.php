<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\Database\Fixtures;

use Doctrine\ORM\EntityManagerInterface;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;

abstract class DoctrineDatabaseFixture implements DatabaseFixture
{
    abstract public function persist(EntityManagerInterface $entityManager): void;

    /**
     * @throws MissingServiceException
     */
    final public function save(Container $container): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->getByType(EntityManagerInterface::class);
        $this->persist($entityManager);
    }
}
