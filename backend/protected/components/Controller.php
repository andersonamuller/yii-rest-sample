<?php
/**
 * Controller adds default functionalities to all controllers extending from this class.
 *
 * All controller classes for this application should extend from this base class.
 *
 * @author Anderson Müller <anderson.a.muller@gmail.com>
 * @version 0.1
 * @package application.components
 */
abstract class Controller extends CController
{
	/**
	 * @var array the available options to be used by the consumer
	 *
	 * By setting this property, child classes can specify the options,
	 * such as the following,
	 * <pre>
	 * array(
	 *     'list' => array(
	 *         'verb'  => 'GET',
	 *         'route' => 'list'
	 *     )
	 * );
	 * </pre>
	 * A good place to set it is in the init method of the controller
	 *
	 * When responding to a valid request, the service can send the appropriate options,
	 * related to a object or to the response itself, telling the consumer what can be done next.
	 * This helps the service to specify the workflow as the consumer navigate through it,
	 * rather than being described upfront. It also makes it easier to change and grow the application.
	 */
	protected $options = array();

	/**
	 * Returns the option
	 *
	 * @param string $name the option name
	 * @param array $properties extra properties to add to the option
	 * @param array $urlParams parameters used to build the option url
	 * @return array if the option exists then returns as an array (name => option) or an empty array if it does not exist
	 */
	public function getOption($name, $properties = array(), $urlParams = array())
	{
		if (isset($this->options[$name])) {
			$option = $this->options[$name];
			$option = array_merge($option, $properties);
			$option['url'] = $this->createAbsoluteUrl($option['route'], $urlParams);
			unset($option['route']);

			return array(
				$name => $option
			);
		}

		return array();
	}

	/**
	 * @see CController::init()
	 */
	public function init()
	{
		$this->options = array(
			'list'   => array(
				'verb'  => 'GET',
				'route' => 'list'
			),
			'view'   => array(
				'verb'  => 'GET',
				'route' => 'view'
			),
			'new'    => array(
				'verb'  => 'GET',
				'route' => 'new'
			),
			'create' => array(
				'verb'  => 'POST',
				'route' => 'create'
			),
			'update' => array(
				'verb'  => 'PUT',
				'route' => 'update'
			),
			'delete' => array(
				'verb'  => 'DELETE',
				'route' => 'delete'
			)
		);
	}

	/**
	 * Overrides the render method, because all the actions will use just one view
	 *
	 * @param array $data the data to be available in the view
	 * @return string the rendering result. Null if the rendering result is not required.
	 */
	public function render($data = null, $return = false)
	{
		$data = array(
			$this->id => $data
		);

		return parent::render('/default/index', array(
			'data' => $data
		), $return);
	}

	/**
	 * Sends the response with the correct headers
	 *
	 * @param integer $status a valid HTTP status code
	 * @param mixed $body the response body
	 * @param string $contentType the response body content type
	 * @return void
	 */
	public function sendResponse($status = 200, $body = null, $contentType = 'application/json')
	{
		header('HTTP/1.1 ' . $status . ' ' . $this->getStatusCodeMessage($status));
		header('Content-type: ' . $contentType);

		if (!empty($body)) {
			echo $body;
		}

		Yii::app()->end();
	}

	/**
	 * Returns the message for a status code
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
}
