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
			$this->forward('user/index');
		} else {
			$this->forward('friend/index');
		}
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
