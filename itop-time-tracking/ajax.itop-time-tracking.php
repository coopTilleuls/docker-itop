<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

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

require_once APPROOT.'/application/application.inc.php';
require_once APPROOT.'/application/ajaxwebpage.class.inc.php';
require_once APPROOT.'/application/startup.inc.php';
require_once APPROOT.'/application/loginwebpage.class.inc.php';
require_once  'utils.itop-time-tracking.php';
// iTop 2.7.0+
if (file_exists(APPROOT.'/bootstrap.inc.php'))
{
	require_once(APPROOT.'/bootstrap.inc.php');
}

LoginWebPage::DoLogin(false /* bMustBeAdmin */, false /* IsAllowedToPortalUsers */); // Check user rights and prompt if needed
$sOperation = utils::ReadParam('operation', '');

if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
	$oPage = new ajax_page("");
	$oPage->no_cache();
} else {
	$oPage = new AjaxPage('');
}
switch($sOperation) {
	case 'modify_event_form':
	case 'create_event_form':
		// Keep default ContentType
		break;
	default:
		$oPage->SetContentType('application/json');
		break;
}


switch($sOperation)
{
	case 'create':
		createTimeSpent($oPage);
		break;
	case 'delete':
		deleteTimeSpent($oPage);
		break;
	case 'clone':
		cloneTimeSpent($oPage);
		break;
	case 'update_event':
		updateTimeSpent($oPage);
		break;
	case 'modify_event_form':
		modifyEventForm($oPage);
		break;
	case 'create_event_form':
		createEventForm($oPage);
		break;
	case 'get_events_by_user':
		getTimeSpentByUser($oPage);
		break;
	case 'get_events_by_activity':
		getTimeSpentByActivity($oPage);
		break;
	case 'get_activities':
		getActivities($oPage);
		break;
	case 'create_activity':
		createActivityForm($oPage);
		break;
	case 'new_activity':
		newActivity($oPage);
		break;
	case 'favourite_activity':
		favouriteActivity($oPage);
		break;
	case 'user_color_activity':
		userColorActivity($oPage);
		break;
	// stopwatch
	case 'start_stopwatch':
		startStopwatch($oPage);
		break;
	case 'stop_stopwatch':
		stopStopwatch($oPage);
		break;
	case 'reset_stopwatch':
		resetStopwatch($oPage);
		break;
	case 'get_history_stopwatch':
		getHistoryStopwatch($oPage);
		break;

}
$oPage->output();

function createTimeSpent(\WebPage $oP)
{
	$aNewEvent['status'] = 'failed';
	$iActivity = utils::ReadParam('activity_id', -1, true,'integer');
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', -1, true,'parameter');
	$iActivity = Activity::getOrCreateActivity($iActivity, $sObjClass, $iObjId);
	if ($iActivity > 0)
	{
		
		$oActivity = MetaModel::GetObject('Activity', $iActivity);
		$oNewTimeSpent = new TimeSpent();
		
		$oAppContext = new ApplicationContext();
		$aPrefillFormParam = array( 'user' => $_SESSION["auth_user"],
			'context' => $oAppContext->GetAsHash(),
			'default' => utils::ReadParam('default', array(), '', 'raw_data'),
			'origin' => 'console',
			'time-tracking-step' => 'create-timespent', 
			'start-timestamp' => utils::ReadParam('start', 0, true,'integer'),
			'end-timestamp' => utils::ReadParam('end', 0, true,'integer'),

		);
		$oNewTimeSpent->PrefillForm('creation_from_0',$aPrefillFormParam);

		$oNewTimeSpent->Set('activity_id', $iActivity);
		$oNewTimeSpent->Set('origin',  utils::ReadParam('origin', 'calendar', true,'parameter'));

		try
		{
			$oNewTimeSpent->DBInsert();
			$aColor = $oNewTimeSpent->GetColor();
			$oStartDate = new DateTime($oNewTimeSpent->Get('start_date'));
			$oEndDate = new DateTime($oNewTimeSpent->Get('end_date'));
			$aNewEvent['new_event']= array(
				'color' => $aColor[0],
				'textColor' => $aColor[1],
				'title' => $oNewTimeSpent->Get('title'),
				'start' => $oStartDate->format('U')*1000,
				'end' => $oEndDate->format('U')*1000,
				'editable' => true,
				'allDay' => false,
				'timespent_id' => $oNewTimeSpent->GetKey(),
				'contact' => $oNewTimeSpent->Get('contact_id_friendlyname'),
				'description' => $oNewTimeSpent->Get('description'),
				'duration' => AttributeDuration::FormatDuration($oNewTimeSpent->Get('duration')),
			);
			$aNewEvent['status'] = 'ok';
		}
		catch(CoreCannotSaveObjectException $e)
		{
			$aNewEvent['message'] = $e->getIssues();
		}
		
	}
	else
	{
		$aNewEvent['message'] = Dict::S('TimeTracking:Error:WrongActivity');
	}
	$oP->add(json_encode($aNewEvent));
}

