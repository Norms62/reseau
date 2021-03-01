<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210216093821 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affichage (id INT AUTO_INCREMENT NOT NULL, nb_ticket_regroup VARCHAR(255) DEFAULT NULL, ref VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date_soumission VARCHAR(255) NOT NULL, mise_a_jour VARCHAR(255) NOT NULL, rapporteur VARCHAR(255) DEFAULT NULL, resume VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, temps VARCHAR(255) DEFAULT NULL, priorite VARCHAR(255) DEFAULT NULL, impact VARCHAR(255) DEFAULT NULL, etat VARCHAR(255) DEFAULT NULL, resolution VARCHAR(255) DEFAULT NULL, categorie VARCHAR(255) DEFAULT NULL, commentaire VARCHAR(255) DEFAULT NULL, action VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, traitement_id INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
    }
}
