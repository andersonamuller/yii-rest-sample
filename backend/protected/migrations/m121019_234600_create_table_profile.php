<?php

class m121019_234600_create_table_profile extends CDbMigration
{
	public function up()
	{
		$this->createTable('profile', array(
			'id'         => 'pk',
			'picture'    => 'varchar(255)',
			'created_on' => 'datetime NOT NULL',
			'created_by' => 'integer NOT NULL',
			'updated_on' => 'datetime',
			'updated_by' => 'integer',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');
	}

	public function down()
	{
		$this->dropTable('profile');
	}
}
