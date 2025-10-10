<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251009220453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) DEFAULT NULL, gender VARCHAR(50) DEFAULT NULL, status VARCHAR(50) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode_character (episode_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_2DB8260D362B62A0 (episode_id), INDEX IDX_2DB8260D1136BE75 (character_id), PRIMARY KEY(episode_id, character_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE episode_character ADD CONSTRAINT FK_2DB8260D362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode_character ADD CONSTRAINT FK_2DB8260D1136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode_characters DROP FOREIGN KEY FK_65920B90362B62A0');
        $this->addSql('ALTER TABLE episode_characters DROP FOREIGN KEY FK_65920B901136BE75');
        $this->addSql('DROP TABLE characters');
        $this->addSql('DROP TABLE episode_characters');
        $this->addSql('DROP INDEX idx_season_episode ON episode');
        $this->addSql('DROP INDEX idx_air_date ON episode');
        $this->addSql('ALTER TABLE episode DROP external_id, DROP created_at, DROP updated_at, CHANGE name name VARCHAR(255) DEFAULT NULL, CHANGE air_date air_date DATE DEFAULT NULL, CHANGE season season VARCHAR(20) DEFAULT NULL, CHANGE episode_number episode_number VARCHAR(20) DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_rating ON review');
        $this->addSql('DROP INDEX idx_created_at ON review');
        $this->addSql('ALTER TABLE review ADD publication_date VARCHAR(255) DEFAULT NULL, DROP created_at, CHANGE author author VARCHAR(255) DEFAULT NULL, CHANGE text text VARCHAR(255) DEFAULT NULL, CHANGE rating rating DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE characters (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, gender VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, status VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, external_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX idx_status (status), INDEX idx_gender (gender), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE episode_characters (episode_id INT NOT NULL, character_id INT NOT NULL, INDEX IDX_65920B90362B62A0 (episode_id), INDEX IDX_65920B901136BE75 (character_id), PRIMARY KEY(episode_id, character_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE episode_characters ADD CONSTRAINT FK_65920B90362B62A0 FOREIGN KEY (episode_id) REFERENCES episode (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode_characters ADD CONSTRAINT FK_65920B901136BE75 FOREIGN KEY (character_id) REFERENCES characters (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE episode_character DROP FOREIGN KEY FK_2DB8260D362B62A0');
        $this->addSql('ALTER TABLE episode_character DROP FOREIGN KEY FK_2DB8260D1136BE75');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE episode_character');
        $this->addSql('ALTER TABLE review ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP publication_date, CHANGE author author VARCHAR(255) NOT NULL, CHANGE text text LONGTEXT NOT NULL, CHANGE rating rating DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE INDEX idx_rating ON review (rating)');
        $this->addSql('CREATE INDEX idx_created_at ON review (created_at)');
        $this->addSql('ALTER TABLE episode ADD external_id INT NOT NULL, ADD created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE name name VARCHAR(255) NOT NULL, CHANGE air_date air_date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', CHANGE season season INT NOT NULL, CHANGE episode_number episode_number INT NOT NULL, CHANGE url url VARCHAR(500) NOT NULL');
        $this->addSql('CREATE INDEX idx_season_episode ON episode (season, episode_number)');
        $this->addSql('CREATE INDEX idx_air_date ON episode (air_date)');
    }
}
