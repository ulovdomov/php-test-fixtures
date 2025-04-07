<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\Database\Drivers;

final class MySqlDriver implements DatabaseDriver
{
    public function dropDatabase(string $databaseName): string
    {
        return 'DROP DATABASE IF EXISTS `' . $databaseName . '`';
    }

    public function createDatabase(string $databaseName): string
    {
        return 'CREATE DATABASE `' . $databaseName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_czech_ci';
    }

    public function useDatabase(string $databaseName): string|null
    {
        return 'USE `' . $databaseName . '`';
    }

    public function getExportCommand(): string
    {
        return 'mysql-export.sh';
    }

    public function getImportCommand(): string
    {
        return 'mysql-import.sh';
    }

    public function getCacheFile(): string
    {
        return '/database-mysql.sql';
    }
}
