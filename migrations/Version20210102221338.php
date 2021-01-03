<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210102221338 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "graph" (id BLOB NOT NULL --(DC2Type:uuid)
        , user_id BLOB DEFAULT NULL --(DC2Type:uuid)
        , name VARCHAR(125) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_94505DCA76ED395 ON "graph" (user_id)');
        $this->addSql('CREATE TABLE "shortest_path" (id BLOB NOT NULL --(DC2Type:uuid)
        , graph_id BLOB NOT NULL --(DC2Type:uuid)
        , from_node_id VARCHAR(36) NOT NULL, to_node_id VARCHAR(36) NOT NULL, status VARCHAR(255) NOT NULL, data_file VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FFA0CE0B99134837 ON "shortest_path" (graph_id)');
        $this->addSql('CREATE TABLE "user" (id BLOB NOT NULL --(DC2Type:uuid)
        , email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, token VARCHAR(64) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "graph"');
        $this->addSql('DROP TABLE "shortest_path"');
        $this->addSql('DROP TABLE "user"');
    }
}
