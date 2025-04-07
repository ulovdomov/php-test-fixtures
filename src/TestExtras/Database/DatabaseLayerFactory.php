<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\Database;

use Nette\DI\Container;
use UlovDomov\TestExtras\Database\Layers\DatabaseLayer;

final class DatabaseLayerFactory
{
    /**
     * @param class-string<DatabaseLayer> $databaseLayerClass
     */
    public function __construct(private string $databaseLayerClass, private Container $container)
    {
    }

    public function create(string $databaseName): DatabaseLayer
    {
        /** @var DatabaseLayer $databaseLayer */
        $databaseLayer = $this->container->createInstance($this->databaseLayerClass, [
            'databaseName' => $databaseName,
        ]);

        return $databaseLayer;
    }
}
