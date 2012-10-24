<?php
// This is the configuration for yiic console application.
// Any writable CConsoleApplication properties can be configured here.
return array(
	'basePath'   => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name'       => 'Yii REST Sample Console',
	'timeZone'   => 'UTC',
	'charset'    => 'UTF-8',
	'import'     => array(
		'application.components.*',
	),
	'components' => array(
		'db'                 => require 'mysql.php',
		'httpAuthentication' => require 'authentication.php',
	),
	'commandMap' => require 'commands.php'
);
