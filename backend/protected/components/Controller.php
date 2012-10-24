<?php
/**
 * Controller class file
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.components
 */
class Controller extends CController
{
	/**
	 * Override the render method because all the actions will use just one view
	 *
	 * @param array $data the data to be available in the view
	 * @return void
	 */
	public function render($data = null)
	{
		parent::render('/default/index', array(
			'data' => $data
		));
	}

	/**
	 * Send the response with the correct headers
	 *
	 * @param integer $status a valid HTTP status code
	 * @param string $body the response body
	 * @param string $contentType the response body content type
	 * @return void
	 */
	protected function sendResponse($status = 200, $body = '', $contentType = 'application/json')
	{
		header('HTTP/1.1 ' . $status . ' ' . $this->getStatusCodeMessage($status));
		header('Content-type: ' . $contentType);

		if ($body != '') {
			echo $body;
		}

		Yii::app()->end();
	}

	/**
	 * Get the message for a status code
	 *
	 * @param mixed $status a valid HTTP status code
	 * @return string the corresponding message
	 */
	protected function getStatusCodeMessage($status)
	{
		$codes = Array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
		);

		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	/**
	 * Get the user authorized in the application
	 *
	 * @return mixed the user model if found else null
	 */
	protected function getAuthorizedUser()
	{
		return User::model()->findByUsername(Yii::app()->httpAuthentication->username);
	}
}
