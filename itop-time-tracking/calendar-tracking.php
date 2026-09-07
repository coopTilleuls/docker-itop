<?php
// Copyright (C) 2019 Combodo SARL
if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));

// Load current environment
if (file_exists(__DIR__.'/../../approot.inc.php'))
{
	require_once __DIR__.'/../../approot.inc.php';   // When in env-xxxx folder
}
else
{
	require_once __DIR__.'/../../../approot.inc.php';   // When in datamodels/x.x or data/production-modules folder
}


require_once(APPROOT.'application/application.inc.php');
require_once(APPROOT.'application/startup.inc.php');

require_once(APPROOT.'application/itopwebpage.class.inc.php');
require_once(APPROOT.'/application/loginwebpage.class.inc.php');

require_once('class/timetrackingview.class.inc.php');



LoginWebPage::DoLogin(false /* bMustBeAdmin */, false /* IsAllowedToPortalUsers */); // Check user rights and prompt if needed

$oPage = new iTopWebPage(Dict::S('TimeTracking:TitlePage'));
$sTT = new TimeTrackingView('User', true);
$sTT->Display($oPage, 'timetracking_view' , array('user_id' => UserRights::GetUserId()));
$oPage->output();
