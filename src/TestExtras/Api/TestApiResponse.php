<?php declare(strict_types = 1);

namespace UlovDomov\TestExtras\Api;

use Nette\Utils\Strings;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;

final class TestApiResponse
{
    public function __construct(private ResponseInterface $response)
    {

    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return array<string>
     */
    public function getHeader(string $name): array
    {
        return $this->response->getHeader($name);
    }

    /**
     * @return array<mixed>|null
     */
    public function getPayload(): array|null
    {
        $contents = Strings::trim($this->getContents());

        if (Strings::length($contents) === 0) {
            return null;
        }

        try {
            /** @var array<mixed>|null $out */
            $out = \json_decode($contents, flags: \JSON_THROW_ON_ERROR | \JSON_OBJECT_AS_ARRAY);

            return $out;
        } catch (\JsonException $e) {
            Assert::fail(\sprintf('Invalid JSON content: %s, contents: "%s"', $e->getMessage(), $this->getContents()));
        }
    }

    public function getContents(): string
    {
        try {
            $this->response->getBody()->rewind();

            return $this->response->getBody()->getContents();
        } catch (\RuntimeException $e) {
            throw new \LogicException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
