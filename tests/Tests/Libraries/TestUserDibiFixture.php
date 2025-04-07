<?php declare(strict_types = 1);

namespace Tests\Libraries;

use Dibi\Connection;
use Dibi\Exception;
use UlovDomov\TestExtras\Database\Fixtures\DibiDatabaseFixture;

final class TestUserDibiFixture extends DibiDatabaseFixture
{
    public TestDibiUser $user;

    public function load(): void
    {
        $this->user = new TestDibiUser(1, 'John'); // id is here only for testing purposes
    }

    /**
     * @throws Exception
     */
    public function persist(Connection $connection): void
    {
        $data = $this->user->toArray();
        unset($data['id']);
        $connection->query('INSERT INTO users', $data);
    }
}
