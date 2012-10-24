<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id the identifier
 * @property integer $profile_id the profile identifier
 * @property string $username the username to login
 * @property string $password the password to login
 * @property string $authorization the hash containt the necessary information for a http authentication
 *
 * The followings are the available model relations:
 * @property Profile $profile the user's profile
 * @property Person $person if the user is a person, this contains the person data like name and birthdate
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.models
 */
class User extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'user';
	}

	/**
	 * @return array validation rules for model attributes that receive user input.
	 */
	public function rules()
	{
		return array(
			array(
				'username, password',
				'required'
			),
			array(
				'username',
				'unique'
			),
			array(
				'profile_id',
				'numerical',
				'integerOnly' => true
			),
			array(
				'username',
				'length',
				'max' => 64
			),
			array(
				'password',
				'length',
				'max' => 128
			)
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'profile' => array(
				self::BELONGS_TO,
				'Profile',
				'profile_id'
			),
			'person'  => array(
				self::HAS_ONE,
				'Person',
				array(
					'profile_id' => 'id'
				),
				'through' => 'profile'
			)
		);
	}

	/**
	 * @see ActiveRecord::behaviors()
	 */
	public function behaviors()
	{
		return CMap::mergeArray(parent::behaviors(), array(
			'asarray' => array(
				'exceptAttributes' => array(
					'password',
					'authorization'
				)
			)
		));
	}

	/**
	 * @see ActiveRecord::attributeLabels()
	 */
	public function attributeLabels()
	{
		return CMap::mergeArray(parent::attributeLabels(), array(
			'profile_id'    => 'Profile',
			'username'      => 'Username',
			'password'      => 'Password',
			'authorization' => 'Authorization'
		));
	}

	/**
	 * Check if the user is the system administrator
	 *
	 * @return boolean if the user is the system administrator
	 */
	public function isAdmin()
	{
		return $this->username == 'admin';
	}

	/**
	 * Finds a single active record that has the specified username.
	 *
	 * @param string $username the username to search for
	 * @return CActiveRecord the record found. Null if none is found.
	 */
	public function findByUsername($username)
	{
		return $this->findByAttributes(array(
			'username' => $username
		));
	}
}
