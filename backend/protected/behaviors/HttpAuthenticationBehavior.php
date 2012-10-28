<?php
/**
 * HttpAuthenticationBehavior performs authorization checks using http authentication.
 *
 * By enabling this application behavior, access can be limited to only authorized users.
 * <pre>
 * 'behaviors' => array(
 *     'HttpAuthenticationBehavior'
 * ),
 * </pre>
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.behaviors
 */
class HttpAuthenticationBehavior extends CBehavior
{
	/**
	 * Declares events and the event handler methods
	 *
	 * @see CBehavior::events()
	 * @return array events (array keys) and the corresponding event handler methods (array values).
	 */
	public function events()
	{
		return CMap::mergeArray(parent::events(), array(
			'onBeginRequest' => 'beginRequest'
		));
	}

	/**
	 * Performs the authentication
	 */
	public function beginRequest()
	{
		/* @var $httpAuthentication HttpDigestAuthentication */
		$httpAuthentication = $this->owner->httpAuthentication;
		$httpAuthentication->users = array();

		$model = User::model();
		$users = $model->cache(3600, $model->getCacheDependency())->findAll(array(
			'condition' => 'authorization IS NOT NULL',
			'index'     => 'username'
		));

		foreach ($users as $user) {
			/* @var $user User */
			$httpAuthentication->users[$user->username] = $user->authorization;
		}

		$username = $httpAuthentication->authenticate();
		if ($username !== false) {
			Yii::app()->user = $users[$username];

			$path = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'];
			if (is_file($path)) {
				if (!is_readable($path)) {
					header('HTTP/1.0 403 Forbidden');
				} else {
					$stat = stat($path);
					$etag = sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);

					header('Expires: ');
					header('Cache-Control: ');
					header('Pragma: ');

					if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
						header('HTTP/1.0 304 Not Modified');
						header('Etag: "' . $etag . '"');
					} elseif (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']) {
						header('HTTP/1.0 304 Not Modified');
						header('Last-Modified: ' . date('r', $stat['mtime']));
					} else {
						header('HTTP/1.0 200 OK');
						header('Last-Modified: ' . date('r', $stat['mtime']));
						header('Etag: "' . $etag . '"');
						header('Accept-Ranges: bytes');
						header('Content-Length:' . $stat['size']);
						header('Content-type: ' . CFileHelper::getMimeTypeByExtension($path));
						readfile($path);
					}
				}

				Yii::app()->end();
			}
		}

		unset($users);
	}
}
