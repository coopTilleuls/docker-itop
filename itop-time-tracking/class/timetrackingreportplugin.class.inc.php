<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

class TimeTrackingReportPlugIn implements iApplicationUIExtension, iApplicationObjectExtension
{
	protected static $m_bIsModified = false;

	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{		
		$oUser = UserRights::GetUserObject();
		if (!$bEditMode && !$oObject->IsNew() && Activity::IsTargetObject($oObject, 'report-tab') && TimeTrackingRightsPlugin::HasTimeTrackingReportTabDisplayRights($oUser))
		{
			$iActivityId = Activity::getOrCreateActivity(0,  get_class($oObject), $oObject->GetKey());
			if($iActivityId > 0)
			{
				$sTabTitle = Dict::S('TimeTracking:ReportTabLabel');
				$oPage->SetCurrentTab($sTabTitle);
				$sOQL = 'SELECT TimeSpent WHERE activity_id = :activity_id';
				$oSearch = DBObjectSearch::FromOQL($sOQL);
				$aQueryParams = array('activity_id' => $iActivityId);

				$oBlock = new DisplayBlock($oSearch, 'list', false, array('query_params' => $aQueryParams, 'table_id' => 'MyActivities'));

				$iTotalDuration = 0;

				$oActivitySet = new DBObjectSet($oBlock->GetFilter(), array(), $aQueryParams);
				while($oActivity = $oActivitySet->Fetch())
				{
					$iTotalDuration += $oActivity->Get('duration');
				}

				$aDuration = AttributeDuration::SplitDuration($iTotalDuration);
				$sDuration = Dict::Format('Core:Duration_Hours_Minutes_Seconds', ($aDuration['days'] * 24) + $aDuration['hours'], $aDuration['minutes'], $aDuration['seconds']);
				$oPage->add('<div class="tt_total_duration" style="margin-top: 20px;">'.Dict::Format('TimeTracking:Total_Duration', $sDuration.'</div>'));

				$oBlock->Display($oPage, 'tt-report-tab');
			}
			
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

