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
				self::BELONGS_TO,
				'Person',
				array(
					'id' => 'profile_id'
				)
			),
			'user'    => array(
				self::BELONGS_TO,
				'User',
				array(
					'id' => 'profile_id'
				)
			),
			'friends' => array(
				self::MANY_MANY,
				'Profile',
				'friendship(profile_id, friend_id)',
				'with'   => 'person',
				'select' => array(
					'DATEDIFF(ADDDATE(ADDDATE(birthdate, INTERVAL YEAR(NOW()) - YEAR(birthdate) YEAR), INTERVAL ADDDATE(birthdate, INTERVAL YEAR(NOW()) - YEAR(birthdate) YEAR) < DATE(NOW()) YEAR), NOW()) AS days_to_birthday'
				),
				'order'  => 'days_to_birthday ASC'
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
					'pictureUrl'
				)
			)
		));
	}

	public function afterDelete()
	{
		unlink($this->getPicturePath());

		parent::afterDelete();
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

	/**
	 * @return string the profile picture url
	 */
	public function getPictureUrl()
	{
		return Yii::app()->getBaseUrl(true) . '/' . Yii::app()->params->uploadDirectory . '/' . $this->picture;
	}

	/**
	 * @return string the profile picture path
	 */
	public function getPicturePath()
	{
		return realpath(Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR . Yii::app()->params->uploadDirectory . DIRECTORY_SEPARATOR . $this->picture;
	}
}
