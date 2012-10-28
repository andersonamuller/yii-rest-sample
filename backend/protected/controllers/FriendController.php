<?php
/**
 * FriendController contains the actions related to the friend entity.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.controllers
 */
class FriendController extends Controller
{
	/**
	 * Default action when is not defined in the route
	 * Respond with the available options to manage friends
	 *
	 * @return void
	 */
	public function actionIndex()
	{
		$this->render(array(
			'options' => array_merge($this->getOption('list'), $this->getOption('new'))
		));
	}

	/**
	 * Fetch all friends of the logged user
	 *
	 * @return void
	 */
	public function actionList()
	{
		$profiles = Yii::app()->user->profile->friends;

		$list = array();
		foreach ($profiles as $profile) {
			$options = array_merge($this->getOption('view', array(), array(
				'id' => $profile->id
			)), $this->getOption('delete'));

			$list[] = CMap::mergeArray($profile->asArray(), array(
				'options' => $options
			));
		}

		$options = array_merge($this->getOption('list', array(
			'active' => true
		)), $this->getOption('new'));

		$this->render(array(
			'options' => $options,
			'list'    => $list
		));
	}

	/**
	 * Fetch a friend by id
	 *
	 * @return void
	 */
	public function actionView()
	{
		$id = Yii::app()->request->getParam('id');

		if ($id > 0) {
			$profile = Profile::model()->with('person')->findByPk($id, array(
				'condition' => 't.created_by = :user_id OR t.id = :user_id',
				'params'    => array(
					':user_id' => Yii::app()->user->id
				)
			));

			if ($profile instanceof Profile) {
				$options = array_merge($this->getOption('list'), $this->getOption('view', array(
					'active' => true
				), array(
					'id' => $profile->id
				)));

				$profile = CMap::mergeArray($profile->asArray(), array(
					'options' => array_merge($this->getOption('update'), $this->getOption('delete'))
				));

				$this->render(array(
					'options' => $options,
					'data'    => $profile
				));
			} else {
				$this->sendResponse(404);
			}
		} else {
			$this->sendResponse(400);
		}
	}

	/**
	 * Fetch a new friend with the person data
	 *
	 * @return void
	 */
	public function actionNew()
	{
		$profile = new Profile();
		$profile->person = new Person();

		$profile = CMap::mergeArray($profile->asArray(), array(
			'options' => $this->getOption('create')
		));

		$options = array_merge($this->getOption('list'), $this->getOption('new', array(
			'active' => true
		)));

		$this->render(array(
			'options' => $options,
			'data'    => $profile
		));
	}

