<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

class TimeSpentBackgroundProcess implements iBackgroundProcess
{
	const DEFAULT_STOPWATCH_MAX_TIME = 4;
	const DEFAULT_STOPWATCH_CLEAN_PERIODICITY = 1;
	
	public function GetPeriodicity()
	{
		return (int)MetaModel::GetModuleSetting(StopwatchView::MODULE_CODE, 'stopwatch_clean_periodicity', static::DEFAULT_STOPWATCH_CLEAN_PERIODICITY)*3600; // seconds, default every 12 hours
	}

	/**
	 * @param int $iTimeLimit
	 *
	 * @return string
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \DeleteException
	 * @throws \MySQLException
	 * @throws \OQLException
	 * @throws \Exception
	 */
	public function Process($iTimeLimit)
	{
		$aResults = array();
		$sOQL = 'SELECT TimeSpentBackground WHERE status="ongoing" AND start_date <= DATE_SUB(NOW(), INTERVAL :hours HOUR)';
		$aQueryParams = array('hours' => MetaModel::GetModuleSetting(StopwatchView::MODULE_CODE, 'stopwatch_max_time', static::DEFAULT_STOPWATCH_MAX_TIME));
		$oSet = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), array(), $aQueryParams);
		while ($oTimeSpent = $oSet->Fetch())
		{
			$oEndDate = new DateTime();
			$oTimeSpent->Set('end_date', $oEndDate->format('Y-m-d H:i:s'));
			$oTimeSpent->Set('description', Dict::S('TimeTracking:TrackingTimeStopwatchForcedStop'));
			$oTimeSpent->Set('status', 'stopped');
			$oTimeSpent->DBUpdate();
			$oTimeSpentId = $oTimeSpent->GetKey();
			$aResults[] = "Force stopped timed out stopwatch with id : $oTimeSpentId";

			$oSet = new DBObjectSet(DBObjectSearch::FromOQL("SELECT TriggerOnForceStopTimeSpentBackground"));
			while ($oTrigger = $oSet->Fetch())
			{
				/** @var \Trigger $oTrigger */
				$oTrigger->DoActivate($oTimeSpent->ToArgs('this'));
			}
		}
		return implode(' \r\n ', $aResults);
	}
}