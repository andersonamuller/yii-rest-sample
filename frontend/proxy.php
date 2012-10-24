<?php
session_start();

require 'protected/RestRequest.php';
require 'protected/User.php';

if (isset($_POST['logout'])) {
	$user = new User();
	$user->logout();
} else {
	if (isset($_POST['server']) && isset($_POST['username']) && isset($_POST['password'])) {
		$restRequest = new RestRequest($_POST['server']);
		$restRequest->setUsername($_POST['username']);
		$restRequest->setPassword($_POST['password']);
		$restRequest->execute();
		if ($restRequest->getStatusCode() == 200) {
			$user = new User();
			$user->setUsername($_POST['username']);
			$user->setPassword($_POST['password']);
			$user->login();

			$_SESSION['server'] = $_POST['server'];
		}
		$restRequest->setResponseHeader();
		exit();
	}

	$user = new User();
	if ($user->isAuthenticated()) {
		$url = '';
		if (isset($_SERVER['HTTP_FORWARD'])) {
			$url = $_SERVER['HTTP_FORWARD'];
		} elseif (isset($_SESSION['server'])) {
			$url = $_SESSION['server'];
		}

		$requestBody = null;
		switch ($_SERVER['REQUEST_METHOD']) {
			case 'POST':
				$requestBody = $_POST;

				break;
			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents('php://input'), $requestBody);
				break;
		}

		$restRequest = new RestRequest($url, $_SERVER['REQUEST_METHOD'], $requestBody);
		$restRequest->setUsername($user->getUsername());
		$restRequest->setPassword($user->getPassword());
		$restRequest->execute();

		$restRequest->setResponseHeader();
		echo $restRequest->getResponseBody();
	} else {
		header('HTTP/1.1 401 Unauthorized');
	}
}
