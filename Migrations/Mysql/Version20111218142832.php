<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Add the encryption-keypair table
 */
class Version20111218142832 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE typo3_surf_encryption_keypair (flow3_persistence_identifier VARCHAR(40) NOT NULL, privatekey VARCHAR(255) DEFAULT NULL, publickey VARCHAR(255) DEFAULT NULL, encrypted TINYINT(1) DEFAULT NULL, PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("DROP TABLE typo3_surf_encryption_keypair");
	}
}

?>