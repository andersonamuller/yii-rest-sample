<?php
/**
 * ActiveRecord adds default functionalities to all models extending from this class.
 *
 * @property string $created_on the date and time of when the record was created
 * @property integer $created_by the id of the user who created the record
 * @property string $updated_on the date and time of when the record was last updated
 * @property integer $updated_by the id of the user who last updated the record
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.components
 */
abstract class ActiveRecord extends CActiveRecord
{
	private $createdByAttribute = 'created_by';
	private $updatedByAttribute = 'updated_by';

	/**
	 * Returns the static model of the specified AR class.
	 * As of PHP 5.3
	 * @return ActiveRecord the static model class
	 */
	public static function model()
	{
		return parent::model(get_called_class());
	}

	/**
	 * Returns the attribute labels.
	 *
	 * @return array customized attribute labels (name=>label)
	 * @see CModel::attributeLabels()
	 */
	public function attributeLabels()
	{
		return CMap::mergeArray(parent::attributeLabels(), array(
			'id'         => 'ID',
			'created_on' => 'Created on',
			'created_by' => 'Created by',
			'updated_on' => 'Updated on',
			'updated_by' => 'Updated by',
		));
	}

	/**
	 * Returns a list of behaviors that this model should behave as.
	 *
	 * @return array the behavior configurations (behavior name=>behavior configuration)
	 * @see CModel::behaviors()
	 */
	public function behaviors()
	{
		return CMap::mergeArray(parent::behaviors(), array(
			'datetimelog' => array(
				'class'             => 'zii.behaviors.CTimestampBehavior',
				'createAttribute'   => 'created_on',
				'updateAttribute'   => 'updated_on',
				'setUpdateOnCreate' => true
			),
			'array'       => array(
				'class'            => 'AsArrayBehavior',
				'exceptAttributes' => array(
					'created_on',
					'created_by',
					'updated_on',
					'updated_by'
				)
			)
		));
	}

	/**
	 * This method is invoked before saving a record (after validation, if any).
	 *
	 * @return boolean whether the saving should be executed. Defaults to true.
	 * @see CActiveRecord::beforeSave()
	 */
	public function beforeSave()
	{
		if ($this->getIsNewRecord() && ($this->createdByAttribute !== null)) {
			$this->{$this->createdByAttribute} = Yii::app()->user->id;
		}
		if ((!$this->getIsNewRecord() || $this->asa('datetimelog')->setUpdateOnCreate) && ($this->updatedByAttribute !== null)) {
			$this->{$this->updatedByAttribute} = Yii::app()->user->id;
		}

		return parent::beforeSave();
	}

	/**
	 * Returns the default dependency check to cache a table
	 *
	 * @return CChainedCacheDependency
	 */
	public function getCacheDependency()
	{
		return new CChainedCacheDependency(array(
			new CDbCacheDependency('SELECT COUNT(id) FROM ' . $this->tableName()),
			new CDbCacheDependency('SELECT MAX(updated_on) FROM ' . $this->tableName())
		));
	}
}
