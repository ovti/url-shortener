<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230531091353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls ADD users_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A198333A1E FOREIGN KEY (users_id_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_2A9437A198333A1E ON urls (users_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A198333A1E');
        $this->addSql('DROP INDEX IDX_2A9437A198333A1E ON urls');
        $this->addSql('ALTER TABLE urls DROP users_id_id');
    }
}
