<?php
/**
 * FriendController contains the actions related to the friend entity
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.controllers
 */
class FriendController extends Controller
{
	/**
	 * Fetch all friends
	 *
	 * @return void
	 */
	public function actionList()
	{
		$actions = array(
			array(
				'label'  => 'List friends',
				'verb'   => 'GET',
				'url'    => $this->createAbsoluteUrl('list'),
				'active' => true
			),
			array(
				'label' => 'New friend',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('new')
			)
		);

		$authorizedUser = $this->getAuthorizedUser();

		$friends = $authorizedUser->person->profile->friends;

		$records = array();
		foreach ($friends as $friend) {
			$records[] = CMap::mergeArray($friend->asArray(), array(
				'actions' => array(
					'view'   => array(
						'label' => 'View',
						'verb'  => 'GET',
						'url'   => $this->createAbsoluteUrl('view', array(
							'id' => $friend->id
						))
					),
					'delete' => $authorizedUser->id == $friend->created_by ? array() : array(
						'label' => 'Delete',
						'verb'  => 'DELETE',
						'url'   => $this->createAbsoluteUrl('delete')
					)
				)
			));
		}

		$this->render(array(
			'actions' => $actions,
			'friends' => $records
		));
	}

	/**
	 * Fetch a friend by id
	 *
	 * @return void
	 */
	public function actionView()
	{
		if (isset($_GET['id']) && is_numeric($_GET['id'])) {
			$authorizedUser = $this->getAuthorizedUser();

			$friend = Profile::model()->with('person')->findByPk((int) $_GET['id'], array(
				'condition' => 't.created_by = :created_by',
				'params'    => array(
					':created_by' => $authorizedUser->id
				)
			));

			if ($friend instanceof Profile) {
				$actions = array(
					array(
						'label' => 'List friends',
						'verb'  => 'GET',
						'url'   => $this->createAbsoluteUrl('list')
					),
					array(
						'label'  => 'View friend',
						'verb'   => 'GET',
						'url'    => $this->createAbsoluteUrl('view', array(
							'id' => $friend->id
						)),
						'active' => true
					)
				);

				$this->render(array(
					'actions' => $actions,
					'friend'  => CMap::mergeArray($friend->asArray(), array(
						'actions' => array(
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
	 * Fetch a new friend
	 *
	 * @return void
	 */
	public function actionNew()
	{
		$actions = array(
			array(
				'label' => 'List friends',
				'verb'  => 'GET',
				'url'   => $this->createAbsoluteUrl('list')
			),
			array(
				'label'  => 'New friend',
				'verb'   => 'GET',
				'url'    => $this->createAbsoluteUrl('new'),
				'active' => true
			)
		);

		$friend = new Profile();
		$friend->person = new Person();

		$this->render(array(
			'actions' => $actions,
			'friend'  => CMap::mergeArray($friend->asArray(), array(
				'actions' => array(
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
	 * Create a new friend
	 *
	 * @return void
	 */
	public function actionCreate()
	{
		$friend = new Profile();

		if (isset($_POST['Profile']) && is_array($_POST['Profile']) && isset($_POST['Person']) && is_array($_POST['Person'])) {
			$transaction = $friend->dbConnection->beginTransaction();

			try {
				$friend->attributes = $_POST['Profile'];

				$friend->authorization = md5($friend->friendname . ':' . Yii::app()->httpAuthentication->realm . ':' . $friend->password);
				$friend->password = md5($friend->password);

				$valid = false;
				if ($friend->profile_id === null) {
					$profile = new Profile;
					if ($profile->save()) {
						$person = new Person();
						$person->attributes = $_POST['Person'];
						$person->profile_id = $profile->id;
						$person->save();

						$friend->profile_id = $profile->id;

						$valid = $friend->validate();
					}
				}

				if ($valid && $friend->save()) {
					$transaction->commit();

					$actions = array(
						array(
							'label' => 'List friends',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('list')
						),
						array(
							'label'  => 'View friend',
							'verb'   => 'GET',
							'url'    => $this->createAbsoluteUrl('view', array(
								'id' => $friend->id
							)),
							'active' => true
						)
					);

					$friend = Profile::model()->with('person')->findByPk($friend->id);

					$this->sendResponse(201, CJSON::encode(array(
						'actions' => $actions,
						'friend'  => CMap::mergeArray($friend->asArray(), array(
							'actions' => array(
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

		if (isset($friend) && (count($friend->errors) > 0)) {
			$errors['friend'] = $friend->errors;
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
	 * Update a friend
	 *
	 * @access public
	 * @return void
	 */
	public function actionUpdate()
	{
		parse_str(file_get_contents('php://input'), $_PUT);

		if (isset($_PUT['Profile']['id']) && is_numeric($_PUT['Profile']['id']) && isset($_PUT['Person']) && is_array($_PUT['Person'])) {
			$authorizedUser = $this->getAuthorizedUser();

			$friend = Profile::model()->with('person')->findByPk((int) $_PUT['Profile']['id'], array(
				'condition' => 't.created_by = :created_by',
				'params'    => array(
					':created_by' => $authorizedUser->id
				)
			));

			if ($friend instanceof Profile) {
				$person = $friend->person;
				$person->attributes = $_PUT['Person'];
				if ($person->save()) {
					$actions = array(
						array(
							'label' => 'List friends',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('list')
						),
						array(
							'label'  => 'View friend',
							'verb'   => 'GET',
							'url'    => $this->createAbsoluteUrl('view', array(
								'id' => $friend->id
							)),
							'active' => true
						)
					);

					$this->sendResponse(200, CJSON::encode(array(
						'actions' => $actions,
						'friend'  => CMap::mergeArray($friend->asArray(), array(
							'actions' => array(
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
	 * Delete a friend
	 *
	 * @access public
	 * @return void
	 */
	public function actionDelete()
	{
		parse_str(file_get_contents('php://input'), $_DELETE);

		if (isset($_DELETE['Profile']['id']) && is_numeric($_DELETE['Profile']['id'])) {
			$authorizedUser = $this->getAuthorizedUser();

			$friend = Profile::model()->with('person')->findByPk((int) $_DELETE['Profile']['id'], array(
				'condition' => 't.created_by = :created_by AND t.id != :id',
				'params'    => array(
					':created_by' => $authorizedUser->id,
					':id'         => $authorizedUser->id
				)
			));

			if ($friend instanceof Profile) {
				if ($friend->delete()) {
					$actions = array(
						array(
							'label' => 'List friends',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('list')
						),
						array(
							'label' => 'New friend',
							'verb'  => 'GET',
							'url'   => $this->createAbsoluteUrl('new')
						)
					);

					$this->sendResponse(200, CJSON::encode(array(
						'actions' => $actions
					)));
				}
			} else {
				$this->sendResponse(404);
			}
		} else {
			$this->sendResponse(400);
		}

		if (isset($friend) && (count($friend->errors) > 0)) {
			$errors['friend'] = $friend->errors;
		}

		$this->sendResponse(500, CJSON::encode(array(
			'errors' => $errors
		)));
	}
}
?>
