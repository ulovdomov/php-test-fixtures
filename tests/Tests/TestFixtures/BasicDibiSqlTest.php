<?php declare(strict_types = 1);

namespace Tests\TestFixtures;

use Dibi\Connection;
use Dibi\Exception;
use Dibi\Row;
use Nette\Bootstrap\Configurator;
use Nette\DI\Container;
use Nette\DI\MissingServiceException;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Libraries\TestBootstrap;
use Tests\Libraries\TestUserDibiFixture;
use UlovDomov\TestExtras\TestCases\BaseDatabaseTestCase;

final class BasicDibiSqlTest extends BaseDatabaseTestCase
{
    protected function setUp(): void
    {
        self::setTempDir(__DIR__ . '/../../../temp');
        self::setLogDir(__DIR__ . '/../../../log');

        parent::setUp();
    }

    /**
     * @throws MissingServiceException
     * @throws Exception
     */
    #[RunInSeparateProcess]
    public function testBasic(): void
    {
        /** @var Connection $connection */
        $connection = $this->getContainer()->getByType(Connection::class);
        self::assertInstanceOf(Connection::class, $connection);

        $userFixture = $this->getFixture(TestUserDibiFixture::class);

        $userId = $connection->getInsertId();

        $userData = $connection->select('*')->from('users')->where('id = ?', $userId)->fetch();
        self::assertInstanceOf(Row::class, $userData);
        self::assertSame($userId, $userFixture->user->getId());
        self::assertSame($userId, $userData['id']);
        self::assertSame($userFixture->user->getUsername(), $userData['username']);
    }

    protected function createConfigurator(): Configurator
    {
        return TestBootstrap::boot();
    }

    protected function createContainer(array $configs = []): Container
    {
        $configs[] = __DIR__ . '/../../config/dibi.neon';

        if (\file_exists(__DIR__ . '/../../config/local.neon')) {
            $configs[] = __DIR__ . '/../../config/local.neon';
        }

        return parent::createContainer($configs);
    }
}
