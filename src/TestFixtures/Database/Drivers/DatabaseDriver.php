<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Drivers;

interface DatabaseDriver
{
    public function dropDatabase(string $databaseName): string;

    public function createDatabase(string $databaseName): string;

    public function useDatabase(string $databaseName): string|null;
}
