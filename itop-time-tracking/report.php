<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

try
{
	require_once(APPROOT.'/application/application.inc.php');
	require_once(APPROOT.'/application/itopwebpage.class.inc.php');


	require_once(APPROOT.'/application/startup.inc.php');
	$oAppContext = new ApplicationContext();

	require_once(APPROOT.'/application/loginwebpage.class.inc.php');
	LoginWebPage::DoLogin(); // Check user rights and prompt if needed

	$sMode = utils::ReadParam('mode', 'report');
	$oUser = UserRights::GetUserObject();
	
	if($sMode != 'self' && !TimeTrackingRightsPlugin::HasAllReportDisplayRights($oUser))
	{
		$sMode = 'self';
	}
	
	if($sMode == 'self')
	{
		$sTitle = Dict::S('TimeTracking:Title:MyActivities');
	}
	else
	{
		$sTitle = Dict::S('TimeTracking:Title:Report');
	}
	$oPage = new iTopWebPage($sTitle);
	$oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/'.TimeTrackingView::MODULE_CODE."/css/report.scss");
	if(TimeTrackingView::UseLegacy()){
		$oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/'.TimeTrackingView::MODULE_CODE."/legacy/css/report.scss");
	}
	
	$sReport = utils::ReadParam('report', 'weekly');
	$sDate = utils::ReadParam('report_date', date('Y-m-d'));

	switch($sReport)
	{
		case 'monthly':
			$oStartDate = new DateTime($sDate);
			// First day of the month
			$oStartDate->modify('-'.((int)$oStartDate->format('j') -1).' day');
			$oEndDate = clone($oStartDate);
			$oEndDate->modify('+1 month');

			$oNextDate = clone $oStartDate;
			$oNextDate->modify('+1 month');
			$sNextDate = $oNextDate->format('Y-m-d');
			$oPrevDate = clone $oStartDate;
			$oPrevDate->modify('-1 month');
			$sPrevDate = $oPrevDate->format('Y-m-d');
			break;

		case 'weekly':
			$oStartDate = new DateTime($sDate);
			$oStartDate->modify('-'.$oStartDate->format('w').' day');
			// week starts on Mondays
			//TODO make this configurable
			$oStartDate->modify('+1 day');
			$oEndDate = clone $oStartDate;
			$oEndDate->modify('+1 week');

			$oNextDate = clone $oStartDate;
			$oNextDate->modify('+1 week');
			$sNextDate = $oNextDate->format('Y-m-d');
			$oPrevDate = clone $oStartDate;
			$oPrevDate->modify('-1 week');
			$sPrevDate = $oPrevDate->format('Y-m-d');
			break;

		case 'daily':
			$oStartDate = new DateTime($sDate);
			$oEndDate = clone $oStartDate;
			$oEndDate->modify('+1 day');

			$oNextDate = new Datetime($sDate);
			$oNextDate->modify('+1 day');
			$sNextDate = $oNextDate->format('Y-m-d');
			$oPrevDate = new Datetime($sDate);
			$oPrevDate->modify('-1 day');
			$sPrevDate = $oPrevDate->format('Y-m-d');
			break;
	}

	$aQueryParams = array('contact_id' => UserRights::GetContactId(), 'start_date' => $oStartDate->format('Y-m-d') , 'end_date' => $oEndDate->format('Y-m-d'));

	$sNextLabel = Dict::S('TimeTracking:'.ucfirst($sReport).'Next');
	$sPrevLabel = Dict::S('TimeTracking:'.ucfirst($sReport).'Prev');

	$oDisplayEnd = clone $oEndDate;
	$oDisplayEnd->modify('-1 day');
	if ($oStartDate->format('Y-m-d') == $oDisplayEnd->format('Y-m-d'))
	{
		$sLabel = $oStartDate->format(Dict::S('TimeTracking:DayFormat'));
	}
	else
	{
		// range
		$sLabel = Dict::Format('TimeTracking:Report_From_To', $oStartDate->format(Dict::S('TimeTracking:DateRangeFormat')), $oDisplayEnd->format(Dict::S('TimeTracking:DateRangeFormat')));
	}
	if(TimeTrackingView::UseLegacy()){
		$oPage->add("<h1>$sTitle</h1>");
	}
	$oPage->add('<div id="cal_nav" class="ibo-panel ibo-is-cyan ibo-panel ibo-content-block ibo-block ibo-search-form-panel ibo-is-opened">');
	if(!TimeTrackingView::UseLegacy()){
		$oPage->add("<div class='ibo-panel--title'>$sTitle</div>");
	}
	$oPage->add('<div class="ibo-panel--body">');
	$oPage->add('<div style="float:left; margin-left:1em;">');
	$oPage->add('<button type="button" class="ibo-button ibo-is-regular" onclick="$(\'#cur_date\').val(\''.$sPrevDate.'\'); $(\'#fs_0\').closest(\'form\').submit();">'.htmlentities($sPrevLabel, ENT_QUOTES, 'UTF-8').'</button>');
	$oPage->add('<button type="button" class="ibo-button ibo-is-regular" onclick="$(\'#cur_date\').val(\''.$sNextDate.'\'); $(\'#fs_0\').closest(\'form\').submit();">'.htmlentities($sNextLabel, ENT_QUOTES, 'UTF-8').'</button>');
	$oPage->add('</div>');

	$oPage->add('<div class="ibo-input-select-wrapper" style="float:right;margin-right:1em;">');
	$oPage->add('<select class="ibo-input-select ibo-input" name="" onchange="$(\'#report\').val($(this).val()); $(\'#fs_0\').submit();">');
	foreach(array('monthly' => Dict::S('TimeTracking:Monthly'), 'weekly' => Dict::S('TimeTracking:Weekly'), 'daily' => Dict::S('TimeTracking:Daily'))  as $sKey => $sDisplay)
	{
		$sSelected = '';
		if ($sReport == $sKey)
		{
			$sSelected = ' selected';
		}
		$oPage->add('<option value="'.$sKey.'"'.$sSelected.'>'.$sDisplay.'</option>');
	}
	$oPage->add('</select>');
	$oPage->add('</div>');
	$oPage->add('<h2>'.$sLabel.'</h2>');
	$oPage->add('<div style="clear:both;"></div></div></div>');
	$oPage->add('<div id="cal_nav_form">');
	$oPage->add('<input type="hidden" name="mode" value="'.$sMode.'"/>');
	$oPage->add('<input type="hidden" name="exec_module" value="'.TimeTrackingView::MODULE_CODE.'"/>');
	$oPage->add('<input type="hidden" name="exec_page" value="report.php"/>');
	$oPage->add('<input type="hidden" name="exec_env" value="'.utils::GetCurrentEnvironment().'"/>'); //TODO: de-hardcode
	$oPage->add('<input type="hidden" name="report" id="report" value="'.$sReport.'"/>');
	$oAppContext = new ApplicationContext();
	$oPage->add($oAppContext->GetForForm());
	$oPage->add('<input type="hidden" id="cur_date" name="report_date" value="'.$sDate.'"/>');
	$oPage->add('<input type="hidden" id="search_tab" name="search_tab" value="0"/>');
	$oPage->add('</div>');

	$sActionUrl = ''; //../pages/exec.php'; // The following DOES NOT work since it gets mixed up with GET parameter from the form: utils::GetAbsoluteUrlModulePage('itop-time-tracking', 'report.php');
	if ($sMode == 'self')
	{
		$sOQLDefaultValue = 'SELECT TimeSpent WHERE contact_id = :contact_id AND start_date >= :start_date AND end_date < :end_date';
		$sOQL = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'default_report_query', $sOQLDefaultValue);
		$oSearch = DBObjectSearch::FromOQL($sOQL);
		$oPage->add('<div style="margin-left:3px;margin-right:3px;"><div class="HRDrawer"></div></div>');
		$oPage->add('<form id="fs_0" method="post" action="'.$sActionUrl.'"></form>');
	}
	else
	{
		$sOQLDefaultValue = 'SELECT TimeSpent WHERE start_date >= :start_date AND end_date < :end_date';
		$sOQL = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'manager_report_query', $sOQLDefaultValue);

		$oSearch = DBObjectSearch::FromOQL($sOQL);

		$aFilterCodes = array_keys(MetaModel::GetClassFilterDefs($oSearch->GetClass()));
		foreach($aFilterCodes as $sFilterCode)
		{
			$externalFilterValue = utils::ReadParam($sFilterCode, '', false, 'raw_data');
			$condition = null;
			$bParseSearchString = true;
			if ($externalFilterValue != '')
			{
				// Search takes precedence over context params...
				$bParseSearchString = true;
				unset($aQueryParams[$sFilterCode]);
				if (!is_array($externalFilterValue))
				{
					$condition = trim($externalFilterValue);
				}
				else if (count($externalFilterValue) == 1)
				{
					$condition = trim($externalFilterValue[0]);
				}
				else
				{
					$condition = $externalFilterValue;
				}
			}
	
			if (!is_null($condition))
			{
				$sOpCode = null; // default operator
				if (is_array($condition))
				{
					// Multiple values, add them as AND X IN (v1, v2, v3...)
					$sOpCode = 'IN';
				}

				$oSearch->AddCondition($sFilterCode, $condition, $sOpCode, $bParseSearchString);
			}
		}
		
		
		$oBlock = new LegacySearchBlock($oSearch, array('query_params' => $aQueryParams, 'action' => $sActionUrl, 'open' => false));
		$oBlock->Display($oPage, 0);

		$sMoreCriteria = Dict::S('TimeTracking:MoreCriteria');
		$sLessCriteria = Dict::S('TimeTracking:LessCriteria');

		$bSearchTabOpen = utils::ReadParam('search_tab', false);
		$sSearchTabJS = '';
		if($bSearchTabOpen)
		{
			$sSearchTabJS = "$('#dh_0').trigger('click');\n";
		}

		$oPage->add_ready_script(
			<<<EOF
$('#ds_0 h2').remove();
$('#dh_0').html('$sMoreCriteria').unbind('click').bind('click', function() {
		$('#ds_0').slideToggle('normal', function() { $('#dh_0').parent().resize(); } );
		$(this).toggleClass('open');
		if ($(this).hasClass('open'))
		{
			$('#search_tab').val(1);
			$(this).html('$sLessCriteria');
		}
		else
		{
			$('#search_tab').val(0);
			$(this).html('$sMoreCriteria');
		}
	});
	$sSearchTabJS
EOF
		);
		
		// Display overview

		$oOverviewTimeSpentSearch = $oSearch;
		$bIsOverviewContactFiltered = true;
		$sContactSearchParam = utils::ReadParam('contact_id', '', false, 'raw_data');

		$sOverviewContactOQL = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'manager_report_silo', '');
		if (empty($sOverviewContactOQL))
		{
			$bIsOverviewContactFiltered = false;
			$sOverviewContactOQL = 'SELECT Contact';
		}
		$aOverviewContactParams = array('contact_id' => UserRights::GetContactId(), 'start_date' => $oStartDate->format('Y-m-d') , 'end_date' => $oEndDate->format('Y-m-d'));
		$aOverviewContactIds = array();


		// Fill contact ids 
		$oOverviewContactSearch = DBObjectSearch::FromOQL($sOverviewContactOQL);
		$oOverviewContactSearch->SetShowObsoleteData(utils::ShowObsoleteData());
		$oOverviewContactSet = new DBObjectSet($oOverviewContactSearch, array(), $aOverviewContactParams);
		while ($oOverviewContact = $oOverviewContactSet->Fetch())
		{
			$sOverviewContactId = $oOverviewContact->GetKey();
			// If we have a contact search via form, and our matched contact is : -
			// - In the array (multiple contact searched)
			// - Equal to the search contact (single contact searched)
			if((!empty($sContactSearchParam) && ((is_array($sContactSearchParam) && in_array($sOverviewContactId, $sContactSearchParam)) || $sOverviewContactId == $sContactSearchParam) )|| empty($sContactSearchParam))
			{
				$aOverviewContactIds[] = $sOverviewContactId;
			}
		}

		// And add them to time spent search if necessary
		if ($bIsOverviewContactFiltered && !empty($aOverviewContactIds))
		{
			$oOverviewTimeSpentSearch->AddCondition('contact_id', $aOverviewContactIds, 'IN');
			$oOverviewTimeSpentSearch->SetShowObsoleteData(utils::ShowObsoleteData());
		}
		


		$oOverviewTimeSpentSet = new DBObjectSet($oOverviewTimeSpentSearch, array(), $aQueryParams);
		$aTimeSpentGroupedByContact = array_fill_keys($aOverviewContactIds, 0);

		// For each timespent group them by their contact id and adds their duration
		while ($oOverviewTimeSpent = $oOverviewTimeSpentSet->Fetch()){
			$oOverviewTimeSpentContactId = $oOverviewTimeSpent->Get('contact_id');
			$oOverviewTimeSpentContactDuration = $oOverviewTimeSpent->Get('duration');
			if (!empty($oOverviewTimeSpentContactId) && in_array($oOverviewTimeSpentContactId, $aOverviewContactIds) && !empty($oOverviewTimeSpentContactDuration))
			{
				$aTimeSpentGroupedByContact[$oOverviewTimeSpentContactId] += $oOverviewTimeSpentContactDuration;
			}
		}

		$bIsWeeklyReportTSColumnShown = true;
		$sWeeklyReportTSAttr = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'weekly_report_time_spent_attribute', '');
		$sWeeklyReportTSDefault = utils::GetConfig()->GetModuleSetting(TimeTrackingView::MODULE_CODE, 'weekly_report_time_spent_default', '');

		if(empty($sWeeklyReportTSAttr) && empty($sWeeklyReportTSDefault))
		{
				$bIsWeeklyReportTSColumnShown = false;
		}

		$aOverviewData = array();
		foreach ($aTimeSpentGroupedByContact as $iContactId => $iTimeSpentGroupedByContactDuration)
		{
			$oContact = MetaModel::GetObject('Contact', $iContactId);
			if($oContact !== null)
			{
				$sContactFriendlyname = $oContact->Get('friendlyname');

				$sContactDuration = AttributeDuration::SplitDuration($iTimeSpentGroupedByContactDuration);
				$sContactDuration = Dict::Format('Core:Duration_Hours_Minutes_Seconds', ($sContactDuration['days'] * 24) + $sContactDuration['hours'], $sContactDuration['minutes'], $sContactDuration['seconds']);

				$aRow = array('contact_id' => $iContactId, 'contact_friendlyname' => $sContactFriendlyname, 'contact_duration' => $sContactDuration);

				$sContactRequiredDuration = null;
				if(!empty($sWeeklyReportTSAttr) && MetaModel::IsValidAttCode(get_class($oContact), $sWeeklyReportTSAttr))
				{
					$sContactRequiredDuration = $oContact->Get($sWeeklyReportTSAttr);
					$aRow['contact_required_duration'] = $sContactRequiredDuration;
				}
				else if (!empty($sWeeklyReportTSDefault))
				{
					$sContactRequiredDuration = Dict::S($sWeeklyReportTSDefault);
					$aRow['contact_required_duration'] = $sContactRequiredDuration;
				}
				else if ($bIsWeeklyReportTSColumnShown)
				{
					$aRow['contact_required_duration'] = '';
				}

				$aOverviewData[] = $aRow;
			}
		}
		$sOverviewTitle = Dict::S('TimeTracking:ReportOverview:Label');
		$sOverviewContactHeader = Dict::S('TimeTracking:ReportOverview:ContactHeader');
		$sOverviewDurationHeader = Dict::S('TimeTracking:ReportOverview:DurationHeader');
		$sOverviewRequiredDurationHeader= Dict::S('TimeTracking:ReportOverview:RequiredDurationHeader');
		
		$oPage->add(
			<<<HTML
			<h2>$sOverviewTitle</h2>
			<div id="tt_timespent_by_contact_overview" class="dataTable">
			<table>
				<thead class="dataTables_scrollHeadInner">
					<tr> 
						<th>$sOverviewContactHeader</th>
						<th>$sOverviewDurationHeader</th>
HTML
		);
		if($bIsWeeklyReportTSColumnShown)
		{
			$oPage->add('<th>'.$sOverviewRequiredDurationHeader.'</th>');
		}
		$oPage->add(
			<<<HTML
					</tr>
				</thead>
				<tbody>
HTML
		);
		foreach ($aOverviewData as $sOverviewRow)
		{
			$sOverviewRowId = $sOverviewRow['contact_id'];
			$sOverviewRowFN = utils::HtmlEntities($sOverviewRow['contact_friendlyname']);
			$sOverviewRowDuration = utils::HtmlEntities($sOverviewRow['contact_duration']);

			$oPage->add(
				<<<HTML
			<tr data-contact-id="$sOverviewRowId">
				<th>$sOverviewRowFN</th>
				<th>$sOverviewRowDuration</th>
HTML
			);
			if($bIsWeeklyReportTSColumnShown)
			{
				$sOverviewRowRequiredDuration = $sOverviewRow['contact_required_duration'];
				$oPage->add('<th>'.$sOverviewRowRequiredDuration.'</th>');
			}
			$oPage->add('</tr>');
		}
		$oPage->add(
			<<<HTML
				</tbody>
	
			</table>
			</div>
HTML
		);
		$oPage->add_ready_script(
			<<<JS
		$('#tt_timespent_by_contact_overview tr[data-contact-id] th:nth-child(1)').on('click',function(){
			var iClickedContactId = $(this).parent('tr').attr('data-contact-id');
			if($('form#fs_0 input[name="contact_id"]')[0])
			{
				$('form#fs_0 input[name="contact_id"]').val(iClickedContactId);
			}
			else
			{
				$("#0search_contact_id").val([iClickedContactId]);
			}
			$('form#fs_0').submit();
		});
JS
);
	}
	$oPage->add_ready_script('var sForm = $(\'#cal_nav_form\').html(); $(\'#cal_nav_form\').remove(); $(\'#fs_0\').append(sForm);');

	// Display the set and compute the total
	$sTSReportTitle = Dict::S('TimeTracking:TimeSpentReport:Label');
	$oPage->add('<h2>'. $sTSReportTitle . '</h2>');
	
	$oBlock = new DisplayBlock($oSearch, 'list', false, array('query_params' => $aQueryParams, 'table_id' => 'MyActivities'));

	$iTotalDuration = 0;

	$oActivitySet = new DBObjectSet($oSearch, array(), $aQueryParams);
	while($oActivity = $oActivitySet->Fetch())
	{
		$iTotalDuration += $oActivity->Get('duration');
	}
	$aDuration = AttributeDuration::SplitDuration($iTotalDuration);
	$sDuration = Dict::Format('Core:Duration_Hours_Minutes_Seconds', ($aDuration['days'] * 24) + $aDuration['hours'], $aDuration['minutes'], $aDuration['seconds']);
	$oPage->add('<div class="tt_total_duration" style="margin-top: 20px;">'.Dict::Format('TimeTracking:Total_Duration', $sDuration.'</div>'));
	$oBlock->Display($oPage, 1);

	// Charts
	require('report_charts.php');

	$oPage->output();
}
catch(Exception $e)
{
	require_once(APPROOT.'/setup/setuppage.class.inc.php');
	$oPage = new SetupPage(Dict::S('UI:PageTitle:FatalError'));
	$oPage->add("<h1>".Dict::S('UI:FatalErrorMessage')."</h1>\n");
	$oPage->error(Dict::Format('UI:Error_Details', $e->getMessage()));
	$oPage->output();

	if (MetaModel::IsLogEnabledIssue())
	{
		if (MetaModel::IsValidClass('EventIssue'))
		{
			$oLog = new EventIssue();

			$oLog->Set('message', $e->getMessage());
			$oLog->Set('userinfo', '');
			$oLog->Set('issue', 'PHP Exception');
			$oLog->Set('impact', 'Page could not be displayed');
			$oLog->Set('callstack', $e->getTrace());
			$oLog->Set('data', array());
			$oLog->DBInsertNoReload();
		}

		IssueLog::Error($e->getMessage());
	}
}	