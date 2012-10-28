<?php
/**
 * UserController contains the actions related to the user entity.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.controllers
 */
class UserController extends Controller
{
	/**
	 * Only the admin user is allowed to execute actions from this controller
	 *
	 * @return array of filters
	 * @see CController::filters()
	 */
	public function filters()
	{
		return array(
			array(
				'OnlyAdminFilter'
			)
		);
	}

	/**
	 * Default action when is not defined in the route
	 * Respond with the available options to manage users
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
	 * Fetch all users except the admin
	 *
	 * @return void
	 */
	public function actionList()
	{
		$users = User::model()->with('person')->findAll(array(
			'condition' => 't.created_by = :user_id AND t.id != :user_id',
			'params'    => array(
				':user_id' => Yii::app()->user->id
			)
		));

		$list = array();
		foreach ($users as $user) {
			$options = $this->getOption('view', array(), array(
				'id' => $user->id
			));

			if ((Yii::app()->user->id == $user->id) || (Yii::app()->user->id == $user->created_by)) {
				$options = array_merge($options, $this->getOption('delete'));
			}

			$list[] = CMap::mergeArray($user->asArray(), array(
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
	 * Fetch a user by id
	 *
	 * @return void
	 */
	public function actionView()
	{
		$id = Yii::app()->request->getParam('id');

		if ($id > 0) {
			$user = User::model()->with('person')->findByPk($id, array(
				'condition' => 't.created_by = :user_id OR t.id = :user_id',
				'params'    => array(
					':user_id' => Yii::app()->user->id
				)
			));

			if ($user instanceof User) {
				$options = array_merge($this->getOption('list'), $this->getOption('view', array(
					'active' => true
				), array(
					'id' => $user->id
				)));

				$user = CMap::mergeArray($user->asArray(), array(
					'options' => array_merge($this->getOption('update'), $this->getOption('delete'))
				));

				$this->render(array(
					'options' => $options,
					'data'    => $user
				));
			} else {
				$this->sendResponse(404);
			}
		} else {
			$this->sendResponse(400);
		}
	}

	/**
	 * Fetch a new user with profile and person data
	 *
	 * @return void
	 */
	public function actionNew()
	{
		$user = new User();
		$user->person = new Person();

		$user = CMap::mergeArray($user->asArray(), array(
			'options' => $this->getOption('create')
		));

		$options = array_merge($this->getOption('list'), $this->getOption('new', array(
			'active' => true
		)));

		$this->render(array(
			'options' => $options,
			'data'    => $user
		));
	}

	/**
	 * Create a new user, with profile and person data
	 *
	 * @return void
	 */
	public function actionCreate()
	{
		$userAttributes = Yii::app()->request->getPost('User');
		$personAttributes = Yii::app()->request->getPost('Person');

		$errors = array();

		if (is_array($userAttributes) && is_array($personAttributes)) {
			$user = new User();

			$transaction = $user->dbConnection->beginTransaction();

			try {
				$user->attributes = $userAttributes;

				$user->authorization = md5($user->username . ':' . Yii::app()->httpAuthentication->realm . ':' . $user->password);
				$user->password = md5($user->password);

				$valid = false;
				if ($user->profile_id === null) {
					$profile = new Profile();
					if ($profile->save()) {
						$person = new Person();
						$person->attributes = $personAttributes;
						$person->profile_id = $profile->id;
						$person->save();

						$user->profile_id = $profile->id;

						$valid = $user->validate();
					}
				}

				if ($valid && $user->save()) {
					$transaction->commit();

					$user = User::model()->with('person')->findByPk($user->id);
					if ($user instanceof User) {
						$options = array_merge($this->getOption('list'), $this->getOption('view', array(
							'active' => true
						), array(
							'id' => $user->id
						)));

						$user = CMap::mergeArray($user->asArray(), array(
							'options' => array_merge($this->getOption('update'), $this->getOption('delete'))
						));

						$this->sendResponse(201, $this->render(array(
							'options' => $options,
							'data'    => $user
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

		if (isset($user) && (count($user->errors) > 0)) {
			$errors['user'] = $user->errors;
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
	 * Update a user person data
	 *
	 * @access public
	 * @return void
	 */
	public function actionUpdate()
	{
		$userAttributes = Yii::app()->request->getPut('User');
		$personAttributes = Yii::app()->request->getPut('Person');

		if (is_array($userAttributes) && is_numeric($userAttributes['id']) && is_array($personAttributes)) {
			$user = User::model()->with('person')->findByPk($userAttributes['id'], array(
				'condition' => 't.created_by = :user_id',
				'params'    => array(
					':user_id' => Yii::app()->user->id
				)
			));

			if ($user instanceof User) {
				$person = $user->person;
				$person->attributes = $personAttributes;
				if ($person->save()) {
					$options = array_merge($this->getOption('list'), $this->getOption('view', array(
						'active' => true
					), array(
						'id' => $user->id
					)));

					$user = CMap::mergeArray($user->asArray(), array(
						'options' => array_merge($this->getOption('update'), $this->getOption('delete'))
					));

					$this->sendResponse(200, $this->render(array(
						'options' => $options,
						'data'    => $user
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
	 * Delete a user
	 *
	 * @access public
	 * @return void
	 */
	public function actionDelete()
	{
		$userAttributes = Yii::app()->request->getDelete('User');

		if (is_array($userAttributes) && is_numeric($userAttributes['id'])) {
			$user = User::model()->findByPk($userAttributes['id'], array(
				'condition' => 't.created_by = :user_id AND t.id != :user_id',
				'params'    => array(
					':user_id' => Yii::app()->user->id
				)
			));

			if ($user instanceof User) {
				if ($user->delete()) {
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

		if (isset($user) && (count($user->errors) > 0)) {
			$errors['user'] = $user->errors;
		}

		$this->sendResponse(500, $this->render(array(
			'errors' => $errors
		), true));
	}
}
?>
