<?php

class m121020_142458_create_table_friendship extends CDbMigration
{
	public function up()
	{
		$this->createTable('friendship', array(
			'id'         => 'pk',
			'profile_id' => 'int NOT NULL',
			'friend_id'  => 'int NOT NULL',
			'created_on' => 'datetime NOT NULL',
			'created_by' => 'integer NOT NULL',
			'updated_on' => 'datetime',
			'updated_by' => 'integer',
		), 'ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

		$this->addForeignKey('fk_friendship_profile', 'friendship', 'profile_id', 'profile', 'id', 'CASCADE', 'CASCADE');
		$this->addForeignKey('fk_friendship_friend', 'friendship', 'friend_id', 'profile', 'id', 'CASCADE', 'CASCADE');
		$this->createIndex('ux_friendship', 'friendship', 'profile_id, friend_id', true);
	}

	public function down()
	{
		try {
			$this->dropIndex('ux_friendship', 'friendship');
		} catch (Exception $e) {
		}

		try {
			$this->dropForeignKey('fk_friendship_friend', 'friendship');
		} catch (Exception $e) {
		}

		try {
			$this->dropForeignKey('fk_friendship_profile', 'friendship');
		} catch (Exception $e) {
		}

		$this->dropTable('friendship');
	}
}
