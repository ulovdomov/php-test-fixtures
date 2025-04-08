<?php declare(strict_types = 1);

namespace Tests\TestFixtures;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Libraries\TestBootstrap;
use Tests\Libraries\TestUser;
use UlovDomov\TestExtras\TestCases\BaseDatabaseTestCase;

if (!\defined('STDIN')) {
    \define('STDIN', \fopen('php://stdin', 'r'));
}

final class BasicDoctrineTest extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        self::setTempDir(__DIR__ . '/../../../temp');
        self::setLogDir(__DIR__ . '/../../../log');

        parent::setUp();
    }

    /**
     * @throws MissingServiceException
     * @throws ORMException
     */
    #[RunInSeparateProcess]
    public function testBasic(): void
    {
        /** @var EntityManagerInterface $entityManger */
        $entityManger = $this->getContainer()->getByType(EntityManagerInterface::class);
        self::assertInstanceOf(EntityManagerInterface::class, $entityManger);

        $user = new TestUser('Tester');

        $entityManger->persist($user);
        $entityManger->flush();

        $userId = $user->getId();
        self::assertIsNumeric($userId);

        $entityManger->clear();

        $user = $entityManger->createQueryBuilder()
            ->select('u')
            ->from(TestUser::class, 'u')
            ->where('u.id = :id')
            ->setParameter('id', $userId)
            ->getQuery()
            ->getSingleResult();
        self::assertInstanceOf(TestUser::class, $user);
        self::assertEquals($userId, $user->getId());
        self::assertEquals('Tester', $user->getUsername());
    }

    protected function createConfigurator(): Configurator
    {
        return TestBootstrap::boot();
    }

    protected function createContainer(array $configs = []): Container
    {
        $configs[] = __DIR__ . '/../../config/doctrine.neon';

        if (\file_exists(__DIR__ . '/../../config/local.neon')) {
            $configs[] = __DIR__ . '/../../config/local.neon';
        }

        return parent::createContainer($configs);
    }
}
