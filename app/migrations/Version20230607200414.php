<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230607200414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls ADD guest_user_id INT DEFAULT NULL, DROP guest_user');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A1E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES guest_users (id)');
        $this->addSql('CREATE INDEX IDX_2A9437A1E7AB17D9 ON urls (guest_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A1E7AB17D9');
        $this->addSql('DROP INDEX IDX_2A9437A1E7AB17D9 ON urls');
        $this->addSql('ALTER TABLE urls ADD guest_user INT NOT NULL, DROP guest_user_id');
    }
}
