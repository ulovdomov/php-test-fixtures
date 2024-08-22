<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\TestCases;

use Psr\Container\ContainerInterface;
use Slim\App;

require_once __DIR__ . '/BaseDITestCase.php';

abstract class BaseDIApiTestCase extends BaseDITestCase
{
    use ApiTestTrait;

    /**
     * @return App<ContainerInterface>
     */
    private function getSlimApp(): App
    {
        return $this->getService(App::class);
    }
}
