<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

require_once ('timetrackingview.class.inc.php');
class TimeTrackingPlugIn implements iApplicationUIExtension, iApplicationObjectExtension
{
	protected static $m_bIsModified = false;

	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if (!$bEditMode && $oObject instanceof Activity && !$oObject->IsNew()){
			$oPage->add_linked_stylesheet('../'.'env-'.utils::GetCurrentEnvironment().'/'.TimeTrackingView::MODULE_CODE.'/css/activity.css');
			
			$sObjClass = $oObject->Get('obj_class');
			$sObjId = $oObject->Get('obj_id');
			
			$sIconClass = 'fas fa-external-link-alt';
			if(TimeTrackingView::UseLegacy())
			{
				$sIconClass = 'fa fa-external-link';
			}
			$sObjUrl = DBObject::MakeHyperlink($sObjClass, $sObjId, '<i class="'.$sIconClass.'" aria-hidden="true"></i>');

			$sFieldSelector = '[data-attribute-code="obj_id"] .ibo-field--value';
			if(TimeTrackingView::UseLegacy())
			{
				$sFieldSelector = '[data-attribute-code="obj_id"] .field_value';
			}
			
			$oPage->add_ready_script(
				<<<JS
			var ActivityUrl = $('$sObjUrl').find('a').attr('target', '_blank').addClass('tt-activity-object-link');
			$('$sFieldSelector').append(ActivityUrl);
JS
			);
	}
	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
		$oUser = UserRights::GetUserObject();
		if (!$bEditMode && !$oObject->IsNew() && Activity::IsTargetObject($oObject, 'calendar-tab') && TimeTrackingRightsPlugin::HasTimeTrackingTabDisplayRights($oUser))
		{
				$sTabTitle = Dict::S('TimeTracking:TabLabel');
				$oPage->SetCurrentTab($sTabTitle);
				$oView = new TimeTrackingView('Object', true);
				$oView->Display($oPage,'timetracking_view', array('obj_class' => get_class($oObject), 'obj_id' => $oObject->GetKey()));
		}
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
	}

	public function OnFormCancel($sTempId)
	{
	}

	public function EnumUsedAttributes($oObject)
	{
		return array();
	}

	public function GetIcon($oObject)
	{
		return '';
	}

	public function GetHilightClass($oObject)
	{
		// Possible return values are:
		// HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE
		return HILIGHT_CLASS_NONE;
	}

	public function EnumAllowedActions(DBObjectSet $oSet)
	{
		// No action
		return array();
	}

	public function OnIsModified($oObject)
	{
		return self::$m_bIsModified;
	}

	public function OnCheckToWrite($oObject)
	{
		return array();
	}

	public function OnCheckToDelete($oObject)
	{
		return array();
	}

	public function OnDBUpdate($oObject, $oChange = null)
	{
	}

	public function OnDBInsert($oObject, $oChange = null)
	{
	}

	public function OnDBDelete($oObject, $oChange = null)
	{
	}
}
