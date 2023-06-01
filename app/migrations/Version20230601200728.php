<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230601200728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls_visited ADD url_id INT NOT NULL');
        $this->addSql('ALTER TABLE urls_visited ADD CONSTRAINT FK_B633420781CFDAE7 FOREIGN KEY (url_id) REFERENCES urls (id)');
        $this->addSql('CREATE INDEX IDX_B633420781CFDAE7 ON urls_visited (url_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls_visited DROP FOREIGN KEY FK_B633420781CFDAE7');
        $this->addSql('DROP INDEX IDX_B633420781CFDAE7 ON urls_visited');
        $this->addSql('ALTER TABLE urls_visited DROP url_id');
    }
}
