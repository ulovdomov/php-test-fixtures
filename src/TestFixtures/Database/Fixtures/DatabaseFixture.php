<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\Database\Fixtures;

use Nette\DI\Container;

interface DatabaseFixture
{
    public function load(): void;

    public function save(Container $container): void;
}
