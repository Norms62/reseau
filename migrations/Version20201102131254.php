<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102131254 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE traitement ADD ticket_regroup_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D27A276D42 FOREIGN KEY (ticket_regroup_id) REFERENCES tickets_regroup (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A356D27A276D42 ON traitement (ticket_regroup_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D27A276D42');
        $this->addSql('DROP INDEX UNIQ_2A356D27A276D42 ON traitement');
        $this->addSql('ALTER TABLE traitement DROP ticket_regroup_id');
    }
}
