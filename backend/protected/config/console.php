<?php
return array(
	'basePath'   => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name'       => 'Yii REST Sample Console',
	'timeZone'   => 'UTC',
	'charset'    => 'UTF-8',
	'import'     => array(
		'application.components.*',
		'packages.redis.*'
	),
	'components' => array(
		'redis'              => require 'redis.php',
		'cache'              => require 'cache.php',
		'db'                 => require 'mysql.php',
		'httpAuthentication' => require 'authentication.php'
	),
	'commandMap' => require 'commands.php'
);
