<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260226223443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_image DROP FOREIGN KEY `FK_6C4FE9B8ED5CA9E6`');
        $this->addSql('DROP TABLE service_image');
        $this->addSql('ALTER TABLE service ADD image VARCHAR(255) DEFAULT NULL, CHANGE is_active is_active TINYINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E19D9AD2C53D045F ON service (image)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_image (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, updated_at DATETIME DEFAULT NULL, service_id INT NOT NULL, UNIQUE INDEX UNIQ_6C4FE9B8AC199498 (image_name), INDEX IDX_6C4FE9B8ED5CA9E6 (service_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_0900_ai_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE service_image ADD CONSTRAINT `FK_6C4FE9B8ED5CA9E6` FOREIGN KEY (service_id) REFERENCES service (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE session DROP FOREIGN KEY FK_D044D5D4ED5CA9E6');
        $this->addSql('DROP TABLE session');
        $this->addSql('DROP INDEX UNIQ_E19D9AD2C53D045F ON service');
        $this->addSql('ALTER TABLE service DROP image, CHANGE is_active is_active TINYINT DEFAULT 1 NOT NULL');
    }
}
