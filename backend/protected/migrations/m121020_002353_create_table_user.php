<?php

class m121020_002353_create_table_user extends CDbMigration
{
	public function up()
	{
		$this->createTable('user', array(
			'id'            => 'pk',
			'profile_id'    => 'int',
			'username'      => 'varchar(64) NOT NULL',
			'password'      => 'varchar(128) NOT NULL',
			'authorization' => 'varchar(128)',
			'created_on'    => 'datetime NOT NULL',
			'created_by'    => 'integer NOT NULL',
			'updated_on'    => 'datetime',
			'updated_by'    => 'integer',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->addForeignKey('fk_user_profile', 'user', 'profile_id', 'profile', 'id', 'CASCADE', 'CASCADE');
		$this->createIndex('ux_username', 'user', 'username', true);

		$id = 1;

		$username = 'admin';
		$password = 'admin123';

		$this->insert('user', array(
			'id'            => $id,
			'username'      => $username,
			'password'      => md5($password),
			'authorization' => md5($username . ':' . Yii::app()->httpAuthentication->realm . ':' . $password),
			'created_on'    => date('Y-m-d H:i:s'),
			'created_by'    => $id
		));
	}

	public function down()
	{
		$this->delete('user', array(
			'username = :username'
		), array(
			':username' => 'admin'
		));

		try {
			$this->dropIndex('ux_username', 'person');
		} catch (Exception $e) {
		}

		try {
			$this->dropForeignKey('fk_user_person', 'person');
		} catch (Exception $e) {
		}

		$this->dropTable('user');
	}
}
