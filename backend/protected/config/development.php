<?php
return CMap::mergeArray(require(dirname(__FILE__) . '/main.php'), array(
	'components' => array(
		'log'        => array(
			'routes' => require 'debug.php'
		),
		'urlManager' => array(
			'rules' => array(
				'<module:(gii|webshell)>'                               => '<module>',
				'<module:(gii|webshell)>/<controller:\w+>'              => '<module>/<controller>',
				'<module:(gii|webshell)>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>'
			)
		)
	),
	'modules'    => array(
		'gii'      => array(
			'class'     => 'system.gii.GiiModule',
			'password'  => 'admin',
			'ipFilters' => array(
				'127.0.0.1',
				'::1'
			),
		),
		'webshell' => array(
			'class'          => 'application.modules.webshell.WebShellModule',
			'yiicCommandMap' => require 'commands.php'
		)
	)
));
