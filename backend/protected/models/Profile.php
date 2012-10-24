<?php
/**
 * This is the model class for table "profile".
 *
 * The followings are the available columns in table 'profile':
 * @property integer $id the identifier
 * @property string $picture the picture file path
 *
 * The followings are the available model relations:
 * @property Person $person the person who owns this profile
 * @property User $user the user linked to this profile
 * @property Profile[] $friends the profiles of friends of this profile
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.models
 */
class Profile extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'profile';
	}

	/**
	 * @return array validation rules for model attributes that receive user input.
	 */
	public function rules()
	{
		return array(
			array(
				'picture',
				'length',
				'max' => 255
			)
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'person'  => array(
				self::HAS_ONE,
				'Person',
				array(
					'profile_id' => 'id'
				)
			),
			'user'    => array(
				self::HAS_ONE,
				'User',
				array(
					'profile_id' => 'id'
				)
			),
			'friends' => array(
				self::MANY_MANY,
				'Profile',
				'friendship(profile_id, friend_id)',
				'with'  => 'person',
				'order' => 'person.birthdate DESC'
			)
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return CMap::mergeArray(parent::attributeLabels(), array(
			'picture' => 'Picture',
		));
	}
}
