<?php declare(strict_types = 1);

namespace TestDatabase\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241104100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TABLE `users` (
              `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
              `username` varchar(255) NOT NULL
        ) ENGINE='InnoDB' COLLATE 'utf8mb4_czech_ci'");
    }
}
