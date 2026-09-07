<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

class TimeTrackingView
{
	const MODULE_CODE = 'itop-time-tracking';
	/* Enumerations */
	const ENUM_TIMEFRAME_DAY = 'day';
	const ENUM_TIMEFRAME_WEEK = 'week';
	const ENUM_TIMEFRAME_MONTH = 'month';
	const ENUM_VIEW_DAILY_BASIC = 'basicDay';
	const ENUM_VIEW_DAILY_AGENDA = 'agendaDay';
	const ENUM_VIEW_WEEKLY_BASIC = 'basicWeek';
	const ENUM_VIEW_WEEKLY_AGENDA = 'agendaWeek';
	const ENUM_VIEW_MONTHLY_BASIC = 'month';
	const ENUM_VIEW_MONTHLY_LIST = 'listMonth';
	const DEFAULT_TITLE = '';
	const DEFAULT_TIMEFRAME = self::ENUM_TIMEFRAME_WEEK;

	const DEFAULT_FIRST_DAY = 1;
	const DEFAULT_EVENT_DURATION = 30;

	//const DEFAULT_BUSINESS_DAYS = array(1, 2, 3, 4, 5);
	const DEFAULT_BUSINESS_HOURS_START = '08:00:00';
	const DEFAULT_BUSINESS_HOURS_END = '18:00:00';
	const DEFAULT_AGENDA_START = '00:00:00';
	const DEFAULT_AGENDA_END = '23:59:59';

	protected $sMode;
	protected $bEditMode;
	protected $sTimeframe;
	
	const XML_LEGACY_VERSION = '1.7';

	/**
	 * Compare static::XML_LEGACY_VERSION with ITOP_DESIGN_LATEST_VERSION and returns true if the later is <= to the former.
	 * If static::XML_LEGACY_VERSION, return false
	 * 
	 * @return bool
	 * 
	 * @since 2.3.0
	 */
	public static function UseLegacy(){
		return static::XML_LEGACY_VERSION !== '' ? version_compare(ITOP_DESIGN_LATEST_VERSION, static::XML_LEGACY_VERSION, '<=') : false;
	}
	
