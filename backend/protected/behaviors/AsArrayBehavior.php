<?php
/**
 * AsArrayBehavior converts a model object into an array.
 *
 * In case the model is a ActiveRecord it will also convert its relations.
 *
 * @author Anderson Müller
 * @version 0.1
 * @package application.behaviors
 */
class AsArrayBehavior extends CBehavior
{
	public $exceptAttributes = array();
	public $additionalAttributes = array();

	/**
	 * Does the convertion from a model object to an array
	 *
	 * @return array the attributes and relation attributes in case of active record
	 */
	public function asArray()
	{
		if ($this->owner instanceof CModel) {
			$attributes = $this->owner->getAttributes();

			if ($this->owner instanceof CActiveRecord) {
				$relations = $this->getRelations();

				foreach ($relations as $key => $value) {
					$attributes[$key] = $value;
				}
			}

			foreach ($this->additionalAttributes as $attribute) {
				eval('$attributes[\'' . str_replace('.', '\'][\'', $attribute) . '\'] = $this->owner->' . str_replace('.', '->', $attribute) . ';');
			}

			foreach ($this->exceptAttributes as $attribute) {
				eval('$hasAttribute = isset($attributes[\'' . str_replace('.', '\'][\'', $attribute) . '\']);');
				if ($hasAttribute) {
					eval('unset($attributes[\'' . str_replace('.', '\'][\'', $attribute) . '\']);');
				}
			}

			return $attributes;
		}

		return array();
	}

	/**
	 * Returns the relations of an active record
	 *
	 * @return array the realtions as (name => attributes)
	 */
	private function getRelations()
	{
		$relations = array();

		$model = null;

		$metaData = $this->owner->getMetaData();

		foreach ($metaData->relations as $name => $relation) {
			$model = $this->owner->getRelated($name);
			if ($model instanceof CModel) {
				$relations[$name] = $model->getAttributes();

				$behaviors = $model->behaviors();
				if (isset($behaviors['array'])) {
					$exceptAttributes = $model->asa('array')->exceptAttributes;
					foreach ($exceptAttributes as &$attribute) {
						$attribute = $name . '.' . $attribute;
					}

					$this->exceptAttributes = array_merge($this->exceptAttributes, $exceptAttributes);

					$additionalAttributes = $model->asa('array')->additionalAttributes;
					foreach ($additionalAttributes as &$attribute) {
						$attribute = $name . '.' . $attribute;
					}

					$this->additionalAttributes = array_merge($this->additionalAttributes, $additionalAttributes);
				}
			} else {
				$relations[$name] = $model;
			}

		}

		return $relations;
	}
}
