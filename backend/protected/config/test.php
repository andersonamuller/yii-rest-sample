<?php
return CMap::mergeArray(require(dirname(__FILE__) . '/main.php'), array(
	'components' => array(
		'fixture' => array(
			'class' => 'system.test.CDbFixtureManager',
		),
		'db'      => CMap::mergeArray(require 'mysql.php', array(
			'connectionString' => 'mysql:host=localhost;dbname=yii-rest-sample_test'
		))
	),
));