	public function __construct($sMode, $bEditMode= true, $sTimeframe = self::DEFAULT_TIMEFRAME)
	{
		$this->sMode = $sMode;
		$this->sTimeframe = $sTimeframe;
		$this->bEditMode = $bEditMode;
	}
	public function Display(\WebPage $oP, $sId = 'timetracking_view', $aExtraParams = array())
	{
		$oP->add_dict_entry('TimeTracking:SelectColorForActivity');
		$oP->add_dict_entry('TimeTracking:SetColor');
		$oP->add_dict_entry('TimeTracking:SelectActivity:Placeholder');
		$oP->add_dict_entry('UI:Button:Delete');
		$oP->add_dict_entry('UI:Button:Apply');
		$oP->add_dict_entry('UI:Delect:Confirm_Object');
		$oP->add_dict_entry('Class:TimeSpent');

		$sAbsUrlModulesRoot = utils::GetAbsoluteUrlModulesRoot().static::MODULE_CODE;
		$sAbsApp = utils::GetAbsoluteUrlAppRoot();
		$oP->add_saas('env-'.utils::GetCurrentEnvironment()."/".static::MODULE_CODE."/css/modal.scss");
		$oP->add_saas('env-'.utils::GetCurrentEnvironment()."/".static::MODULE_CODE."/css/time_tracking.scss");
		if(static::UseLegacy()){
			$oP->add_linked_stylesheet('../env-'.utils::GetCurrentEnvironment()."/".static::MODULE_CODE."/legacy/css/combobox_widget.css");
		}
		else{
			$oP->add_linked_stylesheet('../env-'.utils::GetCurrentEnvironment()."/".static::MODULE_CODE."/css/combobox_widget.css");
		}
		$oAppContext = new ApplicationContext();
		$sGetActivity = '';
		$sModalSwitcherHtml = '';
		$sActivityMode = 'none';
		$oP->add('<div id="tt_header">');
		switch ($this->sMode)
		{
			case 'Object':
				$sObjClass = $aExtraParams['obj_class'];
				$iObjId = $aExtraParams['obj_id'];
				$oP->add('<input type="hidden" class="get_tt_activity" value="'.$sObjClass.','.$iObjId.'"/>');
				break;
			case 'User':
				$sActivityMode = appUserPreferences::GetPref('timetracking_activity_picker', 'classic');
				$sDivClass = 'tt_activity_classic';
				$sCloseModalClass = 'tt_activity_modal_close_hidden';
				$sDisplayAddButton = "display:none;";
				$sModalSwitcherHtml = '<div id="timetracking-activity-mode-picker"> <a href="#" id="timetracking-classic-mode">'.Dict::S('TimeTracking:ClassicMode').'</a> | <a href="#" id="timetracking-modal-mode">'.Dict::S('TimeTracking:ModalMode').'</a></div>';
				if ($sActivityMode === 'modal')
				{
					$sDivClass = 'tt_activity_modal';
					$sCloseModalClass = 'tt_activity_modal_close';
					$sDisplayAddButton='';
				}
				$oP->add('<div class="'.$sDivClass.'" ><div><a href="#"><i class="fa fa-times '.$sCloseModalClass.'" aria-hidden="true"></i></a>');
				$oP->add('<select class="get_tt_activity_combobox"></select>');
				$oP->add('<input type="hidden" class="get_tt_activity" value=""/>');
				$oP->add('<i class="fa fa-paint-brush tt_user_color_activity" aria-hidden="true"></i>');
				$oP->add('<button name="Add" style="'.$sDisplayAddButton.'">'.Dict::S('TrackingTime:AddTimeSpent').'</button>');
				$oP->add('</div>');
				$oP->add('</div>');

				break;
		}
			$aCalendarOptions = array(
			'header' => array(
				'left' => 'prev,next today',
				'center' => 'title',
				'right' => implode(',', array(static::ENUM_VIEW_MONTHLY_BASIC, static::ENUM_VIEW_WEEKLY_AGENDA, static::ENUM_VIEW_DAILY_AGENDA, static::ENUM_VIEW_MONTHLY_LIST)),
			),
			'locale' => strtolower(substr(UserRights::GetUserLanguage(), 0, 2)),
			'editable' => $this->bEditMode,
			'navLinks' => true,
			'selectable' => true,
			'timezone' => 'local',
			'weekNumbers' => true,
			'contentHeight' => 'auto',
			'slotDuration' => MetaModel::GetModuleSetting(static::MODULE_CODE, 'default_event_duration', '00:30:00'),
			'weekNumbersWithinDays' => true,
			'hiddenDays' => static::GetExcludedDays(),
			'firstDay' => MetaModel::GetModuleSetting(static::MODULE_CODE, 'first_day', static::DEFAULT_FIRST_DAY),
			'businessHours' => array(
				'dow' => static::GetBusinessDays(),
				'start' => static::GetBusinessHoursStart(),
				'end' => static::GetBusinessHoursEnd(),
			),
			'minTime' => static::GetAgendaStartTime(),
			'maxTime' => static::GetAgendaEndTime(),
			'scrollTime' => static::GetBusinessHoursStart(),
			'defaultView' => 'agendaWeek',
			'allDaySlot' => false,
			'eventResize' => $this->GetEventResize($oAppContext, $aExtraParams),
			'eventLimit' => true,
		);

		$aHandlerOptions = array(
			'endpoints' => array(
				'events' => utils::GetAbsoluteUrlModulesRoot().static::MODULE_CODE.'/ajax.'.static::MODULE_CODE.'.php',
				'activities' =>  utils::GetAbsoluteUrlModulesRoot().static::MODULE_CODE.'/ajax.'.static::MODULE_CODE.'.php',
				'ui' => utils::GetAbsoluteUrlAppRoot().'/pages/UI.php',
			),
			'params' => array(
				'events' => $this->GetEventSourceParams($oAppContext, $aExtraParams),
			),
			'get_activity' =>$sGetActivity,
			'mode' => $this->sMode,
			'activity_mode' => $sActivityMode,
			'id' => $sId,
			'calendar_options' => $aCalendarOptions,
			'clone_events' => MetaModel::GetModuleSetting(static::MODULE_CODE, 'clone_events', false),
			'is_legacy' => static::UseLegacy()
		);
		
		if(static::UseLegacy()){
			$aHandlerOptions['like_button_off'] = 'fa fa-heart-o';
			$aHandlerOptions['like_button_on'] = 'fa fa-heart';
		}
		
		$sOptionsAsJson = json_encode($aHandlerOptions);
		$sHtml = '';
		$sHtml .= $sModalSwitcherHtml;
		$sHtml .= '</div>';
		$sHtml .= '<div id="'.$sId.'" class="timetracking-view-container">';
		$sHtml .= '<div class="timetracking-content">';
		$sHtml .= '</div>';
		$sHtml .= '</div>';
		$oP->add($sHtml);

		$sModuleVersion = utils::GetCompiledModuleVersion(static::MODULE_CODE);
// Note: This is a small file loader as we need to ensure scripts are loaded one after another as there are dependants.
		// We could have integrate an existing file loader but it would have need to be on every pages and could be a future conflict with another extension.
		$oP->add_ready_script(
			<<<EOF
	var aTTFilesToLoad = [
		// CSS
		{ url: "{$sAbsUrlModulesRoot}/css/fullcalendar.min.css?v=$sModuleVersion", type: "text" },
		{ url: "{$sAbsUrlModulesRoot}/css/spectrum.css?v=$sModuleVersion", type: "text" },

		// JS
		{ url: "{$sAbsUrlModulesRoot}/js/jquery.menu.before_itop_2.6_fix.js?v=$sModuleVersion", type: "script" },
		{ url: "{$sAbsUrlModulesRoot}/js/moment.min.js?v=$sModuleVersion", type: "script" },
		{ url: "{$sAbsUrlModulesRoot}/js/fullcalendar.min.js?v=$sModuleVersion", type: "script" },
		{ url: "{$sAbsUrlModulesRoot}/js/spectrum.js?v=$sModuleVersion", type: "script" },
		{ url: "{$sAbsUrlModulesRoot}/js/locale-all.js?v=$sModuleVersion", type: "script" },
		{ url: "{$sAbsUrlModulesRoot}/js/calendar_widget.js?v=$sModuleVersion", type: "script" },
		{ url: "{$sAbsUrlModulesRoot}/js/combobox_activity_widget.js?v=$sModuleVersion", type: "script" },

		{ url: "{$sAbsApp}js/wizardhelper.js", type: "script" },
	];
	var fTTInitCallback = function(){ $('#{$sId}').timetracking_handler({$sOptionsAsJson}); };
	
	var iTTCurrentIdx = 0;
	var iTTFilesToLoadCount = aTTFilesToLoad.length;
	var fTTLoadScript = function(){
		$.when(
			$.ajax({
				url: aTTFilesToLoad[iTTCurrentIdx].url,
				dataType: aTTFilesToLoad[iTTCurrentIdx].type,
				cache: true
			})
			.done(function(){
				if( (aTTFilesToLoad[iTTCurrentIdx].type === 'text') && ($('head link[type="text/css"][href="' + aTTFilesToLoad[iTTCurrentIdx].url + '"]').length === 0) )
				{
					$('<link rel="stylesheet" type="text/css" href="' + aTTFilesToLoad[iTTCurrentIdx].url + '" />').appendTo('head');
				}
			})
		)
		.then(function(){
			iTTCurrentIdx++;
			if(iTTCurrentIdx !== iTTFilesToLoadCount)
			{
				fTTLoadScript();
			}
			else
			{
				fTTInitCallback();
			}
		});
	};
	
	fTTLoadScript();
EOF
		);
	}

