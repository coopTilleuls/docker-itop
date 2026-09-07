<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

/**
 * This file contains the charts to be drawn at the bottom of the "report.php" page.
 * The file is directly included into report.php.
 * 
 * Context variables:
 * 
 * $oPage (WebPage) The page used for the ouput.
 * 
 * $oActivitySet (DBObjectSet) The set of "Activity" objects resulting from the search.
 *                             The set has probably already been traversed, so don't forget to Rewind() it.
 *                             
 * $sReport (String) The type of report: 'daily', 'weekly' or 'monthly'.
 *
 * $sMode (String) Set to 'self' when the report is limited to the current user. Otherwise 'report'.
 */

if (($sReport != 'daily') && ($oActivitySet->Count() > 0))
{
	// Only display charts for weekly and monthly reports, if the result set is not empty
	
	// Style the chart container
	$oPage->add_style("
	.raphael-bar-chart {
	  margin: 15px auto;
	  width: 850px;
	  height: 350px;
	  text-align: center;
	  border: #efefef 1px solid;
	}");	
	
	$iChartId = 0;
	
	$aChartsGroupBy = utils::GetConfig()->GetModuleSetting('itop-time-tracking', 'report_charts_definition', array());

	foreach ($aChartsGroupBy as $aChartGroupBy)
	{
		if(MetaModel::IsValidAttCode($oActivitySet->GetClass(), $aChartGroupBy['group_by_attribute']))
		{
			$oChart = new StackedBarChartView($oActivitySet, $sReport, 'start_date', $aChartGroupBy['group_by_attribute'], 'duration');
			$oChart->Display($oPage, 'bar_chart_'.($iChartId++) /* unique ID */, true /* display legend */, Dict::S($aChartGroupBy['label']));
		}
	}
}		