function deleteTimeSpent(\WebPage $oP)
{
	$sTimeSpentId = utils::ReadParam('id', '', true,'parameter');

	$aStatus = array('status' => 'failed');
	$sOQL = 'SELECT TimeSpent WHERE id = :id';
	$aQueryParams = array('id' => $sTimeSpentId);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	$oTimeSpent = $oSet->Fetch();
	if ($oTimeSpent != null)
	{
		try{
			$oTimeSpent->DBDelete();
			$aStatus['status'] = 'ok';
		}
		catch(DeleteException $e)
		{
			$aStatus['message'] = Dict::S('TimeTracking:Error:DeleteExpired');
		}
		catch (ArchivedObjectException $e)
		{
			$aStatus['message'] = Dict::S('TimeTracking:Error:DeleteArchived');
		}
	}
	$oP->add(json_encode($aStatus));
}

function cloneTimeSpent(\WebPage $oP)
{
	$aNewEvent['status'] = 'failed';

	$sTimeSpentId = utils::ReadParam('id', '', true,'parameter');
	$sOQL = 'SELECT TimeSpent WHERE id = :id';
	$aQueryParams = array('id' => $sTimeSpentId);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	$oTimeSpent = $oSet->Fetch();
	if ($oTimeSpent)
	{
		$oNewTimeSpent = clone $oTimeSpent;
		$oNewTimeSpent->Set('origin',  utils::ReadParam('origin', 'calendar', true,'parameter'));

		try
		{
			$oNewTimeSpent->DBInsert();
			$aColor = $oNewTimeSpent->GetColor();
			$oStartDate = new DateTime($oNewTimeSpent->Get('start_date'));
			$oEndDate = new DateTime($oNewTimeSpent->Get('end_date'));

			$aNewEvent['new_event']= array(
				'color' => $aColor[0],
				'textColor' => $aColor[1],
				'title' => $oNewTimeSpent->Get('title'),
				'start' => $oStartDate->format('U')*1000,
				'end' => $oEndDate->format('U')*1000,
				'editable' => true,
				'allDay' => false,
				'timespent_id' => $oNewTimeSpent->GetKey(),
				'contact' => $oNewTimeSpent->Get('contact_id_friendlyname'),
				'description' => $oNewTimeSpent->Get('description'),
				'duration' => AttributeDuration::FormatDuration($oNewTimeSpent->Get('duration')),
			);
			$aNewEvent['status'] = 'ok';
		}
		catch(CoreCannotSaveObjectException $e)
		{
			$aNewEvent['message'] = $e->getIssues();
		}
	}
	$oP->add(json_encode($aNewEvent));
}

