<?php

class m121019_234634_create_table_person extends CDbMigration
{
	public function up()
	{
		$this->createTable('person', array(
			'id'         => 'pk',
			'profile_id' => 'int NOT NULL',
			'first_name' => 'varchar(64)',
			'last_name'  => 'varchar(64) NOT NULL',
			'birthdate'  => 'date',
			'created_on' => 'datetime NOT NULL',
			'created_by' => 'integer NOT NULL',
			'updated_on' => 'datetime',
			'updated_by' => 'integer',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->addForeignKey('fk_person_profile', 'person', 'profile_id', 'profile', 'id', 'CASCADE', 'CASCADE');
		$this->createIndex('ix_birthdate', 'person', 'birthdate');
	}

	public function down()
	{
		try {
			$this->dropIndex('ix_birthdate', 'person');
		} catch (Exception $e) {
		}

		try {
			$this->dropForeignKey('fk_person_profile', 'person');
		} catch (Exception $e) {
		}

		$this->dropTable('person');
	}
}
