<?php
/**
 * UserController contains the actions related to the user entity
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
	 * @see CController::filters()
	 * @return array
	 */
	public function filters()
	{
		return array(
			array(
				'OnlyAdminFilter'
			)
		);
	}

	public function options()
	{
		return array(
			'list'   => array(
				'label' => 'List users',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('list')
			),
			'new'    => array(
				'label' => 'New user',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('new')
			),
			'view'   => array(
				'label' => 'View',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('view', array(
					'id' => $user->id
				))
			),
			'delete' => $authorizedUser->id == $user->id ? array() : array(
				'label' => 'Delete',
				'verb'  => 'DELETE',
				'url'   => $this->createAbsoluteUrl('delete')
			)
		);
	}

	/**
	 * Fetch all users
	 *
	 * @return void
	 */
	public function actionList()
	{
		$options = array(
			array(
				'label'  => 'List users',
				'verb'   => 'GET',
				'url'    => $this->createAbsoluteUrl('list'),
				'active' => true
			),
			array(
				'label' => 'New user',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('new')
			)
		);

		$authorizedUser = $this->getAuthorizedUser();

		$users = User::model()->with('profile', 'person')->findAll(array(
			'condition' => 't.created_by = :created_by AND t.id != :id',
			'params'    => array(
				':created_by' => $authorizedUser->id,
				':id'         => $authorizedUser->id
			)
		));

		$records = array();
		foreach ($users as $user) {
			$records[] = CMap::mergeArray($user->asArray(), array(
				'options' => array(
					'view'   => array(
						'label' => 'View',
						'verb'  => 'GET',
						'url'   => $this->createAbsoluteUrl('view', array(
							'id' => $user->id
						))
					),
					'delete' => $authorizedUser->id == $user->id ? array() : array(
						'label' => 'Delete',
						'verb'  => 'DELETE',
						'url'   => $this->createAbsoluteUrl('delete')
					)
				)
			));
		}

		$this->render(array(
			'options' => $options,
			'users'   => $records
		));
	}

	/**
	 * Fetch a user by id
	 *
	 * @return void
	 */
	public function actionView()
	{
		if (isset($_GET['id']) && is_numeric($_GET['id'])) {
			$user = User::model()->with('person')->findByPk((int) $_GET['id']);

			if ($user instanceof User) {
				$options = array(
					array(
						'label' => 'List users',
						'verb'  => 'GET',
						'url'   => $this->createAbsoluteUrl('list')
					),
					array(
						'label'  => 'View user',
						'verb'   => 'GET',
						'url'    => $this->createAbsoluteUrl('view', array(
							'id' => $user->id
						)),
						'active' => true
					)
				);

				$this->render(array(
					'options' => $options,
					'user'    => CMap::mergeArray($user->asArray(), array(
						'options' => array(
							'update' => array(
								'label' => 'Update',
								'verb'  => 'PUT',
								'url'   => $this->createAbsoluteUrl('update')
							),
							'delete' => array(
								'label' => 'Delete',
								'verb'  => 'DELETE',
								'url'   => $this->createAbsoluteUrl('delete')
							)
						)
					))
				));
			} else {
				$this->sendResponse(404);
			}
		} else {
			$this->sendResponse(400);
		}
	}

	/**
	 * Fetch a new user
	 *
	 * @return void
	 */
	public function actionNew()
	{
		$options = array(
			array(
				'label' => 'List users',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('list')
			),
			array(
				'label'  => 'New user',
				'verb'   => 'GET',
				'url'    => $this->createAbsoluteUrl('new'),
				'active' => true
			)
		);

		$user = new User();
		$user->person = new Person();

		$this->render(array(
			'options' => $options,
			'user'    => CMap::mergeArray($user->asArray(), array(
				'options' => array(
					'create' => array(
						'label' => 'Create',
						'verb'  => 'POST',
						'url'   => $this->createAbsoluteUrl('create')
					),
				)
			))
		));
	}

	/**
	 * Create a new user
	 *
	 * @return void
	 */
	public function actionCreate()
	{
		$user = new User();

		if (isset($_POST['User']) && is_array($_POST['User']) && isset($_POST['Person']) && is_array($_POST['Person'])) {
			$transaction = $user->dbConnection->beginTransaction();

			try {
				$user->attributes = $_POST['User'];

				$user->authorization = md5($user->username . ':' . Yii::app()->httpAuthentication->realm . ':' . $user->password);
				$user->password = md5($user->password);

				$valid = false;
				if ($user->profile_id === null) {
					$profile = new Profile;
					if ($profile->save()) {
						$person = new Person();
						$person->attributes = $_POST['Person'];
						$person->profile_id = $profile->id;
						$person->save();

						$user->profile_id = $profile->id;

						$valid = $user->validate();
					}
				}

				if ($valid && $user->save()) {
					$transaction->commit();

					$options = array(
						array(
							'label' => 'List users',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('list')
						),
						array(
							'label'  => 'View user',
							'verb'   => 'GET',
							'url'    => $this->createAbsoluteUrl('view', array(
								'id' => $user->id
							)),
							'active' => true
						)
					);

					$user = User::model()->with('person')->findByPk($user->id);

					$this->sendResponse(201, CJSON::encode(array(
						'options' => $options,
						'user'    => CMap::mergeArray($user->asArray(), array(
							'options' => array(
								'update' => array(
									'label' => 'Update',
									'verb'  => 'PUT',
									'url'   => $this->createAbsoluteUrl('update')
								),
								'delete' => array(
									'label' => 'Delete',
									'verb'  => 'DELETE',
									'url'   => $this->createAbsoluteUrl('delete')
								)
							)
						))
					)));
				}
			} catch (Exception $exception) {
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

		$this->sendResponse(500, CJSON::encode(array(
			'errors' => $errors
		)));
	}

	/**
	 * Update a user
	 *
	 * @access public
	 * @return void
	 */
	public function actionUpdate()
	{
		parse_str(file_get_contents('php://input'), $_PUT);

		if (isset($_PUT['User']['id']) && is_numeric($_PUT['User']['id']) && isset($_PUT['Person']) && is_array($_PUT['Person'])) {
			$authorizedUser = $this->getAuthorizedUser();

			$user = User::model()->with('person')->findByPk((int) $_PUT['User']['id'], array(
				'condition' => 't.created_by = :created_by',
				'params'    => array(
					':created_by' => $authorizedUser->id
				)
			));

			if ($user instanceof User) {
				$person = $user->person;
				$person->attributes = $_PUT['Person'];
				if ($person->save()) {
					$options = array(
						array(
							'label' => 'List users',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('list')
						),
						array(
							'label'  => 'View user',
							'verb'   => 'GET',
							'url'    => $this->createAbsoluteUrl('view', array(
								'id' => $user->id
							)),
							'active' => true
						)
					);

					$this->sendResponse(200, CJSON::encode(array(
						'options' => $options,
						'user'    => CMap::mergeArray($user->asArray(), array(
							'options' => array(
								'update' => array(
									'label' => 'Update',
									'verb'  => 'PUT',
									'url'   => $this->createAbsoluteUrl('update')
								),
								'delete' => array(
									'label' => 'Delete',
									'verb'  => 'DELETE',
									'url'   => $this->createAbsoluteUrl('delete')
								)
							)
						))
					)));
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

		$this->sendResponse(500, CJSON::encode(array(
			'errors' => $errors
		)));
	}

	/**
	 * Delete a user
	 *
	 * @access public
	 * @return void
	 */
	public function actionDelete()
	{
		parse_str(file_get_contents('php://input'), $_DELETE);

		if (isset($_DELETE['User']['id']) && is_numeric($_DELETE['User']['id'])) {
			$authorizedUser = $this->getAuthorizedUser();

			$user = User::model()->with('person')->findByPk((int) $_DELETE['User']['id'], array(
				'condition' => 't.created_by = :created_by AND t.id != :id',
				'params'    => array(
					':created_by' => $authorizedUser->id,
					':id'         => $authorizedUser->id
				)
			));

			if ($user instanceof User) {
				if ($user->delete()) {
					$options = array(
						array(
							'label' => 'List users',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('list')
						),
						array(
							'label' => 'New user',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('new')
						)
					);

					$this->sendResponse(200, CJSON::encode(array(
						'options' => $options
					)));
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

		$this->sendResponse(500, CJSON::encode(array(
			'errors' => $errors
		)));
	}
}
?>
