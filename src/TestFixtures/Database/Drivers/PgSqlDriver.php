<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Drivers;

final class PgSqlDriver implements DatabaseDriver
{
    public function dropDatabase(string $databaseName): string
    {
        return 'DROP DATABASE IF EXISTS ' . $databaseName;
    }

    public function createDatabase(string $databaseName): string
    {
        return 'CREATE DATABASE ' . $databaseName . ' ENCODING "UTF8" LC_COLLATE = "en_US.utf8" LC_CTYPE = "en_US.utf8"';
    }

    public function useDatabase(string $databaseName): string|null
    {
        return null;
    }

    public function getExportCommand(): string
    {
        return 'pgsql-export.sh';
    }

    public function getImportCommand(): string
    {
        return 'pgsql-import.sh';
    }

    public function getCacheFile(): string
    {
        return '/database-pgsql.sql';
    }
}