function updateTimeSpent(\WebPage $oP)
{
	$sMinimumEventDuration = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'minimum_event_duration_display', '00:00:00');
	$sTimeSpentId = utils::ReadParam('id', '', true,'parameter');

	$aStatus = array("status" => 'failed');
	$sOQL = 'SELECT TimeSpent WHERE id = :id';
	$aQueryParams = array('id' => $sTimeSpentId);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	$oTimeSpent = $oSet->Fetch();
	if ($oTimeSpent != null)
	{
		$oStartDate = new DateTime();
		$oStartDate->setTimestamp(utils::ReadParam('start', 0, true,'integer'));
		$oTimeSpent->Set('start_date',  $oStartDate->format('Y-m-d H:i:s'));
		$oEndDate = new DateTime();
		$oEndDate->setTimestamp(utils::ReadParam('end', 0, true,'integer'));
		$oTimeSpent->Set('end_date',  $oEndDate->format('Y-m-d H:i:s'));
		$oTimeSpent->Set('description',  utils::ReadParam('description', '', true,'raw_data'));
		$oTimeSpent->Set('title',  utils::ReadParam('title', '', true,'raw_data'));
		try
		{
			$oTimeSpent->DBUpdate();
			$aColor = $oTimeSpent->GetColor();
			$oStartDate = new DateTime($oTimeSpent->Get('start_date'));
			$oEndDate = new DateTime($oTimeSpent->Get('end_date'));

			if($oStartDate->format('Y-m-d') === $oEndDate->format('Y-m-d') && $oStartDate->diff($oEndDate)->format('%H:%I:%S') < $sMinimumEventDuration)
			{
				$oEndDate = clone $oStartDate;
				$aMinimumEventDuration = explode(':', $sMinimumEventDuration);
				$oEndDate->add(new DateInterval('PT'.$aMinimumEventDuration[0].'H'))->add(new DateInterval('PT'.$aMinimumEventDuration[1].'M'))->add(new DateInterval('PT'.$aMinimumEventDuration[2].'S'));
			}
			
			$aStatus['event'] = array(
				'color' => $aColor[0],
				'textColor' => $aColor[1],
				'title' => $oTimeSpent->Get('title'),
				'start' => $oStartDate->format('U')*1000,
				'end' => $oEndDate->format('U')*1000,
				'editable' => true,
				'allDay' => false,
				'timespent_id' => $oTimeSpent->GetKey(),
				'contact' => $oTimeSpent->Get('contact_id_friendlyname'),
				'description' => $oTimeSpent->Get('description'),
				'duration' => AttributeDuration::FormatDuration($oTimeSpent->Get('duration')),
			);
			$aStatus['status'] = 'ok';
		}
		catch(CoreCannotSaveObjectException $e)
		{
			$aStatus['message'] = $e->getIssues();
		}
	}
	$oP->add(json_encode($aStatus));

}
function createEventForm(\WebPage $oP)
{
	$iActivity = utils::ReadParam('activity_id', -1, true,'integer');
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', -1, true,'parameter');
	$iActivity = Activity::getOrCreateActivity($iActivity, $sObjClass, $iObjId);
	if ($iActivity > 0)
	{
		$oActivity = MetaModel::GetObject('Activity', $iActivity);
		$oNewTimeSpent = new TimeSpent();

		$oAppContext = new ApplicationContext();
		$aPrefillFormParam = array( 'user' => $_SESSION["auth_user"],
			'context' => $oAppContext->GetAsHash(),
			'default' => utils::ReadParam('default', array(), '', 'raw_data'),
			'origin' => 'console',
		    'time-tracking-step' => 'create-form',
			'start-timestamp' => utils::ReadParam('start', 0, true,'integer'),
			'end-timestamp' => utils::ReadParam('end', 0, true,'integer'),
		);
		$oNewTimeSpent->PrefillForm('creation_from_0',$aPrefillFormParam);

		$oNewTimeSpent->Set('activity_id', $iActivity);
		$oNewTimeSpent->Set('origin',  utils::ReadParam('origin', 'calendar', true,'parameter'));
		$oNewTimeSpent->DisplayModifyForm($oP, array('wizard_container' => true));
	}
	else
	{
		$oP->p(Dict::S('TimeTracking:Error:WrongActivity'));
	}
}
function modifyEventForm(\WebPage $oP)
{
	$sTimeSpentId = utils::ReadParam('event_id', '', true,'parameter');
	$sOQL = 'SELECT TimeSpent WHERE id = :id';
	$aQueryParams = array('id' => $sTimeSpentId);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	$oTimeSpent = $oSet->Fetch();
	if ($oTimeSpent != null)
	{
		$oTimeSpent->DisplayModifyForm($oP, array('wizard_container' => true));
	}
}
function getTimeSpentByActivity(\WebPage $oP)
{
	$aEvents = array();
	$aEvents['status'] = 'failed';
	
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', '', true,'parameter');
	$iUserId = UserRights::GetUserId();
	$oStartDate = new DateTime();
	$oStartDate->setTimestamp(utils::ReadParam('start', 0, true,'integer'));
	$oEndDate = new DateTime();
	$oEndDate->setTimestamp(utils::ReadParam('end', 0, true,'integer'));
	$sMinimumEventDuration = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'minimum_event_duration_display', '00:00:00');

	if(!empty($sObjClass) && !empty($iObjId))
	{
		$sOQL = 'SELECT Activity WHERE obj_class = :obj_class AND obj_id = :obj_id';
		$aQueryParams = array('obj_class' => $sObjClass, 'obj_id' => $iObjId);
		$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
		$aEvents['status'] = 'ok';
		$oActivity = $oSet->Fetch();
		if ($oActivity)
		{
			$sOQL = 'SELECT TimeSpent WHERE activity_id = :activity_id AND user_id = :user_id AND((start_date >= :start_date AND start_date <= :end_date) OR (end_date >= :start_date AND end_date <= :end_date) OR (start_date < :start_date AND end_date > :end_date))';
			$aQueryParams = array('activity_id' => $oActivity->GetKey(), 'user_id' => $iUserId, 'start_date' => $oStartDate->format('Y-m-d H:i:s'), 'end_date'=> $oEndDate->format('Y-m-d H:i:s'));
			$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
			while ($oTimeSpent = $oSet->Fetch())
			{
				$bEditable = true;
				if($oTimeSpent instanceof TimeSpentBackground)
				{
					$bEditable = !($oTimeSpent->Get('status') === 'ongoing');
				}
				$aColor = $oTimeSpent->GetColor();
				$oStartDate = new DateTime($oTimeSpent->Get('start_date'));
				$oEndDate = new DateTime($oTimeSpent->Get('end_date'));

				if($oStartDate->format('Y-m-d') === $oEndDate->format('Y-m-d') && $oStartDate->diff($oEndDate)->format('%H:%I:%S') < $sMinimumEventDuration)
				{
					$oEndDate = clone $oStartDate;
					$aMinimumEventDuration = explode(':', $sMinimumEventDuration);
					$oEndDate->add(new DateInterval('PT'.$aMinimumEventDuration[0].'H'))->add(new DateInterval('PT'.$aMinimumEventDuration[1].'M'))->add(new DateInterval('PT'.$aMinimumEventDuration[2].'S'));
				}
				
				$aEvent = array(
					'color' => $aColor[0],
					'textColor' => $aColor[1],
					'title' => $oTimeSpent->Get('title'),
					'start' => $oStartDate->format('U') * 1000,
					'end' => $oEndDate->format('U') * 1000,
					'description' => $oTimeSpent->Get('description'),
					'editable' => $bEditable,
					'allDay' => false,
					'timespent_id' => $oTimeSpent->GetKey(),
					'contact' => $oTimeSpent->Get('contact_id_friendlyname'),
					'description' => $oTimeSpent->Get('description'),
					'duration' => AttributeDuration::FormatDuration($oTimeSpent->Get('duration')),
				);
				$aEvents['event'][] = $aEvent;
			}
		}
	}
	$oP->add(json_encode($aEvents));
}
function getTimeSpentByUser(\WebPage $oP)
{
	$aEvents = array();
	$aEvents['status'] = 'failed';
	
	$iUserId = utils::ReadParam('user_id', -1, true,'integer');
	$oStartDate = new DateTime();
	$oStartDate->setTimestamp(utils::ReadParam('start', 0, true,'integer'));
	$oEndDate = new DateTime();
	$oEndDate->setTimestamp(utils::ReadParam('end', 0, true,'integer'));
	$sMinimumEventDuration = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'minimum_event_duration_display', '00:00:00');
	
	if($iUserId > -1)
	{
		$sOQL = 'SELECT TimeSpent WHERE user_id = :user_id AND ((start_date >= :start_date AND start_date <= :end_date) OR (end_date >= :start_date AND end_date <= :end_date) OR (start_date < :start_date AND end_date > :end_date))';
		$aQueryParams = array('user_id' =>$iUserId, 'start_date' => $oStartDate->format('Y-m-d H:i:s'), 'end_date'=> $oEndDate->format('Y-m-d H:i:s'),);
		$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
		$aEvents['status'] = 'ok';
		while($oTimeSpent = $oSet->Fetch())
		{
			$bEditable = true;
			if($oTimeSpent instanceof TimeSpentBackground)
			{
				$bEditable = !($oTimeSpent->Get('status') === 'ongoing');
			}
			$aColor = $oTimeSpent->GetColor();
			$oStartDate = new DateTime($oTimeSpent->Get('start_date'));
			$oEndDate = new DateTime($oTimeSpent->Get('end_date'));
			
			if($oStartDate->format('Y-m-d') === $oEndDate->format('Y-m-d') && $oStartDate->diff($oEndDate)->format('%H:%I:%S') < $sMinimumEventDuration)
			{
				$oEndDate = clone $oStartDate;
				$aMinimumEventDuration = explode(':', $sMinimumEventDuration);
				$oEndDate->add(new DateInterval('PT'.$aMinimumEventDuration[0].'H'))->add(new DateInterval('PT'.$aMinimumEventDuration[1].'M'))->add(new DateInterval('PT'.$aMinimumEventDuration[2].'S'));
			}
			$aEvent = array(
				'color' => $aColor[0],
				'textColor' => $aColor[1],
				'title' => $oTimeSpent->Get('title'),
				'start' => $oStartDate->format('U')*1000,
				'end' => $oEndDate->format('U')*1000,
				'description' => $oTimeSpent->Get('description'),
				'editable' => $bEditable,
				'allDay' => false,
				'timespent_id' => $oTimeSpent->GetKey(),
				'contact' => $oTimeSpent->Get('contact_id_friendlyname'),
				'description' => $oTimeSpent->Get('description'),
				'duration' => AttributeDuration::FormatDuration($oTimeSpent->Get('duration')),
			);
			$aEvents['event'][] = $aEvent;
		}
	}
	$oP->add(json_encode($aEvents));
}
function getActivities(\WebPage $oP)
{
	$aPossibleActivities = array();
	$aAllowedClasses = MetaModel::GetModuleSetting(TimeTrackingView::MODULE_CODE, 'allowed_classes', array());
	$aClassColor = MetaModel::GetModuleSetting(TimeTrackingView::MODULE_CODE, 'colors', array());

	//Get existing Activities
	$aActivities = array();
	$sOQL = 'SELECT Activity';
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array());
	while($oActivity = $oSet->Fetch())
	{
		$aActivities[$oActivity->GetKey()] = array($oActivity->Get('obj_class'), $oActivity->Get('obj_id'));
	}
	
	//Get FavouriteActivities
	$sOQL = 'SELECT FavouriteActivity WHERE user_id=:user_id';
	$aQueryParams = array('user_id' =>UserRights::GetUserId());
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	$aFavActivities = array();
	while($oFavActivity = $oSet->Fetch())
	{
		$aFavActivities[] = array($oFavActivity->Get('activity_id'),$oFavActivity->Get('user_id'));
	}
	
	$sFavoriteString = Dict::S('TimeTracking:Category:Favorite');
	$aTargetObjects = Activity::GetTargetObjects('calendar-page');
	foreach($aTargetObjects as $sClass => $oSet)
	{
		$aClassPossibleActivities = array();
		foreach($oSet as $oAllowedObj)
		{
			$sObjClass = get_class($oAllowedObj);
			$iObjKey = $oAllowedObj->GetKey();
			$bIsFav = false;
				
			$sColor = '';
			if(in_array(array($sObjClass, $iObjKey), $aActivities, false))
			{
				$oActivity = MetaModel::GetObject('Activity', array_search(array($sObjClass, $iObjKey), $aActivities), false);
				if($oActivity)
				{
					$sOQL = 'SELECT UserColorActivity WHERE activity_id = :activity_id AND user_id = :user_id';
					$aQueryParams = array('activity_id' => $oActivity->GetKey(), 'user_id' => UserRights::GetUserId());
					$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
					$oUserColorActivity = $oSet->Fetch();
					if ($oUserColorActivity != null)
					{
						$sColor = $oUserColorActivity->Get('background_color');
					}
					else
					{
						$sColor = $oActivity->Get('background_color');
					}
					
					if(in_array(array($oActivity->GetKey(), UserRights::GetUserId()), $aFavActivities, false))
					{
						$bIsFav = true;
					}
				}
			}
			else
			{
				if (array_key_exists('classes', $aClassColor) && array_key_exists($sClass, $aClassColor['classes']))
				{
					$sColor = $aClassColor['classes'][$sClass]['background'];
				}
				$sColor = ActivityColors::GetColor($sColor, 'background');
			}
			$aPossibleActivity = array(
				'object_class' => $sObjClass,
				'object_id' => $iObjKey,
				'friendlyname' => $oAllowedObj->Get('friendlyname'),
				'color' => $sColor,
				'scope_class' => $sClass
			);
			if($bIsFav)
			{
				$aPossibleActivity['favorite'] = "true";
				$aPossibleActivities[$sFavoriteString][] = $aPossibleActivity;
			}
			else
			{
				$aClassPossibleActivities[] = $aPossibleActivity;
			}
		}
		if(!empty($aClassPossibleActivities))
		{
			usort($aClassPossibleActivities,'\TimeTrackingUtils\sortPossibleActivitiesByFriendlyname');
			$sClassName = $sClass;
			try
			{
				$sClassName = MetaModel::GetName($sClass);
			}
			catch (Exception $e)
			{
			}
			$aPossibleActivities[$sClassName] = $aClassPossibleActivities;
		}
	}
	if(count($aPossibleActivities) > 1)
	{
		if(!array_key_exists($sFavoriteString, $aPossibleActivities))
		{
			$aPossibleActivities[$sFavoriteString] = array();
		}
		else
		{
			usort($aPossibleActivities[$sFavoriteString],'\TimeTrackingUtils\sortPossibleActivitiesByFriendlyname');
		}
		
		uksort($aPossibleActivities,'\TimeTrackingUtils\sortPossibleActivitiesClasses');
	}
	$oP->add(json_encode(array('status' => 'ok', 'possible_activities' => $aPossibleActivities)));
}

