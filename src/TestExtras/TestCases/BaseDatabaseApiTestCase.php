<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\TestCases;

use Psr\Container\ContainerInterface;
use Slim\App;

require_once __DIR__ . '/BaseDatabaseTestCase.php';

abstract class BaseDatabaseApiTestCase extends BaseDatabaseTestCase
{
    use ApiTestTrait;

    /**
     * @return App<ContainerInterface>
     */
    private function getSlimApp(): App
    {
        /** @phpstan-ignore-next-line */
        return $this->getService(App::class);
    }
}
