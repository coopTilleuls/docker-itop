<?php namespace TimeTrackingUtils;
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

function sortActivitiesByCategory($a1, $a2)
{
	if($a1['category'] === \Dict::S('TimeTracking:Category:Favorite'))
	{
		if($a2['category'] === \Dict::S('TimeTracking:Category:Favorite'))
		{
			return 0;
		}
		else if($a2['category'] === \Dict::S('TimeTracking:Category:All'))
		{
			return -1;
		}
	}
	else if($a1['category'] === \Dict::S('TimeTracking:Category:All'))
	{
		if($a2['category'] === \Dict::S('TimeTracking:Category:Favorite'))
		{
			return 1;
		}
		else if($a2['category'] === \Dict::S('TimeTracking:Category:All'))
		{
			return 0;
		}
	}
}
/*
 * Compare function to build a TimeSpent array from more recent to older
 */
function sortTimeSpentByStartTime($a1, $a2)
{
	if ($a1['start_date'] == $a2['start_date']) {
		return 0;
	}
	return ($a1['start_date'] < $a2['start_date']) ? 1 : -1;
}

function sortPossibleActivitiesByClass($a1, $a2)
{
	if($a1['object_class'] === $a2['object_class'] )
	{
		return strcmp($a1['friendlyname'], $a2['friendlyname']);
	}
	else
	{
		return strcmp($a1['object_class'], $a2['object_class']);
	}
}


function sortPossibleActivitiesByFriendlyname($a1, $a2)
{
	return strcmp($a1['friendlyname'], $a2['friendlyname']);
}
function sortPossibleActivitiesClasses($a1, $a2)
{
	$sFavoriteString = \Dict::S('TimeTracking:Category:Favorite');
	if($a1 === $sFavoriteString)
	{
		return -1;
	}
	else if ($a2 === $sFavoriteString)
	{
		return 1;
	}
	return strcmp($a1,$a2);
}