<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241102223909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE achat (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, programme_id INT NOT NULL, date_achat DATE NOT NULL, INDEX IDX_26A98456FB88E14F (utilisateur_id), INDEX IDX_26A9845662BB7AEE (programme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE avis (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, programme_id INT NOT NULL, commentaire LONGTEXT NOT NULL, note INT NOT NULL, date_avis DATE NOT NULL, INDEX IDX_8F91ABF0FB88E14F (utilisateur_id), INDEX IDX_8F91ABF062BB7AEE (programme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE programme (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, prix DOUBLE PRECISION NOT NULL, duree INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT FK_26A98456FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE achat ADD CONSTRAINT FK_26A9845662BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF0FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE avis ADD CONSTRAINT FK_8F91ABF062BB7AEE FOREIGN KEY (programme_id) REFERENCES programme (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY FK_26A98456FB88E14F');
        $this->addSql('ALTER TABLE achat DROP FOREIGN KEY FK_26A9845662BB7AEE');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF0FB88E14F');
        $this->addSql('ALTER TABLE avis DROP FOREIGN KEY FK_8F91ABF062BB7AEE');
        $this->addSql('DROP TABLE achat');
        $this->addSql('DROP TABLE avis');
        $this->addSql('DROP TABLE programme');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
