<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210531120705 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE context ADD choice_one VARCHAR(10) DEFAULT NULL, ADD choice_two VARCHAR(10) DEFAULT NULL, ADD choice_three VARCHAR(10) DEFAULT NULL, ADD choice_four VARCHAR(10) DEFAULT NULL, DROP ecs, DROP equipment, DROP module, DROP `loop`, DROP zone, DROP choice_free');
        $this->addSql('ALTER TABLE user ADD token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE context ADD ecs VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD equipment VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD module VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD `loop` VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, ADD zone INT NOT NULL, ADD choice_free DOUBLE PRECISION DEFAULT NULL, DROP choice_one, DROP choice_two, DROP choice_three, DROP choice_four');
        $this->addSql('ALTER TABLE user DROP token');
    }
}
