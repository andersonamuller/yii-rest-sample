<?php
/**
 * This is the model class for table "person".
 *
 * The followings are the available columns in table 'person':
 * @property integer $id the identifier
 * @property integer $profile_id the profile identifier
 * @property string $first_name the person's first name(s)
 * @property string $last_name the person's last name(s)
 * @property string $birthdate the person's birthdate
 *
 * The followings are the available model relations:
 * @property Profile $profile the person's profile
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.models
 */
class Person extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'person';
	}

	/**
	 * @return array validation rules for model attributes that receive user input.
	 */
	public function rules()
	{
		return array(
			array(
				'profile_id, last_name',
				'required'
			),
			array(
				'profile_id',
				'numerical',
				'integerOnly' => true
			),
			array(
				'first_name, last_name',
				'length',
				'max' => 64
			),
			array(
				'birthdate',
				'safe'
			),
			array(
				'first_name, last_name, birthdate',
				'safe',
				'on' => 'search'
			),
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
			)
		);
	}

	/**
	 * @see ActiveRecord::behaviors()
	 */
	public function behaviors()
	{
		return CMap::mergeArray(parent::behaviors(), array(
			'array' => array(
				'additionalAttributes' => array(
					'fullName'
				)
			)
		));
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return CMap::mergeArray(parent::attributeLabels(), array(
			'profile_id' => 'Profile',
			'first_name' => 'First Name',
			'last_name'  => 'Last Name',
			'birthdate'  => 'Birthdate'
		));
	}

	/**
	 * @return string the person's full name
	 */
	public function getFullName()
	{
		$names = array();
		if (!empty($this->first_name)) {
			$names[] = $this->first_name;
		}
		if (!empty($this->last_name)) {
			$names[] = $this->last_name;
		}

		return implode(' ', $names);
	}
}
