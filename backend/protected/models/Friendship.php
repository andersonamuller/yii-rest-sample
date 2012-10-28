<?php
/**
 * This is the model class for table "friendship".
 *
 * The followings are the available columns in table 'friendship':
 * @property integer $id
 * @property integer $profile_id
 * @property integer $friend_id
 *
 * The followings are the available model relations:
 * @property Profile $friend
 * @property Profile $profile
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.models
 */
class Friendship extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'friendship';
	}

	/**
	 * @return array validation rules for model attributes that receive user input.
	 */
	public function rules()
	{
		return array(
			array(
				'profile_id, friend_id',
				'required'
			),
			array(
				'profile_id, friend_id',
				'numerical',
				'integerOnly' => true
			)
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'friend'  => array(
				self::BELONGS_TO,
				'Profile',
				'friend_id'
			),
			'profile' => array(
				self::BELONGS_TO,
				'Profile',
				'profile_id'
			),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return CMap::mergeArray(parent::attributeLabels(), array(
			'profile_id' => 'Profile',
			'friend_id'  => 'Friend'
		));
	}
}
