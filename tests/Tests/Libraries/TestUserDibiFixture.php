<?php declare(strict_types = 1);

namespace Tests\Libraries;

use Dibi\Connection;
use UlovDomov\TestFixtures\Database\Fixtures\DibiDatabaseFixture;

final class TestUserDibiFixture extends DibiDatabaseFixture
{
    public TestDibiUser $user;

    public function load(): void
    {
        $this->user = new TestDibiUser(1, 'Tester');
    }

    public function persist(Connection $connection): void
    {
        $connection->insert('test_user', $this->user->toArray());
    }
}
