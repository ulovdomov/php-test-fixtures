# Php Test fixtures

Php Test fixtures for PhpUnit

## Installation

Add following to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ulovdomov/php-test-fixtures"
    }
  ]
}
```

And run:

```shell
composer require --dev ulovdomov/test-fixtures
```

## Usage

- If you get `Test code or tested code removed error handlers other than its own`:
  - try use `#[RunInSeparateProcess]` attribute for test method where you got this error.

_To use the pre-prepared BaseTestCases, simply extend the respective test case in your test class._

### `BaseUnitTestCase`

- Basic test case with tracy and dump method

_If you are running tests in parallel using the paratest Composer package, you may find synchronous processing useful
for certain tests. This can be achieved, for example, using a built-in file system lock._

```php
public function testFoo(): void
{
    self::lock('my-lock-identifier');
}
```

There is also a dump method, which dumps values into a log file.
```php
public function testFoo(): void
{
    $var = 'my mixed variable content to dump';
    self::dump($var);
}
```

It is also possible to set custom paths for the `temp` and `log` directories.

- The best place to set them is in the `setUp` method.

```php
protected function setUp(): void
{
    self::setTempDir(__DIR__ . '/../../../temp');
    self::setLogDir(__DIR__ . '/../../../log');

    parent::setUp();
}
```

### Test fixtures

For `DI` and the `Database` base TestCase, there is also support for Test Fixtures. These are classes that allow you
to define various entities and DTOs in one place, centralizing dependencies. This means that if the constructor
signature of an entity/DTO changes, no complex refactoring is needed—only the fixtures need to be updated.

- There is `DibiDatabaseFixture` and `DoctrineDatabaseFixture` (doctrine have `EntityMangerInterface` in persist method)
- The functionality is the same for both `Dibi` and `Doctrine`. In the `load` method, entities are created, and in the `persist` method, they are persisted to the database.
- The `persist` method is only called if the fixture is used in a database test case.

```php
namespace Tests\App;

final class TestUserDibiFixture extends DibiDatabaseFixture
{
    public TestDibiUser $user;
    
    public function load(): void
    {
        $this->user = new TestDibiUser(1, 'Tester');
    }

    public function persist(Connection $connection): void
    {
        $connection->insert('test_user', $this->user->toArray());
    }
}
```

You also need to register the fixture in the DI container:

```neon
services:
    - \Tests\App\TestUserDibiFixture
```

Usage in a test case:

```php
use Tests\App\TestUserDibiFixture;

public function testSomething(): void
{
    $userFixture = $this->getFixture(TestUserDibiFixture::class);

    $userFixture->user; // instance of TestDibiUser
}
```

### `BaseDITestCase`

- Test case with Nette DI container

If your Bootstrap class is not in namespace`App`, you must override `createConfigurator` method:

```php
protected function createConfigurator(): Configurator
{
    return Foo\Bar\MyBootstrap::boot();
}
```

If you need to add specific .neon configurations, simply override the setupConfigurator method:

```php
protected function setupConfigurator(Configurator $configurator): void
{
    if (\file_exists(__DIR__ . '/../../config/test.neon')) {
        $configurator->addConfig(__DIR__ . '/../../config/test.neon');
    }
}
```

And you can use methods in your TestCases:

```php
// To replace service with new instance
protected function replaceService(string $name, object $service): void

// To get service by given type
protected function getService(string $type, bool $throw = true): object|null
```

### `BaseDatabaseTestCase`

- **!!! For proper functionality, the `mysql-client` package must be installed for MySQL, and `postgresql-client` for PostgreSQL.**
- To use the `BaseDatabaseTestCase`, you need to register `UlovDomov\TestFixtures\DI\TestExtrasExtension` in the DI container and configure migrations, the database layer, and the database type.

```neon
extensions:
    testExtras: UlovDomov\TestFixtures\DI\TestExtrasExtension

testExtras:
    database: 'pgsql'         # (mysql|pgsql)
    databaseLayer: 'dibi'     # (doctrine|dibi)
    migrations: 'nextras'     # (doctrine|nextras)
```

- After this setup, the `BaseDatabaseTestCase` and all its descendants will function correctly.
- The database is initialized from the migration command and creates a dump in the temp directory as a cache to avoid running migrations every time.

## Development

### First setup

1. Run for initialization
```shell
make init
```
2. Run composer install
```shell
make composer
```

Use tasks in Makefile:

- To log into container
```shell
make docker
```
- To run code sniffer fix
```shell
make cs-fix
```
- To run PhpStan
```shell
make phpstan
```
- To run tests
```shell
make phpunit
```