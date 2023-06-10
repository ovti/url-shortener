<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230610183558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A1E7AB17D9');
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A118997C42');
        $this->addSql('DROP INDEX IDX_2A9437A1E7AB17D9 ON urls');
        $this->addSql('ALTER TABLE urls CHANGE guest_user_id users_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A167B3B43D FOREIGN KEY (users_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A118997C42 FOREIGN KEY (guest_users_id) REFERENCES guest_users (id)');
        $this->addSql('CREATE INDEX IDX_2A9437A167B3B43D ON urls (users_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A167B3B43D');
        $this->addSql('ALTER TABLE urls DROP FOREIGN KEY FK_2A9437A118997C42');
        $this->addSql('DROP INDEX IDX_2A9437A167B3B43D ON urls');
        $this->addSql('ALTER TABLE urls CHANGE users_id guest_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A1E7AB17D9 FOREIGN KEY (guest_user_id) REFERENCES guest_users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A118997C42 FOREIGN KEY (guest_users_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2A9437A1E7AB17D9 ON urls (guest_user_id)');
    }
}
