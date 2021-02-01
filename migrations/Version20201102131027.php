<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102131027 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affichage DROP FOREIGN KEY FK_27914E97C583F1C');
        $this->addSql('DROP INDEX UNIQ_27914E97C583F1C ON affichage');
        $this->addSql('ALTER TABLE affichage CHANGE traitement_id traitement_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE affichage ADD CONSTRAINT FK_27914E97C583F1C FOREIGN KEY (traitement_id_id) REFERENCES traitement (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_27914E97C583F1C ON affichage (traitement_id_id)');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D275774FDDC');
        $this->addSql('DROP INDEX FK_2A356D275774FDDC ON traitement');
        $this->addSql('ALTER TABLE traitement ADD ticket_masao_id INT DEFAULT NULL, CHANGE ticket_om ticket_om_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D27A53C47F6 FOREIGN KEY (ticket_om_id) REFERENCES om (id)');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D2755C50B36 FOREIGN KEY (ticket_masao_id) REFERENCES masao (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A356D27A53C47F6 ON traitement (ticket_om_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2A356D2755C50B36 ON traitement (ticket_masao_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affichage DROP FOREIGN KEY FK_27914E97C583F1C');
        $this->addSql('DROP INDEX UNIQ_27914E97C583F1C ON affichage');
        $this->addSql('ALTER TABLE affichage CHANGE traitement_id_id traitement_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE affichage ADD CONSTRAINT FK_27914E97C583F1C FOREIGN KEY (traitement_id) REFERENCES traitement (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_27914E97C583F1C ON affichage (traitement_id)');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D27A53C47F6');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D2755C50B36');
        $this->addSql('DROP INDEX UNIQ_2A356D27A53C47F6 ON traitement');
        $this->addSql('DROP INDEX UNIQ_2A356D2755C50B36 ON traitement');
        $this->addSql('ALTER TABLE traitement ADD ticket_om INT DEFAULT NULL, DROP ticket_om_id, DROP ticket_masao_id');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D275774FDDC FOREIGN KEY (ticket_om) REFERENCES om (id)');
        $this->addSql('CREATE INDEX FK_2A356D275774FDDC ON traitement (ticket_om)');
    }
}
