<?php declare(strict_types = 1);

namespace Tests\Libraries;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class TestUser
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $username;

    public function __construct(string $username)
    {
        $this->username = $username;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
