<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210302110944 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE data ADD filter_ratio DOUBLE PRECISION DEFAULT NULL, ADD temperature_correction DOUBLE PRECISION DEFAULT NULL, ADD slope_temperature_correction DOUBLE PRECISION DEFAULT NULL, ADD raw_co DOUBLE PRECISION DEFAULT NULL, ADD co_correction DOUBLE PRECISION DEFAULT NULL, ADD datetime DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE data DROP filter_ratio, DROP temperature_correction, DROP slope_temperature_correction, DROP raw_co, DROP co_correction, DROP datetime');
    }
}
