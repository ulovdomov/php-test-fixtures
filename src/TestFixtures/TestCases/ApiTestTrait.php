<?php declare(strict_types = 1);

namespace UlovDomov\TestFixtures\TestCases;

use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Tracy\Debugger;
use UlovDomov\TestFixtures\Api\MockServerRequest;
use UlovDomov\TestFixtures\Api\TestApiResponse;

require_once __DIR__ . '/BaseDITestCase.php';

trait ApiTestTrait
{
    private string|null $apiToken = null;

    abstract private function getSlimApp(): App;

    abstract public static function assertSame(mixed $expected, mixed $actual, string $message = ''): void;

    abstract public static function assertCount(
        int $expectedCount,
        \Countable|iterable $haystack,
        string $message = '',
    ): void;

    abstract public static function assertTrue(mixed $condition, string $message = ''): void;

    abstract public static function fail(string $message = ''): void;

    /**
     * @param array<int|string|mixed> $query
     */
    protected static function withParams(string $uri, array $query = []): string
    {
        return $uri . (\count($query) > 0 ? '?' . \http_build_query($query) : '');
    }

    protected function authorizeApi(): void
    {
        $this->apiToken = MockServerRequest::API_TOKEN;
    }

    protected function get(string $path, string|null $authToken = null): TestApiResponse
    {
        $request = self::createApiRequest(
            path: $path,
            authToken: $authToken,
        );

        return $this->handleApiRequest($request);
    }

    /**
     * @param array<int|string|mixed> $data
     */
    protected function post(string $path, array $data = [], string|null $authToken = null): TestApiResponse
    {
        $request = self::createApiRequest(
            path: $path,
            method: 'POST',
            data: $data,
            authToken: $authToken,
        );

        return $this->handleApiRequest($request);
    }

    /**
     * @param array<int|string|mixed> $data
     */
    protected function put(string $path, array $data = [], string|null $authToken = null): TestApiResponse
    {
        $request = self::createApiRequest(
            path: $path,
            method: 'PUT',
            data: $data,
            authToken: $authToken,
        );

        return $this->handleApiRequest($request);
    }

    protected function delete(string $path, string|null $authToken = null): TestApiResponse
    {
        $request = self::createApiRequest(
            path: $path,
            method: 'DELETE',
            authToken: $authToken,
        );

        return $this->handleApiRequest($request);
    }

    private function handleApiRequest(ServerRequestInterface $request): TestApiResponse
    {
        try {
            $productionMode = Debugger::$productionMode;
            Debugger::$productionMode = true;

            return new TestApiResponse($this->getSlimApp()->handle($request));
        } finally {
            Debugger::$productionMode = $productionMode;
        }
    }

    /**
     * @param array<int|string|mixed> $data
     */
    private function createApiRequest(
        string $path,
        string $method = 'GET',
        array|null $data = null,
        string|null $authToken = null,
    ): MockServerRequest
    {
        $headers = [
            'Content-type' => 'application/json',
        ];

        $authToken ??= $this->apiToken;

        if ($authToken !== null) {
            $headers['Authorization'] = \sprintf('Bearer %s', $authToken);
        }

        return self::createMockServerRequest(
            $path,
            $method,
            $data !== null ? self::jsonEncode($data) : null,
            $headers,
        );
    }

    /**
     * @param array<int|string> $headers
     */
    private static function createMockServerRequest(
        string $path,
        string $method = 'GET',
        string|null $body = null,
        array $headers = [],
    ): MockServerRequest
    {
        return new MockServerRequest(self::createUri($path), $method, $body, $headers);
    }

    private static function createUri(string $path): string
    {
        return 'http://app.loc/' . \ltrim($path, '/');
    }

    protected static function assertSuccessStatus(TestApiResponse $response): void
    {
        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * @param array<mixed> $actualData
     */
    public static function assertPath(string $path, mixed $expected, array $actualData): void
    {
        self::assertPathCallback($path, static function ($actual) use ($path, $expected) {
            self::assertSame($expected, $actual, \sprintf('For path %s', $path));

            return true;
        }, $actualData);
    }

    /**
     * @param array<mixed> $actualData
     */
    public static function assertPathCount(string $path, int $count, array $actualData): void
    {
        self::assertPathCallback($path, static function ($actual) use ($path, $count) {
            self::assertCount($count, $actual, \sprintf('For path %s', $path));

            return true;
        }, $actualData);
    }

    /**
     * @param array<int|string|mixed> $actualData
     */
    public static function assertPathCallback(string $path, callable $callback, array $actualData): void
    {
        $key = $path;

        if (\str_starts_with($path, '$.')) {
            $key = \substr($path, 2);
        }

        if (\str_contains($key, '.')) {
            $keys = \explode('.', $key);

            foreach ($keys as $innerKey) {
                if (!\is_array($actualData)) {
                    self::fail(
                        \sprintf(
                            'Value for key %s is not an array in data: %s',
                            $innerKey,
                            self::jsonEncode($actualData),
                        ),
                    );
                } elseif (!\array_key_exists($innerKey, $actualData)) {
                    self::fail(\sprintf('Key %s not found in data: %s', $innerKey, self::jsonEncode($actualData)));
                }

                $actualData = $actualData[$innerKey];
            }

            self::assertTrue(\call_user_func($callback, $actualData), 'Result from callback must be true');

            return;
        }

        if (\array_key_exists($key, $actualData)) {
            self::assertTrue(\call_user_func($callback, $actualData[$key]), 'Result from callback must be true');
        } else {
            self::fail(\sprintf('Key %s not found in data: %s', $key, self::jsonEncode($actualData)));
        }
    }

    private static function jsonEncode(mixed $actualData): string
    {
        try {
            return Json::encode($actualData);
        } catch (JsonException $e) {
            Assert::fail('Invalid JSON source: ' . $e->getMessage());
        }
    }
}
