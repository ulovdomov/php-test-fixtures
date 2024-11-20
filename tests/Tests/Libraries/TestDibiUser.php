<?php declare(strict_types = 1);

namespace Tests\Libraries;

final class TestDibiUser
{
    public function __construct(private int $id, private string $username)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
        ];
    }
}
