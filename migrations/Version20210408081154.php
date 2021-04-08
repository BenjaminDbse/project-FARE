<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210408081154 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE algo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, coef1ratio DOUBLE PRECISION NOT NULL, coef1tmp DOUBLE PRECISION NOT NULL, coef2tmp DOUBLE PRECISION NOT NULL, coef2ratio DOUBLE PRECISION NOT NULL, xtmp DOUBLE PRECISION NOT NULL, xratio DOUBLE PRECISION NOT NULL, ytmp DOUBLE PRECISION NOT NULL, yratio DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE algo');
    }
}
