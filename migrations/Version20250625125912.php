<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625125912 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product_commande (id INT AUTO_INCREMENT NOT NULL, many_to_one_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, description VARCHAR(400) DEFAULT NULL, quantity INT NOT NULL, INDEX IDX_A55ACCEAEAB5DEB (many_to_one_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_commande ADD CONSTRAINT FK_A55ACCEAEAB5DEB FOREIGN KEY (many_to_one_id) REFERENCES commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD status_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D6BF700BD FOREIGN KEY (status_id) REFERENCES status (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_6EEAA67D6BF700BD ON commande (status_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE status DROP FOREIGN KEY FK_7B00651C82EA2E54
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_7B00651C82EA2E54 ON status
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE status ADD statut VARCHAR(255) NOT NULL, ADD description VARCHAR(255) DEFAULT NULL, DROP commande_id, CHANGE color color VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE product_commande DROP FOREIGN KEY FK_A55ACCEAEAB5DEB
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D6BF700BD
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_6EEAA67D6BF700BD ON commande
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE commande DROP status_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE status ADD commande_id INT NOT NULL, DROP statut, DROP description, CHANGE color color VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE status ADD CONSTRAINT FK_7B00651C82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7B00651C82EA2E54 ON status (commande_id)
        SQL);
    }
}
