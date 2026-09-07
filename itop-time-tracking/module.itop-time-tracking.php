<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'itop-time-tracking/2.3.3',
	array(
		// Identification
		//
		'label' => 'Time Tracking',
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			'itop-legacy-search-base/1.0.0',
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'model.itop-time-tracking.php',
			'class/activitycolors.class.inc.php',
			'class/stopwatchplugin.class.inc.php',
			'class/stopwatchview.class.inc.php',
			'class/timetrackingplugin.class.inc.php',
			'class/timetrackingreportplugin.class.inc.php',
			'class/timetrackingview.class.inc.php',
			'class/timespentbackgroundprocess.class.inc.php',
			'dashletbarchart.class.inc.php'
		),
		'webservice' => array(

		),
		'data.struct' => array(
			// add your 'structure' definition XML files here,

		),
		'data.sample' => array(
			// add your sample data XML files here,
		),

		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any

		// Default settings
		//
		'settings' => array(
			'allowed_classes' => array (
				'UserRequest' => array(
					'calendar-tab' => 'SELECT UserRequest WHERE status != "closed"',
					'stopwatch' => 'SELECT UserRequest WHERE status != "closed"',
					'report-tab' => 'SELECT UserRequest WHERE status IN ("resolved", "closed")',
				),
				'Incident' => array(
					'calendar-tab' => 'SELECT Incident WHERE status != "closed"',
					'stopwatch' => 'SELECT Incident WHERE status != "closed"',
					'report-tab' => 'SELECT Incident WHERE status IN ("resolved", "closed")',
				),
				'CustomerContract' => array(
					'calendar-page' => 'SELECT CustomerContract WHERE status = "production"',
					'report-tab' => 'SELECT CustomerContract',
				),
			),
			'colors' => array(
				'default_stopwatch' =>  array('text'=> '#ffffff', 'background'=> '#a6a6a6'),
				'default_calendar' =>  array('text'=> '#ffffff', 'background'=> '#FFCC80'),
				'classes' => array(
					'UserRequest' => array('text'=> '#ffffff', 'background'=> 'shadeof:blue'),
					'Incident' => array('text'=> '#ffffff', 'background'=> 'shadeof:green'),
					'CustomerContract' => array('text'=> '#ffffff', 'background'=> 'shadeof:grey'),

				)
			),
			'clone_events' => false,
			'default_event_duration' => '00:30:00',
			'day_start_time' => '06:00:00',
			'day_end_time' => '22:00:00',
			'excluded_days' => array('Saturday','Sunday'),
			'first_day' => 1,
			'business_hours'=> array(
				'days_of_week' => array('1', '2', '3', '4', '5'),
				'start' => '08:00:00',
				'end' => '18:00:00',
			),
			'minimum_event_duration_display' => '00:30:00',
			'stopwatch_clean_periodicity' => 1,
			'stopwatch_max_time' => 4,
			'delete_max_event_age' => 30,
			'default_report_query'=> 'SELECT TimeSpent WHERE contact_id = :contact_id AND start_date >= :start_date AND end_date < :end_date',
			'manager_report_query'=> 'SELECT TimeSpent WHERE start_date >= :start_date AND end_date < :end_date',
			'manager_report_silo' => 'SELECT Person',
			'weekly_report_time_spent_attribute' => '',
			'weekly_report_time_spent_default' => '30hrs',
			'report_charts_definition' => array(
				array('group_by_attribute' => 'contact_id', 'label' => 'TimeTracking:ReportActivityPerUser'),
				array('group_by_attribute' => 'org_id', 'label' => 'TimeTracking:ReportActivityPerCustomer')
			)
		),
	)
);


?>
