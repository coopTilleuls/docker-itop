<?php

$MySettings = array(
	'db_host'    => getenv('DB_HOSTNAME'),
	'db_name'    => getenv('DB_ENV_MYSQL_DATABASE'),
	'db_user'    => getenv('DB_ENV_MYSQL_USER'),
	'db_pwd'     => getenv('DB_ENV_MYSQL_PASSWORD'),
	'db_subname' => getenv('DB_PREFIX'),
);

$MyModuleSettings = array();
$MyModules = array('addons' => array('user rights' => 'addons/userrights/userrightsprofile.class.inc.php'));
