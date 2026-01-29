<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128165623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sub_task (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, inicio DATETIME NOT NULL, fin DATETIME DEFAULT NULL, maintask_id INT NOT NULL, owner_id INT NOT NULL, INDEX IDX_75E844E4EB995E13 (maintask_id), INDEX IDX_75E844E47E3C61F9 (owner_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE sub_task ADD CONSTRAINT FK_75E844E4EB995E13 FOREIGN KEY (maintask_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE sub_task ADD CONSTRAINT FK_75E844E47E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sub_task DROP FOREIGN KEY FK_75E844E4EB995E13');
        $this->addSql('ALTER TABLE sub_task DROP FOREIGN KEY FK_75E844E47E3C61F9');
        $this->addSql('DROP TABLE sub_task');
    }
}