	/**
	 * Create a new friend with a profile picture and person data
	 *
	 * @return void
	 */
	public function actionCreate()
	{
		$profileAttributes = Yii::app()->request->getPost('Profile');
		$personAttributes = Yii::app()->request->getPost('Person');

		$errors = array();

		if (is_array($profileAttributes) && is_array($personAttributes)) {
			$profile = new Profile();

			$transaction = $profile->dbConnection->beginTransaction();

			try {
				if ($valid = $profile->save()) {
					$person = new Person();
					$person->attributes = $personAttributes;
					$person->profile_id = $profile->id;
					$valid = $person->save() && $valid;

					$friendship = new Friendship();
					$friendship->profile_id = Yii::app()->user->profile_id;
					$friendship->friend_id = $profile->id;
					$valid = $friendship->save() && $valid;
				}

				if ($valid) {
					if (is_array($profileAttributes['picture'])) {
						$pictureFileName = $profile->id . '.' . CFileHelper::getExtension($profileAttributes['picture']['name']);
						$pictureFilePath = $profile->picturePath . $pictureFileName;

						$image = new Imagick();
						$image->readImageBlob($profileAttributes['picture']['content']);

						$maxSize = 200;
						if ($image->getImageHeight() <= $image->getImageWidth()) {
							$image->resizeImage($maxSize, 0, Imagick::FILTER_LANCZOS, 1);
						} else {
							$image->resizeImage(0, $maxSize, Imagick::FILTER_LANCZOS, 1);
						}
						$image->stripImage();
						$image->writeImage($pictureFilePath);
						$image->destroy();

						$profile->picture = $pictureFileName;
						$profile->save();
					}

					$transaction->commit();

					$profile = Profile::model()->with('person')->findByPk($profile->id);
					if ($profile instanceof Profile) {
						$options = array_merge($this->getOption('list'), $this->getOption('view', array(
							'active' => true
						), array(
							'id' => $profile->id
						)));

						$profile = CMap::mergeArray($profile->asArray(), array(
							'options' => array_merge($this->getOption('update'), $this->getOption('delete'))
						));

						$this->sendResponse(201, $this->render(array(
							'options' => $options,
							'data'    => $profile
						), true));
					} else {
						$this->sendResponse(404);
					}
				}
			} catch (Exception $exception) {
				$errors['action'][] = $exception->getMessage();
				$transaction->rollback();
			}
		} else {
			$this->sendResponse(400);
		}

		if (isset($profile) && (count($profile->errors) > 0)) {
			$errors['profile'] = $profile->errors;
		}
		if (isset($person) && (count($person->errors) > 0)) {
			$errors['person'] = $person->errors;
		}

		$this->sendResponse(500, $this->render(array(
			'errors' => $errors
		), true));
	}

	/**
	 * Update a friend person data
	 *
	 * @access public
	 * @return void
	 */
	public function actionUpdate()
	{
		$profileAttributes = Yii::app()->request->getPut('Profile');
		$personAttributes = Yii::app()->request->getPut('Person');

		if (is_array($profileAttributes) && is_numeric($profileAttributes['id']) && is_array($personAttributes)) {
			$profile = Profile::model()->with('person')->findByPk($profileAttributes['id'], array(
				'condition' => 't.created_by = :user_id',
				'params'    => array(
					':user_id' => Yii::app()->user->id
				)
			));

			if ($profile instanceof Profile) {
				$person = $profile->person;
				$person->attributes = $personAttributes;
				if ($person->save()) {
					$options = array_merge($this->getOption('list'), $this->getOption('view', array(
						'active' => true
					), array(
						'id' => $profile->id
					)));

					$profile = CMap::mergeArray($profile->asArray(), array(
						'options' => array_merge($this->getOption('update'), $this->getOption('delete'))
					));

					$this->sendResponse(200, $this->render(array(
						'options' => $options,
						'data'    => $profile
					), true));
				}
			} else {
				$this->sendResponse(404);
			}
		} else {
			$this->sendResponse(400);
		}

		if (isset($person) && (count($person->errors) > 0)) {
			$errors['person'] = $person->errors;
		}

		$this->sendResponse(500, $this->render(array(
			'errors' => $errors
		), true));
	}

	/**
	 * Delete a friend
	 *
	 * @access public
	 * @return void
	 */
	public function actionDelete()
	{
		$profileAttributes = Yii::app()->request->getDelete('Profile');

		if (is_array($profileAttributes) && is_numeric($profileAttributes['id'])) {
			$profile = Profile::model()->findByPk($profileAttributes['id'], array(
				'condition' => 't.created_by = :user_id AND t.id != :user_id',
				'params'    => array(
					':user_id' => Yii::app()->user->id
				)
			));

			if ($profile instanceof Profile) {
				if ($profile->delete()) {
					$options = array_merge($this->getOption('list'), $this->getOption('new'));

					$this->sendResponse(200, $this->render(array(
						'options' => $options
					), true));
				}
			} else {
				$this->sendResponse(404);
			}
		} else {
			$this->sendResponse(400);
		}

		if (isset($profile) && (count($profile->errors) > 0)) {
			$errors['profile'] = $profile->errors;
		}

		$this->sendResponse(500, $this->render(array(
			'errors' => $errors
		), true));
	}
}
?>
