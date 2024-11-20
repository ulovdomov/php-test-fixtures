<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Layers;

interface DatabaseLayer
{
    public function getDatabaseName(): string;

    public function createAndUseDatabase(): void;

    public function dropDatabase(): void;

    public function getExportCommand(): string;

    public function getImportCommand(): string;

    /**
     * @return array<string, string>
     */
    public function getConfig(): array;
}
