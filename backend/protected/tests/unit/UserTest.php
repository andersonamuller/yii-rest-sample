<?php
class UserTest extends CDbTestCase
{
	public $fixtures = array(
		'users' => 'User',
	);

	public function testFindAdmin()
	{
		$user = User::model()->findByAttributes(array(
			'username' => $this->users['admin']['username']
		));

		$this->assertTrue($user instanceof User);
	}

	public function testCrud()
	{
		$username = 'test';
		$password = 'test123';

		$user = new User();
		$user->setAttributes(array(
			'username'      => $username,
			'password'      => md5($password),
			'authorization' => md5($username . ':' . Yii::app()->httpAuthentication->realm . ':' . $password)
		), false);
		$this->assertTrue($user->save(true));

		$user = User::model()->findByPk($user->id);
		$this->assertTrue($user instanceof User);

		$username = 'test2';

		$user->username = $username;
		$this->assertTrue($user->save(true));

		$user = User::model()->findByAttributes(array(
			'username' => $username
		));
		$this->assertTrue($user instanceof User);

		$this->assertTrue($user->delete());

		$user = User::model()->findByAttributes(array(
			'username' => $username
		));
		$this->assertFalse($user instanceof User);
	}
}
