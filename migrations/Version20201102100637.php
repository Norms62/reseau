<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201102100637 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affichage (id INT AUTO_INCREMENT NOT NULL, traitement_id INT NOT NULL, ticket_id INT NOT NULL, nb_tikcket_regroup VARCHAR(255) DEFAULT NULL, ref VARCHAR(255) NOT NULL, date_soumission VARCHAR(255) NOT NULL, mise_a_jour VARCHAR(255) NOT NULL, rapporteur VARCHAR(255) DEFAULT NULL, resume VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, temps VARCHAR(255) DEFAULT NULL, priorite VARCHAR(255) DEFAULT NULL, impact VARCHAR(255) DEFAULT NULL, etat VARCHAR(255) DEFAULT NULL, resolution VARCHAR(255) DEFAULT NULL, categorie VARCHAR(255) DEFAULT NULL, commentaire VARCHAR(255) DEFAULT NULL, action VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE masao (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME NOT NULL, ref VARCHAR(255) NOT NULL, date_soumission VARCHAR(255) NOT NULL, mise_a_jour VARCHAR(255) NOT NULL, rapporteur VARCHAR(255) DEFAULT NULL, resume VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, temps VARCHAR(255) DEFAULT NULL, priorite VARCHAR(255) DEFAULT NULL, impact VARCHAR(255) DEFAULT NULL, etat VARCHAR(255) DEFAULT NULL, resolution VARCHAR(255) DEFAULT NULL, categorie VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE om (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME NOT NULL, ref VARCHAR(255) NOT NULL, date_soumission VARCHAR(255) NOT NULL, mise_a_jour VARCHAR(255) NOT NULL, rapporteur VARCHAR(255) DEFAULT NULL, resume VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, temps VARCHAR(255) DEFAULT NULL, priorite VARCHAR(255) DEFAULT NULL, impact VARCHAR(255) DEFAULT NULL, etat VARCHAR(255) DEFAULT NULL, resolution VARCHAR(255) DEFAULT NULL, categorie VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tickets_regroup (id INT AUTO_INCREMENT NOT NULL, masao_id INT DEFAULT NULL, om_id INT DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, type VARCHAR(255) NOT NULL, nb_tikcket_regroup VARCHAR(255) DEFAULT NULL, ref VARCHAR(255) NOT NULL, date_soumission VARCHAR(255) NOT NULL, mise_a_jour VARCHAR(255) NOT NULL, rapporteur VARCHAR(255) DEFAULT NULL, resume VARCHAR(255) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, temps VARCHAR(255) DEFAULT NULL, priorite VARCHAR(255) DEFAULT NULL, impact VARCHAR(255) DEFAULT NULL, etat VARCHAR(255) DEFAULT NULL, resolution VARCHAR(255) DEFAULT NULL, categorie VARCHAR(255) DEFAULT NULL, commentaire VARCHAR(255) DEFAULT NULL, action VARCHAR(255) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateurs (id INT AUTO_INCREMENT NOT NULL, date_creation DATETIME NOT NULL, email VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, roles VARCHAR(255) NOT NULL, mdp VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE affichage');
        $this->addSql('DROP TABLE masao');
        $this->addSql('DROP TABLE om');
        $this->addSql('DROP TABLE tickets_regroup');
        $this->addSql('DROP TABLE traitement');
        $this->addSql('DROP TABLE utilisateurs');
    }
}
