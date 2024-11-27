<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Layers;

use UlovDomov\TestFixtures\Database\Drivers\DatabaseDriver;

interface DatabaseLayer
{
    public function getDatabaseName(): string;

    public function getCacheFile(): string;

    public function createAndUseDatabase(): void;

    public function dropDatabase(): void;

    public function getDriver(): DatabaseDriver;

    /**
     * @return array<string, string>
     */
    public function getConfig(): array;
}