function createActivityForm(\WebPage $oP)
{
	$aAllowedClasses = MetaModel::GetModuleSetting(TimeTrackingView::MODULE_CODE, 'allowed_classes', array());
	$aActivities = array();
	$sOQL = 'SELECT Activity';
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array());
	while($oActivity = $oSet->Fetch())
	{
		$aActivities[] = array($oActivity->Get('obj_class'), $oActivity->Get('obj_id'));
	}
	$aTargetObjects = Activity::GetTargetObjects('calendar-page');
	$aPossibleActivities = array();
	foreach($aTargetObjects as $sClass => $oSet)
	{
		$aParentClasses = MetaModel::EnumParentClasses($sClass, ENUM_PARENT_CLASSES_ALL, false);
		foreach($oSet as $oAllowedObj)
		{
			if(in_array(array($sClass, $oAllowedObj->GetKey()), $aActivities, false))
			{
				continue;
			}
			else
			{
				foreach($aParentClasses as $sParentClass)
				{
					if(in_array(array($aParentClasses, $oAllowedObj->GetKey()), $aActivities, false))
					{
						continue;
					}
				}
			}
			$aPossibleActivities[] = array('object_class' => $sClass, 'object_id' => $oAllowedObj->GetKey(), 'friendlyname' => $oAllowedObj->Get('friendlyname'));
		}
	}
	if(count($aPossibleActivities) > 1)
	{
		usort($aPossibleActivities,'\TimeTrackingUtils\sortPossibleActivitiesByClass');
	}
	$oP->add(json_encode(array('status' => 'ok', 'possible_activities' => $aPossibleActivities)));
}
function NewActivity(\WebPage $oP)
{
	$aNewActivity = array();
	$aNewActivity['status'] = 'failed';
	$sClass = utils::ReadParam('object_class', '', true,'parameter');
	$iId = utils::ReadParam('object_id', -1, true,'integer');
	$sBGColor = utils::ReadParam('background_color', '', true,'raw');
	$sTextColor = utils::ReadParam('text_color', '', true,'raw');

	$bExists = false;
	$sOQL = 'SELECT Activity';
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), array());
	while($oActivity = $oSet->Fetch())
	{
		if($oActivity->Get('obj_class') === $sClass && $oActivity->Get('obj_id') ===  $iId)
		{
			$bExists = true;
			break;
		}
	}

	if(!$bExists && MetaModel::GetObject($sClass,$iId))
	{
		$oNewActivity = new Activity;
		$oNewActivity->Set('obj_class', $sClass);
		$oNewActivity->Set('obj_id', $iId);
		$oNewActivity->Set('background_color', $sBGColor);
		$oNewActivity->Set('text_color', $sTextColor);
		$oNewActivity->DBInsert();
		$aNewActivity['status'] = 'ok';
		$aNewActivity['new_activity'] = array('id' => $oNewActivity->GetKey(), 'friendlyname' => $oNewActivity->getFriendlyName(), 'category' => Dict::S('TimeTracking:Category:All'), 'favourite' => false, 'color' => $sBGColor);
	}
	$oP->add(json_encode($aNewActivity));
}
function favouriteActivity(\WebPage $oP)
{
	$aReturn = array();
	$aReturn['status'] = 'failed';
	$iUserId = UserRights::GetUserId();
	$iActivityId = utils::ReadParam('activity_id', -1, true,'integer');
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', '', true,'integer');
	$sScopeClass = utils::ReadParam('object_scope', '', true,'parameter');


	$bValue =  utils::ReadParam('value', "false", true,'parameter');

	$iActivityId = Activity::getOrCreateActivity($iActivityId, $sObjClass, $iObjId);
	if ($iActivityId > 0)
	{
		if ($bValue === "true")
		{
			$oNewFavAct = new FavouriteActivity();
			$oNewFavAct->Set('user_id', $iUserId);
			$oNewFavAct->Set('activity_id', $iActivityId);
			$oNewFavAct->DBInsert();
			$aReturn['new_category'] = Dict::S('TimeTracking:Category:Favorite');
		}
		else
		{
			$sOQL = 'SELECT FavouriteActivity WHERE user_id=:user_id AND activity_id=:activity_id';
			$aQueryParams = array('user_id' => $iUserId, 'activity_id' => $iActivityId);
			$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
			while($oFavActivity = $oSet->Fetch())
			{
				$oFavActivity->DBDelete();
			}
			$aReturn['new_category'] = $sScopeClass;//Dict::S('TimeTracking:Category:All');
		}

		$aReturn['status'] = 'ok';
		$aReturn['new_value'] = $bValue;
	}
	else
	{
		$aReturn['message'] = Dict::S('TimeTracking:Error:WrongActivity');
	}

	$oP->add(json_encode($aReturn));
}
function userColorActivity(\WebPage $oP)
{
	$aReturn = array();
	$aReturn['status'] = 'failed';

	$iUserId = UserRights::GetUserId();
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', '', true,'integer');
	$sNewBackgroundColor = utils::ReadParam('background_color', '', true,'raw');
	$sNewTextColor = utils::ReadParam('text_color', '', true,'raw');
	
	$iActivityId = Activity::getOrCreateActivity(0, $sObjClass, $iObjId);

	if ($iActivityId > 0)
	{
		$sOQL = 'SELECT UserColorActivity WHERE activity_id = :activity_id AND user_id = :user_id';
		$aQueryParams = array('activity_id' => $iActivityId, 'user_id' => $iUserId);
		$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
		$oUserColorActivity = $oSet->Fetch();
		if ($oUserColorActivity != null)
		{
			$oUserColorActivity->Set('background_color', $sNewBackgroundColor);
			$oUserColorActivity->Set('text_color', $sNewTextColor);
			$oUserColorActivity->DBUpdate();
		}
		else
		{
			$oNewUserColorActivity = MetaModel::NewObject('UserColorActivity');
			$oNewUserColorActivity->Set('user_id', $iUserId);
			$oNewUserColorActivity->Set('activity_id', $iActivityId);
			$oNewUserColorActivity->Set('background_color', $sNewBackgroundColor);
			$oNewUserColorActivity->Set('text_color', $sNewTextColor);
			$oNewUserColorActivity->DBInsert();
		}	
		$aReturn['status'] = 'ok';
	}
	else
	{
		$aReturn['message'] = Dict::S('TimeTracking:Error:WrongActivity');
	}

	$oP->add(json_encode($aReturn));
}
function startStopwatch(\WebPage $oP)
{
	$aReturn['status'] = 'failed';
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', -1, true,'parameter');
	$oActivity = null;
	$iActivity = Activity::getOrCreateActivity(-1, $sObjClass, $iObjId);

	if ($iActivity > 0)
	{
		$aReturn['status'] = 'ok';

		$oActivity = MetaModel::GetObject('Activity', $iActivity);
		$oNewTimeSpent = new TimeSpentBackground();

		$oAppContext = new ApplicationContext();
		$aPrefillFormParam = array( 'user' => $_SESSION["auth_user"],
			'context' => $oAppContext->GetAsHash(),
			'default' => utils::ReadParam('default', array(), '', 'raw_data'),
			'origin' => 'console',
			'time-tracking-step' => 'start-stopwatch'
		);
		$oNewTimeSpent->PrefillForm('creation_from_0',$aPrefillFormParam);

		$oNewTimeSpent->Set('activity_id', $iActivity);
		$oNewTimeSpent->Set('origin',  'stopwatch');
		$oNewTimeSpent->Set('title',  $oActivity->GetFriendlyName());
		$oNewTimeSpent->Set('org_id', $oNewTimeSpent->GetOrganization());

		$oNewTimeSpent->DBInsert();

		$oStartDate = new DateTime($oNewTimeSpent->Get('start_date'));
		$aReturn['new_timspent_background']= array(
			'title' => $oNewTimeSpent->Get('title'),
			'start' => $oStartDate->format('Y-m-d H:i:s'),
			'editable' => true,
			'allDay' => false,
			'timespent_id' => $oNewTimeSpent->GetKey());
	}
	$oP->add(json_encode($aReturn));
}

