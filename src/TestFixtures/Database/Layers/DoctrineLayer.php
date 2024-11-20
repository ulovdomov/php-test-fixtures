<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Layers;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use UlovDomov\TestFixtures\Database\Drivers\DatabaseDriver;

final class DoctrineLayer implements DatabaseLayer
{
    public function __construct(
        private string $databaseName,
        private Connection $connection,
        private DatabaseDriver $driver,
    )
    {
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    /**
     * @return array<string, string>
     */
    public function getConfig(): array
    {
        /** @var array<string> $params */
        $params = $this->connection->getParams();

        return [
            'password' => $params['password'],
            'host' => $params['host'],
            'user' => $params['user'],
        ];
    }

    /**
     * @throws Exception
     */
    public function createAndUseDatabase(): void
    {
        $this->connection->executeStatement($this->driver->dropDatabase($this->databaseName));
        $this->connection->executeStatement($this->driver->createDatabase($this->databaseName));

        $useDatabase = $this->driver->useDatabase($this->databaseName);

        if ($useDatabase !== null) {
            $this->connection->executeStatement($useDatabase);
        } else {
            $this->connection->__construct(
                ['dbname' => $this->databaseName] + $this->connection->getParams(),
                $this->connection->getDriver(),
                $this->connection->getConfiguration(),
            );
            $this->connection->close(); //will this reconnect?
        }
    }

    /**
     * @throws Exception
     */
    public function dropDatabase(): void
    {
        $this->connection->executeQuery($this->driver->dropDatabase($this->databaseName));
    }

    public function getExportCommand(): string
    {
        return 'mysql-export.sh';
    }

    public function getImportCommand(): string
    {
        return 'mysql-export.sh';
    }
}
