<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210607080742 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE algo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, coef1ratio DOUBLE PRECISION NOT NULL, coef1tmp DOUBLE PRECISION NOT NULL, coef2tmp DOUBLE PRECISION NOT NULL, coef2ratio DOUBLE PRECISION NOT NULL, xtmp DOUBLE PRECISION NOT NULL, xratio DOUBLE PRECISION NOT NULL, ytmp DOUBLE PRECISION NOT NULL, yratio DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE context (id INT AUTO_INCREMENT NOT NULL, import_id INT NOT NULL, number INT NOT NULL, algo INT NOT NULL, evalution_case INT NOT NULL, half_context INT NOT NULL, product_identifier INT NOT NULL, datetime DATETIME DEFAULT NULL, velocimeter DOUBLE PRECISION DEFAULT NULL, encr_one DOUBLE PRECISION NOT NULL, encr_two DOUBLE PRECISION NOT NULL, ratio_alarm DOUBLE PRECISION DEFAULT NULL, delta_seuil DOUBLE PRECISION DEFAULT NULL, temp_alarm DOUBLE PRECISION DEFAULT NULL, slope_seuil DOUBLE PRECISION NOT NULL, INDEX IDX_E25D857EB6A263D9 (import_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE context_data (id INT AUTO_INCREMENT NOT NULL, context_id INT NOT NULL, ratio DOUBLE PRECISION NOT NULL, delta1 DOUBLE PRECISION NOT NULL, pulse1 DOUBLE PRECISION NOT NULL, delta2 DOUBLE PRECISION NOT NULL, pulse2 DOUBLE PRECISION NOT NULL, temp_raw DOUBLE PRECISION NOT NULL, temp_corrected DOUBLE PRECISION NOT NULL, co DOUBLE PRECISION NOT NULL, INDEX IDX_649FB7526B00C1CF (context_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE data (id INT AUTO_INCREMENT NOT NULL, import_id INT NOT NULL, delta1 DOUBLE PRECISION NOT NULL, delta2 DOUBLE PRECISION NOT NULL, filter_ratio DOUBLE PRECISION NOT NULL, temperature_correction DOUBLE PRECISION NOT NULL, slope_temperature_correction DOUBLE PRECISION NOT NULL, raw_co DOUBLE PRECISION NOT NULL, co_correction DOUBLE PRECISION NOT NULL, datetime DATETIME NOT NULL, adr INT NOT NULL, status INT NOT NULL, alarm INT DEFAULT NULL, INDEX IDX_ADF3F363B6A263D9 (import_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import (id INT AUTO_INCREMENT NOT NULL, author_id INT DEFAULT NULL, category_id INT NOT NULL, title VARCHAR(255) NOT NULL, datetime DATETIME DEFAULT NULL, INDEX IDX_9D4ECE1DF675F31B (author_id), INDEX IDX_9D4ECE1D12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `leading` (id INT AUTO_INCREMENT NOT NULL, import_id INT NOT NULL, ecs INT NOT NULL, equipment INT NOT NULL, module INT NOT NULL, adr INT NOT NULL, looping INT NOT NULL, zone INT NOT NULL, UNIQUE INDEX UNIQ_F6FB1BD2B6A263D9 (import_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, verified TINYINT(1) DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE context ADD CONSTRAINT FK_E25D857EB6A263D9 FOREIGN KEY (import_id) REFERENCES import (id)');
        $this->addSql('ALTER TABLE context_data ADD CONSTRAINT FK_649FB7526B00C1CF FOREIGN KEY (context_id) REFERENCES context (id)');
        $this->addSql('ALTER TABLE data ADD CONSTRAINT FK_ADF3F363B6A263D9 FOREIGN KEY (import_id) REFERENCES import (id)');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DF675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1D12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE `leading` ADD CONSTRAINT FK_F6FB1BD2B6A263D9 FOREIGN KEY (import_id) REFERENCES import (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1D12469DE2');
        $this->addSql('ALTER TABLE context_data DROP FOREIGN KEY FK_649FB7526B00C1CF');
        $this->addSql('ALTER TABLE context DROP FOREIGN KEY FK_E25D857EB6A263D9');
        $this->addSql('ALTER TABLE data DROP FOREIGN KEY FK_ADF3F363B6A263D9');
        $this->addSql('ALTER TABLE `leading` DROP FOREIGN KEY FK_F6FB1BD2B6A263D9');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1DF675F31B');
        $this->addSql('DROP TABLE algo');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE context');
        $this->addSql('DROP TABLE context_data');
        $this->addSql('DROP TABLE data');
        $this->addSql('DROP TABLE import');
        $this->addSql('DROP TABLE `leading`');
        $this->addSql('DROP TABLE user');
    }
}
