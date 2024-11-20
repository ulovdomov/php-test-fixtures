<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Layers;

use Dibi\Connection;
use Dibi\Exception;
use UlovDomov\TestFixtures\Database\Drivers\DatabaseDriver;

final class DibiLayer implements DatabaseLayer
{
    public function __construct(
        private string $databaseName,
        private DatabaseDriver $databaseDriver,
        private Connection $connection,
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
        $this->connection->query($this->databaseDriver->dropDatabase($this->databaseName));
        $this->connection->query($this->databaseDriver->createDatabase($this->databaseName));

        $useDatabase = $this->databaseDriver->useDatabase($this->databaseName);

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
        $this->connection->query($this->databaseDriver->dropDatabase($this->databaseName));
    }

    public function getExportCommand(): string
    {
        return 'pgsql-export.sh';
    }

    public function getImportCommand(): string
    {
        return 'pgsql-export.sh';
    }
}
