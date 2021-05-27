<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526133923 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE context (id INT AUTO_INCREMENT NOT NULL, import_id INT NOT NULL, number INT NOT NULL, adr INT NOT NULL, choice_one VARCHAR(10) DEFAULT NULL, choice_two VARCHAR(10) DEFAULT NULL, choice_three VARCHAR(10) DEFAULT NULL, choice_four VARCHAR(10) DEFAULT NULL, algo INT NOT NULL, evalution_case INT NOT NULL, half_context INT NOT NULL, product_identifier INT NOT NULL, datetime DATETIME DEFAULT NULL, velocimeter DOUBLE PRECISION NOT NULL, encr_one DOUBLE PRECISION NOT NULL, encr_two DOUBLE PRECISION NOT NULL, ratio_alarm DOUBLE PRECISION DEFAULT NULL, delta_seuil DOUBLE PRECISION DEFAULT NULL, temp_alarm DOUBLE PRECISION DEFAULT NULL, slope_seuil DOUBLE PRECISION DEFAULT NULL, INDEX IDX_E25D857EB6A263D9 (import_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE context ADD CONSTRAINT FK_E25D857EB6A263D9 FOREIGN KEY (import_id) REFERENCES import_context (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE context');
    }
}
