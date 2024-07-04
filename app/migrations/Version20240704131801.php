<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240704131801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE question_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE test_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE test_answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE test_result_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE answer (id INT NOT NULL, question_id INT NOT NULL, text VARCHAR(255) NOT NULL, bit INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
        $this->addSql('CREATE TABLE question (id INT NOT NULL, test_id INT NOT NULL, text VARCHAR(255) NOT NULL, key INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494E1E5D0459 ON question (test_id)');
        $this->addSql('COMMENT ON COLUMN question.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN question.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE test (id INT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE test_answer (id INT NOT NULL, test_result_id INT NOT NULL, question_id INT NOT NULL, answers INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4D044D0B853A2189 ON test_answer (test_result_id)');
        $this->addSql('CREATE INDEX IDX_4D044D0B1E27F6BF ON test_answer (question_id)');
        $this->addSql('COMMENT ON COLUMN test_answer.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE test_result (id INT NOT NULL, user_id_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_84B3C63D9D86650F ON test_result (user_id_id)');
        $this->addSql('COMMENT ON COLUMN test_result.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, name VARCHAR(64) NOT NULL, email VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "user".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E1E5D0459 FOREIGN KEY (test_id) REFERENCES test (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_answer ADD CONSTRAINT FK_4D044D0B853A2189 FOREIGN KEY (test_result_id) REFERENCES test_result (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_answer ADD CONSTRAINT FK_4D044D0B1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE test_result ADD CONSTRAINT FK_84B3C63D9D86650F FOREIGN KEY (user_id_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE answer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE question_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE test_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE test_answer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE test_result_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE answer DROP CONSTRAINT FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494E1E5D0459');
        $this->addSql('ALTER TABLE test_answer DROP CONSTRAINT FK_4D044D0B853A2189');
        $this->addSql('ALTER TABLE test_answer DROP CONSTRAINT FK_4D044D0B1E27F6BF');
        $this->addSql('ALTER TABLE test_result DROP CONSTRAINT FK_84B3C63D9D86650F');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE test');
        $this->addSql('DROP TABLE test_answer');
        $this->addSql('DROP TABLE test_result');
        $this->addSql('DROP TABLE "user"');
    }
}
