<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260506131020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ON DELETE CASCADE to registration.tournament_id and sport_match.tournament_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A733D1A3E7');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41C33D1A3E7');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41C33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE registration DROP FOREIGN KEY FK_62A8A7A733D1A3E7');
        $this->addSql('ALTER TABLE registration ADD CONSTRAINT FK_62A8A7A733D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
        $this->addSql('ALTER TABLE sport_match DROP FOREIGN KEY FK_CE27A41C33D1A3E7');
        $this->addSql('ALTER TABLE sport_match ADD CONSTRAINT FK_CE27A41C33D1A3E7 FOREIGN KEY (tournament_id) REFERENCES tournament (id)');
    }
}
