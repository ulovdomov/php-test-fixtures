<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\Database\Layers;

use Dibi\Connection;
use Dibi\Exception;
use UlovDomov\TestExtras\Database\Drivers\DatabaseDriver;

final class DibiLayer implements DatabaseLayer
{
    public function __construct(
        private string $databaseName,
        private DatabaseDriver $driver,
        private Connection $connection,
    )
    {
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    public function getCacheFile(): string
    {
        return $this->driver->getCacheFile();
    }

    /**
     * @return array<string, string>
     */
    public function getConfig(): array
    {
        /** @var array<string> $config */
        $config = $this->connection->getConfig();

        return [
            'password' => $config['password'],
            'host' => $config['host'],
            'user' => $config['username'],
        ];
    }

    /**
     * @throws Exception
     */
    public function createAndUseDatabase(): void
    {
        $this->connection->query($this->driver->dropDatabase($this->databaseName));
        $this->connection->query($this->driver->createDatabase($this->databaseName));

        $useDatabase = $this->driver->useDatabase($this->databaseName);

        if ($useDatabase !== null) {
            $this->connection->query($useDatabase);
        } else {
            $this->connection->__construct(['database' => $this->databaseName] + $this->connection->getConfig());
            $this->connection->disconnect();
        }
    }

    /**
     * @throws Exception
     */
    public function dropDatabase(): void
    {
        $this->connection->query($this->driver->dropDatabase($this->databaseName));
    }

    public function getDriver(): DatabaseDriver
    {
        return $this->driver;
    }
}
