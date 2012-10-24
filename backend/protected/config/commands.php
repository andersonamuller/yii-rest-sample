<?php
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

return array(
	'migrate'      => array(
		'class'          => 'system.cli.commands.MigrateCommand',
		'migrationPath'  => 'application.migrations',
		'migrationTable' => 'migration',
		'connectionID'   => 'db'
	),
	'auto-migrate' => array(
		'class'          => 'system.cli.commands.MigrateCommand',
		'migrationPath'  => 'application.migrations',
		'migrationTable' => 'migration',
		'connectionID'   => 'db',
		'interactive'    => false
	)
);
