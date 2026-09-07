<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

class StopwatchView
{
	const MODULE_CODE = 'itop-time-tracking';

	protected $sMode;

	public function __construct($sMode)
	{
		$this->sMode = $sMode;
	}
	public function Display(\WebPage $oP, $sId = 'stopwatch_view', $aExtraParams = array())
	{
		if(TimeTrackingView::UseLegacy()){
			$oP->add_linked_stylesheet('../env-'.utils::GetCurrentEnvironment()."/".static::MODULE_CODE."/legacy/css/stopwatch_widget.css");
		}
		else{
			$oP->add_linked_stylesheet('../env-'.utils::GetCurrentEnvironment()."/".static::MODULE_CODE."/css/stopwatch_widget.css");
			$oP->add_ready_script("$('#ibo-top-bar').append($('#$sId'))");
		}
		
		$oP->add_dict_entry('TimeTracking:PressButtonToStartTrackingOn');
		$oP->add_dict_entry('TimeTracking:TrackingTimeOnDiffObj');
		$oP->add_dict_entry('TimeTracking:TrackingTimeOn');
		$oP->add_dict_entry('TimeTracking::TrackedFrom:stopwatch');
		$oP->add_dict_entry('TimeTracking::TrackedFrom:calendar');
		$oP->add_dict_entry('TimeTracking::TrackedFrom:manual');

		// Prepare icon CSS classes
		if (version_compare(ITOP_VERSION, '2.7.0', '<'))
		{
			$sShowBookmarkIconCSSClasses = "fa fa-clock-o";
			$sHideBookmarkIconCSSClasses = "fa fa-chevron-right";
			$sStartIconCSSClasses = "fa fa-play";
			$sStopIconCSSClasses = "fa fa-stop";
			$sResetIconCSSClases = "fa fa-trash";
		}
		else
		{
			$sShowBookmarkIconCSSClasses = "far fa-clock";
			$sHideBookmarkIconCSSClasses = "fas fa-chevron-right";
			$sStartIconCSSClasses = "fas fa-play";
			$sStopIconCSSClasses = "fas fa-stop";
			$sResetIconCSSClases = "fas fa-trash-alt";
		}

		$sAbsUrlModulesRoot = utils::GetAbsoluteUrlModulesRoot().static::MODULE_CODE;
		$sAbsApp = utils::GetAbsoluteUrlAppRoot();
		$aStopwatchOptions = array(
			'endpoints' => array(
				'timespentbackground' => utils::GetAbsoluteUrlModulesRoot().static::MODULE_CODE.'/ajax.'.static::MODULE_CODE.'.php',
	    ),
		);
		$sBookmarkShowClass = "stopwatch-bookmark-show";
		$sViewShowClass = "stopwatch-view-hidden";
		switch ($this->sMode)
		{
			case 'onactiveobject':
				$aStopwatchOptions['activity_id'] = $aExtraParams['timespentbackground']->Get('activity_id');
				$aStopwatchOptions['timespent_id'] = $aExtraParams['timespentbackground']->GetKey();
				$aStopwatchOptions['start_date'] = $aExtraParams['timespentbackground']->Get('start_date');
				$aStopwatchOptions['object_class'] = $aExtraParams['obj_class'];
				$aStopwatchOptions['object_id'] = $aExtraParams['obj_id'];
				$aStopwatchOptions['object_friendlyname'] = MetaModel::GetObject($aExtraParams['obj_class'], $aExtraParams['obj_id'], true)->Get('friendlyname');
				$aStopwatchOptions['configuration'] = 'onactiveobject';
				break;
			case 'onobject_stopwatchon':
				$aStopwatchOptions['activity_id'] = $aExtraParams['timespentbackground']->Get('activity_id');
				$aStopwatchOptions['timespent_id'] = $aExtraParams['timespentbackground']->GetKey();
				$aStopwatchOptions['start_date'] = $aExtraParams['timespentbackground']->Get('start_date');
				$aStopwatchOptions['object_class'] = $aExtraParams['obj_class'];
				$aStopwatchOptions['object_id'] = $aExtraParams['obj_id'];
				$aStopwatchOptions['object_friendlyname'] = MetaModel::GetObject($aExtraParams['obj_class'], $aExtraParams['obj_id'], true)->Get('friendlyname');
				$aStopwatchOptions['current_object_friendlyname'] = $aExtraParams['timespentbackground']->Get('title');
				$aStopwatchOptions['configuration'] = 'onobject_stopwatchon';

				$sBookmarkShowClass = "stopwatch-bookmark-hide";
				$sViewShowClass = "stopwatch-view-show";
				break;
			case 'onobject_stopwatchoff':
				$aStopwatchOptions['object_class'] = $aExtraParams['obj_class'];
				$aStopwatchOptions['object_id'] = $aExtraParams['obj_id'];
				$aStopwatchOptions['object_friendlyname'] = MetaModel::GetObject($aExtraParams['obj_class'], $aExtraParams['obj_id'], true)->Get('friendlyname');
				$aStopwatchOptions['configuration'] = 'onobject_stopwatchoff';
				break;
			case 'onpage_stopwatchon':
				$aStopwatchOptions['activity_id'] = $aExtraParams['timespentbackground']->Get('activity_id');
				$aStopwatchOptions['timespent_id'] = $aExtraParams['timespentbackground']->GetKey();
				$aStopwatchOptions['start_date'] = $aExtraParams['timespentbackground']->Get('start_date');
				$aStopwatchOptions['object_class'] = $aExtraParams['activity']->Get('obj_class');
				$aStopwatchOptions['object_id'] =  $aExtraParams['activity']->Get('obj_id');
				$aStopwatchOptions['object_friendlyname'] = MetaModel::GetObject($aExtraParams['activity']->Get('obj_class'), $aExtraParams['activity']->Get('obj_id'), true)->Get('friendlyname');
				$aStopwatchOptions['configuration'] = 'onpage_stopwatchon';
				break;
		}
		$sOptionsAsJson = json_encode($aStopwatchOptions);
		$sHtml = '';
		$sHtml .= '<div id="'.$sId.'" class="stopwatch-container '.$sViewShowClass.'" style="display:none">';
		$sHtml .= '<div class="stopwatch-bookmark '.$sBookmarkShowClass.'">';
		$sHtml .= '<i class="stopwatch-bookmark-open-icon '.$sShowBookmarkIconCSSClasses.'" aria-hidden="true"></i>';
		$sHtml .= '<i class="stopwatch-bookmark-close-icon '.$sHideBookmarkIconCSSClasses.'" aria-hidden="true"></i>';
		$sHtml .= '</div>';
		$sHtml .= '<div class="stopwatch-content" style="">';
		$sHtml .= '<div class="stopwatch-msg">';
		$sHtml .= '</div>';
		$sHtml .= '<div class="stopwatch-time">';
		$sHtml .= '</div>';
		$sHtml .= '<div class="stopwatch-button-bar">';
		$sHtml .= '<i class="'.$sStartIconCSSClasses.' play" aria-hidden="true" title="'.Dict::S('TimeTacking:Stopwatch:Start').'"></i>';
		$sHtml .= '<i class="'.$sStopIconCSSClasses.' stop" aria-hidden="true" title="'.Dict::S('TimeTacking:Stopwatch:Stop').'"></i>';
		$sHtml .= '<i class="'.$sResetIconCSSClases.' reset" aria-hidden="true" title="'.Dict::S('TimeTacking:Stopwatch:Reset').'"></i>';
		$sHtml .= '</div>';
		$sHtml .= '<div class="stopwatch-history">';
		$sHtml .= '</div>';
		$sHtml .= '</div>';
		$sHtml .= '</div>';
		$oP->add($sHtml);
		
		$sModuleVersion = utils::GetCompiledModuleVersion(static::MODULE_CODE);
		$oP->add_ready_script(
			<<<EOF
	var aSWFilesToLoad = [

		{ url: "{$sAbsUrlModulesRoot}/js/stopwatch_widget.js?v=$sModuleVersion", type: "script" },
		
	];
	var fSWInitCallback = function(){ $('#{$sId}').stopwatch_handler({$sOptionsAsJson}); };
	
	var iSWCurrentIdx = 0;
	var iSWFilesToLoadCount = aSWFilesToLoad.length;
	var fSWLoadScript = function(){
		$.when(
			$.ajax({
				url: aSWFilesToLoad[iSWCurrentIdx].url,
				dataType: aSWFilesToLoad[iSWCurrentIdx].type,
				cache: true
			})
			.done(function(){
				if( aSWFilesToLoad[iSWCurrentIdx] && (aSWFilesToLoad[iSWCurrentIdx].type === 'text') && ($('head link[type="text/css"][href="' + aSWFilesToLoad[iSWCurrentIdx].url + '"]').length === 0) )
				{
					$('<link rel="stylesheet" type="text/css" href="' + aSWFilesToLoad[iSWCurrentIdx].url + '" />').appendTo('head');
				}
			})
		)
		.then(function(){
			iSWCurrentIdx++;
			if(iSWCurrentIdx !== iSWFilesToLoadCount)
			{
				fSWLoadScript();
			}
			else
			{
				fSWInitCallback();
			}
		});
	};
	
	fSWLoadScript();
EOF
		);
	}
}
