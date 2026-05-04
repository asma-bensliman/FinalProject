<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260504153218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration (id INT AUTO_INCREMENT NOT NULL, player_id INT NOT NULL, tournament_id INT NOT NULL, registration_date DATE NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_62A8A7A799E6F5DF (player_id), INDEX IDX_62A8A7A733D1A3E7 (tournament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sport_match (id INT AUTO_INCREMENT NOT NULL, tournament_id INT NOT NULL, player1_id INT NOT NULL, player2_id INT NOT NULL, match_date DATE NOT NULL, score_player1 INT DEFAULT NULL, score_player2 INT DEFAULT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_CE27A41C33D1A3E7 (tournament_id), INDEX IDX_CE27A41CC0990423 (player1_id), INDEX IDX_CE27A41CD22CABCD (player2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tournament (id INT AUTO_INCREMENT NOT NULL, organizer_id INT NOT NULL, winner_id INT DEFAULT NULL, tournament_name VARCHAR(255) NOT NULL, start_date DATE NOT NULL, location VARCHAR(255) DEFAULT NULL, end_date DATE NOT NULL, description LONGTEXT NOT NULL, max_participants INT NOT NULL, sport VARCHAR(255) NOT NULL, no VARCHAR(255) NOT NULL, INDEX IDX_BD5FB8D9876C4DDA (organizer_id), INDEX IDX_BD5FB8D95DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A799E6F5DF FOREIGN KEY (player_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41C33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41CC0990423 FOREIGN KEY (player1_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41CD22CABCD FOREIGN KEY (player2_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D9876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE tournament ADD CONSTRAINT FK_BD5FB8D95DFCD4B8 FOREIGN KEY (winner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A799E6F5DF');
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A733D1A3E7');
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41C33D1A3E7');
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41CC0990423');
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41CD22CABCD');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D9876C4DDA');
        $this->addSql('ALTER TABLE tournament DROP FOREIGN KEY FK_BD5FB8D95DFCD4B8');
        $this->addSql('DROP TABLE registration');
        $this->addSql('DROP TABLE sport_match');
        $this->addSql('DROP TABLE tournament');
        $this->addSql('DROP TABLE user');
    }
}
