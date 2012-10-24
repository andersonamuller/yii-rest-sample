<?php
/**
 * DefaultController is the entry point for requests without a defined route.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.controllers
 */
class DefaultController extends Controller
{
	/**
	 * Default action when no route defined
	 * @return void
	 */
	public function actionIndex()
	{
		$user = User::model()->findByUsername(Yii::app()->httpAuthentication->username);
		if (($user instanceof User) && $user->isAdmin()) {
			$options = array(
				array(
					'label' => 'List users',
					'verb'  => 'GET',
					'url'   => $this->createAbsoluteUrl('user/list')
				),
				array(
					'label' => 'New user',
					'verb'  => 'GET',
					'url'   => $this->createAbsoluteUrl('user/new')
				)
			);
		} else {
			$options = array(
				array(
					'label' => 'List friends',
					'verb'  => 'GET',
					'url'   => $this->createAbsoluteUrl('friend/list')
				),
				array(
					'label' => 'New friend',
					'verb'  => 'GET',
					'url'   => $this->createAbsoluteUrl('friend/new')
				)
			);
		}

		$this->render(array(
			'options' => $options
		));
	}

	/**
	 * Default action to handle errors
	 * @return void
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error) {
			$this->render(array(
				'code'    => $error['code'],
				'message' => $error['message']
			));
		}
	}
}
?>