function stopStopwatch(\WebPage $oP)
{
	$iTimeSpent = utils::ReadParam('timespent_id', '', true,'integer');
	$sOQL = 'SELECT TimeSpentBackground WHERE id = :id';
	$aQueryParams = array('id' => $iTimeSpent);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	while ($oTimeSpent = $oSet->Fetch())
	{
		$oEndDate = new DateTime();
		$oTimeSpent->Set('end_date', $oEndDate->format('Y-m-d H:i:s'));
		$oTimeSpent->Set('status',  'stopped');
		$oTimeSpent->DBUpdate();
	}
	$oP->add(json_encode(array('status' =>'ok')));
}

function resetStopwatch(\WebPage $oP)
{
	$iTimeSpent = utils::ReadParam('timespent_id', '', true,'integer');
	$sOQL = 'SELECT TimeSpentBackground WHERE id = :id';
	$aQueryParams = array('id' => $iTimeSpent);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	while ($oTimeSpent = $oSet->Fetch())
	{
		$oTimeSpent->DBDelete();
	}
	$oP->add(json_encode(array('status' =>'ok')));
}

function getHistoryStopwatch(\WebPage $oP)
{
	$aReturn['status'] = 'failed';
	$sObjClass = utils::ReadParam('object_class', '', true,'parameter');
	$iObjId = utils::ReadParam('object_id', -1, true,'parameter');
	$sOQL = 'SELECT Activity WHERE obj_class = :obj_class AND obj_id = :obj_id';
	$aQueryParams = array('obj_class' => $sObjClass, 'obj_id' => $iObjId);
	$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
	$oActivity = $oSet->Fetch();
	if ($oActivity != null)
	{
		$aReturn['status'] = 'ok';
		$aReturn['history'] = array();
		$iActivity = $oActivity->GetKey();
		$sOQL = 'SELECT TimeSpent WHERE activity_id = :activity_id AND end_date != \'\'';
		$aQueryParams = array('activity_id' => $iActivity);
		$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
		while ($oTimeSpent = $oSet->Fetch())
		{
			$aHistoryEntry = array();
			$oStartDate = new DateTime($oTimeSpent->Get('start_date'));
			$aHistoryEntry['start_date'] = $oStartDate->format('U')*1000;
			$oEndDate = new DateTime($oTimeSpent->Get('end_date'));
			$aHistoryEntry['end_date'] = $oEndDate->format('U')*1000;
			$aHistoryEntry['origin'] = $oTimeSpent->Get('origin');
			$oUser = MetaModel::GetObject('User', $oTimeSpent->Get('user_id'), false);
			if($oUser !== null)
			{
				$oPerson = MetaModel::GetObject('Person', $oUser->Get('contactid'), false);
				$aHistoryEntry['contact_name'] = $oPerson->Get('friendlyname');
				if(MetaModel::IsValidAttCode('Person', 'picture'))
				{
					$oPersonPicture = $oPerson->Get('picture');
					if (!$oPersonPicture->IsEmpty())
					{
						$aHistoryEntry['contact_picture'] = $oPersonPicture->GetDisplayURL('Person', $oPerson->GetKey(), 'picture');
					}
				}
			}
			$aReturn['history'][] = $aHistoryEntry;
		}
		usort($aReturn['history'],'\TimeTrackingUtils\sortTimeSpentByStartTime');
	}
	$oP->add(json_encode($aReturn));
}
