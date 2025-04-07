<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\Api;

use Laminas\Diactoros\ServerRequest;

final class MockServerRequest extends ServerRequest
{
    public const API_TOKEN = 'abc621417e5d7aa7ad9efa828bc6fdef';

    /**
     * @param array<int|string> $headers
     */
    public function __construct(
        string $uri,
        string $method = 'GET',
        string|null $body = null,
        array $headers = [],
    ) {
        $stream = \fopen('data://text/plain;base64,' . \base64_encode($body ?? ''), 'r');

        if ($stream === false) {
            throw new \LogicException('Resource can not be created');
        }

        parent::__construct(
            uri: $uri,
            method: $method,
            body: $stream,
            headers: $headers,
        );
    }
}