	public function GetEventSourceParams($oAppContext, $aExtraParams)
	{
		$aEventSource = array();
		switch ($this->sMode)
		{
			case 'Object':
				if(isset($aExtraParams['obj_class'], $aExtraParams['obj_id']))
				{
					$sObjClass = $aExtraParams['obj_class'];
					$siObjId = $aExtraParams['obj_id'];
					$aEventSource = array('operation' => 'get_events_by_activity', 'object_class' => $sObjClass, 'object_id' => $siObjId);
				}

			break;
			case 'User':
			default:

				if(isset($aExtraParams['user_id']))
				{
					$iUserId = $aExtraParams['user_id'];
					$aEventSource = array('operation' => 'get_events_by_user', 'user_id' => $iUserId);
				}
			break;
		}
		return $aEventSource;
	}
	public function GetEventResize($oAppContext, $aExtraParams)
	{
		$sModuleCode = static::MODULE_CODE;
		$sEventResize =
			<<< EOL
function(event, delta, revertFunc) {
	var data = array(
		'operation' => 'resize_activity',
		'object_class' => event._id,
		'object_id' => event._class,
		'duration' => delta
	),
	$.post(GetAbsoluteUrlModulesRoot()+$sModuleCode, data, function(oResult) {
	}

}
EOL;
		return $sEventResize;
	}


	public static function GetBusinessDays()
	{
		// Note: Not using a default constant as arrays are not allowed in constant in PHP 5.3 & 5.6.
		$aBusinessDays = array(1, 2, 3, 4, 5);
		$aParams = MetaModel::GetModuleSetting(static::MODULE_CODE, 'business_hours', array());
		if(isset($aParams['days_of_week']) && is_array($aParams['days_of_week']))
		{
			$aBusinessDays = $aParams['days_of_week'];
		}
		return $aBusinessDays;
	}
	/**
	 * Returns the hour formatted like 'HH:MIN:SS'
	 *
	 * @return string
	 */
	public static function GetBusinessHoursStart()
	{
		$sBusinessHoursStart = static::DEFAULT_BUSINESS_HOURS_START;
		$aParams = MetaModel::GetModuleSetting(static::MODULE_CODE, 'business_hours', array());
		if(isset($aParams['start']))
		{
			$sBusinessHoursStart = $aParams['start'];
		}
		return $sBusinessHoursStart;
	}
	/**
	 * Returns the hour formatted like 'HH:MIN:SS'
	 *
	 * @return string
	 */
	public static function GetBusinessHoursEnd()
	{
		$sBusinessHoursEnd = static::DEFAULT_BUSINESS_HOURS_END;
		$aParams = MetaModel::GetModuleSetting(static::MODULE_CODE, 'business_hours', array());
		if(isset($aParams['end']))
		{
			$sBusinessHoursEnd = $aParams['end'];
		}
		return $sBusinessHoursEnd;
	}

	/**
	 * Returns the hour formatted like 'HH:MIN:SS'
	 *
	 * @return string
	 */
	public static function GetAgendaStartTime()
	{
		return MetaModel::GetModuleSetting(static::MODULE_CODE, 'day_start_time', static::DEFAULT_AGENDA_START);
	}
	/**
	 * Returns the hour formatted like 'HH:MIN:SS'
	 *
	 * @return string
	 */
	public static function GetAgendaEndTime()
	{
		return MetaModel::GetModuleSetting(static::MODULE_CODE, 'day_end_time', static::DEFAULT_AGENDA_END);
	}

	public static function GetExcludedDays()
	{
		$aFullCalendarDaysOrder = array('Sunday','Monday','Tuesday','Wednesday', 'Thursday', 'Friday','Saturday');
		$aExcludedDay = array();
		foreach (utils::GetConfig()->GetModuleSetting(static::MODULE_CODE,'excluded_days', array()) as $sDay)
		{
			$DayValue = array_search($sDay, $aFullCalendarDaysOrder);
			if( $DayValue !== false)
			{
				$aExcludedDay[] = $DayValue;
			}
		}
		return $aExcludedDay;
	}
}
