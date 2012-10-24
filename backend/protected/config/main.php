<?php
Yii::setPathOfAlias('packages', dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'packages');

return array(
	'basePath'          => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
	'name'              => 'Yii REST Sample',
	'timeZone'          => 'UTC',
	'charset'           => 'UTF-8',
	'defaultController' => 'default',
	'behaviors'         => array(
		'HttpAuthenticationBehavior'
	),
	'preload'           => array(
		'log'
	),
	'import'            => array(
		'application.behaviors.*',
		'application.components.*',
		'application.filters.*',
		'application.models.*',
		'packages.redis.*'
	),
	'components'        => array(
		'errorHandler'       => array(
			'errorAction' => 'default/error'
		),
		'log'                => array(
			'class'  => 'CLogRouter',
			'routes' => array(
				array(
					'class'  => 'CFileLogRoute',
					'levels' => 'error, warning, info'
				)
			)
		),
		'cache'              => array(
			'class' => 'packages.redis.ARedisCache'
		),
		'redis'              => require 'redis.php',
		'db'                 => require 'mysql.php',
		'httpAuthentication' => require 'authentication.php',
		'urlManager'         => array(
			'urlFormat'      => 'path',
			'showScriptName' => false,
			'rules'          => array(
				array(
					'<controller>/list',
					'pattern' => '<controller:\w+>/list',
					'verb'    => 'GET'
				),
				array(
					'<controller>/view',
					'pattern' => '<controller:\w+>/view/<id:\d+>',
					'verb'    => 'GET'
				),
				array(
					'<controller>/new',
					'pattern' => '<controller:\w+>/new',
					'verb'    => 'GET'
				),
				array(
					'<controller>/create',
					'pattern' => '<controller:\w+>/create',
					'verb'    => 'POST'
				),
				array(
					'<controller>/update',
					'pattern' => '<controller:\w+>/update',
					'verb'    => 'PUT'
				),
				array(
					'<controller>/delete',
					'pattern' => '<controller:\w+>/delete',
					'verb'    => 'DELETE'
				)
			)
		)
	)
);
