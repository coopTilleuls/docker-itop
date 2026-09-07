<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

class StopwatchPlugIn implements iApplicationUIExtension, iApplicationObjectExtension, iPageUIExtension
{
	protected static $m_bIsModified = false;

	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{

	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{

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

	/**
	 * Add content to the North pane
	 *
	 * @param iTopWebPage $oPage The page to insert stuff into.
	 *
	 * @return string The HTML content to add into the page
	 */
	public function GetNorthPaneHtml(iTopWebPage $oPage)
	{
		// TODO: Implement GetNorthPaneHtml() method.
	}

	/**
	 * Add content to the South pane
	 *
	 * @param iTopWebPage $oPage The page to insert stuff into.
	 *
	 * @return string The HTML content to add into the page
	 */
	public function GetSouthPaneHtml(iTopWebPage $oPage)
	{
		// TODO: Implement GetSouthPaneHtml() method.
	}

	/**
	 * Add content to the "admin banner"
	 *
	 * @param iTopWebPage $oPage The page to insert stuff into.
	 *
	 * @return string The HTML content to add into the page
	 */
	public function GetBannerHtml(iTopWebPage $oPage)
	{
		$oUser = UserRights::GetUserObject();
		if(TimeTrackingRightsPlugin::HasStopwatchDisplayRights($oUser))
		{
			$oCurrentObject = self::GetObjectFromPageParams();
			$sOQL = "SELECT TimeSpentBackground WHERE user_id= :user AND status=\"ongoing\"";
			$oActivesChronoSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL),
				array(),
				array('user' => UserRights::GetUserId()));
			$oActiveChrono = $oActivesChronoSet->Fetch();
	
	
			if ($oActiveChrono !== null)
			{
				if ($oCurrentObject !== null)
				{
					$sOQL = "SELECT Activity WHERE obj_id= :obj_id AND obj_class=:obj_class";
					$oCurrentObjectActivitySet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL),
						array(),
						array(
							'obj_id' => $oCurrentObject->GetKey(),
							'obj_class' => get_class($oCurrentObject)
						));
					$oCurrentObjectActivity = $oCurrentObjectActivitySet->Fetch();
					if ($oCurrentObjectActivity !== null && $oCurrentObjectActivity->GetKey() == $oActiveChrono->Get('activity_id'))
					{
						$oView = new StopwatchView('onactiveobject');
						$oView->Display($oPage,
							'stopwatch_view',
							array(
								'timespentbackground' => $oActiveChrono,
								'obj_class' => get_class($oCurrentObject),
								'obj_id' => $oCurrentObject->GetKey()
							));
					}
					else
					{
						if (Activity::IsTargetObject($oCurrentObject,
							'stopwatch'))
						{
							$oView = new StopwatchView('onobject_stopwatchon');
							$oView->Display($oPage,
								'stopwatch_view',
								array(
									'timespentbackground' => $oActiveChrono,
									'obj_class' => get_class($oCurrentObject),
									'obj_id' => $oCurrentObject->GetKey()
								));
						}
					}
				}
				else
				{
					if ($oCurrentObject === null)
					{
						$sOQL = "SELECT Activity WHERE id=:id";
						$oCurrentActivitySet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL),
							array(),
							array('id' => $oActiveChrono->Get('activity_id'),));
						$oCurrentActivity = $oCurrentActivitySet->Fetch();
						if ($oCurrentActivity !== null)
						{
							$oView = new StopwatchView('onpage_stopwatchon');
							$oView->Display($oPage,
								'stopwatch_view',
								array(
									'timespentbackground' => $oActiveChrono,
									'activity' => $oCurrentActivity
								));
						}
					}
				}
			}
			else
			{
				if ($oCurrentObject !== null && Activity::IsTargetObject($oCurrentObject,
						'stopwatch'))
				{
					$oView = new StopwatchView('onobject_stopwatchoff');
					$oView->Display($oPage,
						'stopwatch_view',
						array(
							'obj_class' => get_class($oCurrentObject),
							'obj_id' => $oCurrentObject->GetKey()
						));
				}
			}
		}
		return '';
	}

	/**
	 * Check if the current page is the details of an object
	 * Enter description here ...
	 */
	protected static function GetObjectFromPageParams()
	{
		$oObject = null;
		$sOperation = utils::ReadParam('operation', '');
		$sClass = utils::ReadParam('class', '');
		if (MetaModel::IsValidClass($sClass))
		{
			switch($sOperation)
			{
				case 'details':
				case 'modify':
				case 'stimulus':
					$iKey = utils::ReadParam('id', -1);
					$oObject = MetaModel::GetObject($sClass, $iKey, false);
					break;

				default:
			}
		}
		return $oObject;
	}
}
