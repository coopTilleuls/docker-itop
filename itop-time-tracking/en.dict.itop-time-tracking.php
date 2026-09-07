<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

Dict::Add('EN US', 'English', 'English', array(
	'TimeTracking:TitlePage' => 'Time tracking',
	'TimeTracking:TrackingTimeOn' => 'Tracking time on',
	'TimeTracking:PressButtonToStartTrackingOn' => 'Press play to start tracking on',
	'TimeTracking:TrackingTimeOnDiffObj' => 'Tracking time on different object',
	'TimeTracking:TrackingTimeStopwatchForcedStop' => 'Force-stopped stopwatch',
	'TimeTracking:Category:Favorite' => 'Favorite',
	'TimeTracking:Category:All' => 'All',
	'TimeTracking:TabLabel' => 'Track my time',
	'TimeTracking:ReportTabLabel' => 'Time spent report',
	'TrackingTime:AddTimeSpent' => 'Add time spent',
	'TimeTracking:ClassicMode' => 'Classic mode',
	'TimeTracking:ModalMode' => 'Modal mode',
	'TimeTracking::TrackedFrom:calendar' => 'Tracked from calendar',
	'TimeTracking::TrackedFrom:stopwatch' => 'Tracked from stopwatch',
	'TimeTracking::TrackedFrom:manual' => 'Tracked manually',

	'TimeTracking:SelectColorForActivity' => 'Select colors for this activity',
	'TimeTracking:SetColor' => 'Set colors',
	'TimeTacking:Stopwatch:Start' => 'Start',
	'TimeTacking:Stopwatch:Stop' => 'Stop',
	'TimeTacking:Stopwatch:Reset' => 'Reset',
	'TimeTracking:SelectActivity:Placeholder' => 'Select an activity',
	'TimeSpent:Edition' => 'Edition',
	'TimeSpent:Information' => 'Additional information',

	'TimeTracking:DayOfWeek-0' => 'Sun',
	'TimeTracking:DayOfWeek-1' => 'Mon',
	'TimeTracking:DayOfWeek-2' => 'Tue',
	'TimeTracking:DayOfWeek-3' => 'Wed',
	'TimeTracking:DayOfWeek-4' => 'Thu',
	'TimeTracking:DayOfWeek-5' => 'Fri',
	'TimeTracking:DayOfWeek-6' => 'Sat',

	'TimeTracking:ReportActivityPerUser' => 'Cumulated Activity per User (in Hours)',
	'TimeTracking:ReportActivityPerCustomer' => 'Cumulated Activity per Customer (in Hours)',

	'TimeTracking:Title:MyActivities' => 'My time tracking report',
	'TimeTracking:Title:Report' => 'Time tracking report',
	'TimeTracking:Button:Close' => 'Close',
	'TimeTracking:Today' => 'Today',
	'TimeTracking:Month' => 'Month',
	'TimeTracking:Week' => 'Week',
	'TimeTracking:Day' => 'Day',
	'TimeTracking:Total_Duration' => 'Total duration: %1$s',
	'TimeTracking:Daily' => 'Daily report',
	'TimeTracking:Weekly' => 'Weekly report',
	'TimeTracking:Monthly' => 'Monthly report',
	'TimeTracking:DailyNext' => ' Next day ► ',
	'TimeTracking:DailyPrev' => ' ◄ Prev. day ',
	'TimeTracking:WeeklyNext' => ' Next week ► ',
	'TimeTracking:WeeklyPrev' => ' ◄ Prev. week ',
	'TimeTracking:MonthlyNext' => ' Next month ► ',
	'TimeTracking:MonthlyPrev' => ' ◄ Prev. month ',
	'TimeTracking:Report_From_To' => 'From %1$s to %2$s',
	'TimeTracking:DateRangeFormat' => 'd/m',
	'TimeTracking:DayFormat' => 'd/m/Y',
	'TimeTracking:MoreCriteria' => 'More criteria',
	'TimeTracking:LessCriteria' => 'Less criteria',
	'TimeTracking:ReportOverview:Label' => 'Time tracking overview',
	'TimeTracking:ReportOverview:ContactHeader' => 'Contact',
	'TimeTracking:ReportOverview:DurationHeader' => 'Duration',
	'TimeTracking:ReportOverview:RequiredDurationHeader' => 'Required duration',
	'TimeTracking:TimeSpentReport:Label' => 'Time spent report',
	
	'TimeTracking:Error:Generic' => 'Something went wrong',
	'TimeTracking:Error:WrongActivity' => 'Couldn\'t perform this action for this activity',
	'TimeTracking:Error:DeleteExpired' => 'This event is too old and you can\'t delete it',
	'TimeTracking:Error:DeleteArchived' => 'This event is archived and you can\'t delete it',
	'TimeTracking:Error:DeleteRights' => 'Not enough rights to delete this event',
	'TimeTracking:Error:UpdateExpired' => 'This event is too old and you can\'t update it',
	'TimeTracking:Error:UpdateRights' => 'Not enough rights to update this event',

	'Menu:TimeTracking' => 'Time Tracking',
	'Menu:TimeTrackingPage' => 'Track my time',
	'Menu:MyTimeTrackingReport' => 'My time tracking report',
	'Menu:TimeTrackingReport' => 'Time tracking report',

	'Class:Activity' => 'Activity',
	'Class:Activity/Name' => '%1$s #%2$s',
	'Class:Activity/Attribute:background_color' => 'Background color',
	'Class:Activity/Attribute:text_color' => 'Text color',
	'Class:Activity/Attribute:obj_class' => 'Object class',
	'Class:Activity/Attribute:obj_id' => 'Object id',
	'Class:Activity/Attribute:label' => 'Label',


	'Class:TimeSpent' => 'Time spent',
	'Class:TimeSpent/Attribute:activity_id' => 'Activity',
	'Class:TimeSpent/Attribute:activity_label' => 'Activity label',
	'Class:TimeSpent/Attribute:contact_id' => 'Contact',
	'Class:TimeSpent/Attribute:contact_id_finalclass_recall' => 'Contact class',
	'Class:TimeSpent/Attribute:duration' => 'Duration',
	'Class:TimeSpent/Attribute:description' => 'Description',
	'Class:TimeSpent/Attribute:end_date' => 'End date',
	'Class:TimeSpent/Attribute:org_id' => 'Organization',
	'Class:TimeSpent/Attribute:origin' => 'Origin',
	'Class:TimeSpent/Attribute:origin/Value:calendar' => 'calendar',
	'Class:TimeSpent/Attribute:origin/Value:stopwatch' => 'stopwatch',
	'Class:TimeSpent/Attribute:origin/Value:manual' => 'manual',

	'Class:TimeSpent/Attribute:start_date' => 'Start date',
	'Class:TimeSpent/Attribute:title' => 'Title',
	'Class:TimeSpent/Attribute:user_id' => 'User',
	'Class:TimeSpent/Attribute:user_id_finalclass_recall' => 'User class',

	'Class:TimeSpentBackground/Name' => '%1$s %2$s',
	'Class:TimeSpentBackground' => 'Time spent background',
	'Class:TimeSpentBackground/Attribute:status' => 'Status',


	'Class:FavouriteActivity' => 'Favorite activity',
	'Class:FavouriteActivity/Attribute:activity_id' => 'Activity',
	'Class:FavouriteActivity/Attribute:user_id' => 'User',
	'Class:FavouriteActivity/Attribute:user_id_finalclass_recall' => 'User class',

	'Class:UserColorActivity' => 'User color activity',
	'Class:UserColorActivity/Attribute:background_color' => 'Background color',
	'Class:UserColorActivity/Attribute:text_color' => 'Text color',
	'Class:UserColorActivity/Attribute:activity_id' => 'Activity',
	'Class:UserColorActivity/Attribute:user_id' => 'User',
	
	'Class:TriggerOnForceStopTimeSpentBackground' => 'Trigger (when a time tracking stopwatch times out)',
	'Class:TriggerOnForceStopTimeSpentBackground+' => 'Trigger activated when a time tracking stopwatch is force stopped by cron',
));

