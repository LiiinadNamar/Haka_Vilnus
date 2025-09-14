<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250913131505 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE issue (id SERIAL NOT NULL, priority_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, summary VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_12AD233E497B19F9 ON issue (priority_id)');
        $this->addSql('CREATE TABLE label (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, prompt VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE label_issue (label_id INT NOT NULL, issue_id INT NOT NULL, PRIMARY KEY(label_id, issue_id))');
        $this->addSql('CREATE INDEX IDX_80B3953033B92F39 ON label_issue (label_id)');
        $this->addSql('CREATE INDEX IDX_80B395305E7AA58C ON label_issue (issue_id)');
        $this->addSql('CREATE TABLE message (id SERIAL NOT NULL, issue_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307F5E7AA58C ON message (issue_id)');
        $this->addSql('CREATE TABLE priority (id SERIAL NOT NULL, number INT NOT NULL, prompt VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE issue ADD CONSTRAINT FK_12AD233E497B19F9 FOREIGN KEY (priority_id) REFERENCES priority (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE label_issue ADD CONSTRAINT FK_80B3953033B92F39 FOREIGN KEY (label_id) REFERENCES label (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE label_issue ADD CONSTRAINT FK_80B395305E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F5E7AA58C FOREIGN KEY (issue_id) REFERENCES issue (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE issue DROP CONSTRAINT FK_12AD233E497B19F9');
        $this->addSql('ALTER TABLE label_issue DROP CONSTRAINT FK_80B3953033B92F39');
        $this->addSql('ALTER TABLE label_issue DROP CONSTRAINT FK_80B395305E7AA58C');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F5E7AA58C');
        $this->addSql('DROP TABLE issue');
        $this->addSql('DROP TABLE label');
        $this->addSql('DROP TABLE label_issue');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE priority');
    }
}
