<?php

class DashletBarChart extends Dashlet
{
	public function __construct($oModelReflection, $sId)
	{
		parent::__construct($oModelReflection, $sId);
		$this->aProperties['title'] = '';
		$this->aProperties['query'] = 'SELECT TimeSpent';
		$this->aProperties['time_span'] = 'week';
		$this->aProperties['legend'] = false;
		$this->aProperties['data_group_by'] = 'start_date';
		$this->aProperties['second_group_by'] = 'contact_id';
	}
	
	public function Render($oPage, $bEditMode = false, $aExtraParams = array())
	{
		$sTitle = $this->aProperties['title'];
		$sQuery = $this->aProperties['query'];
		$sTimeSpan = $this->aProperties['time_span'];
		$sShowMenu = $this->aProperties['legend'] ? '1' : '0';

		$oPage->add('<div class="dashlet-content">');
		$sJsonTitle = json_encode(htmlentities(Dict::S($sTitle), ENT_QUOTES, 'UTF-8'));
		
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/raphael.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.raphael.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.line.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.bar.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/barchart.js");
		
		$sUniqueId  = 'dashlet_bar_chart_'.$this->GetID().'_'.$bEditMode;
		$oPage->add('<div id="'.$sUniqueId.'"></div>');
		$oPage->add_ready_script(
<<<EOF
$('#$sUniqueId').barchart({
	y_axis: {min: 0, max: 'auto', step: 'auto'},
	x_axis: {labels: ['One', 'Two', 'Three'] },
	style: 'none',
	type: 'square',
	chart_title: { text: $sJsonTitle },
	series: [
		{
			color: '#995555',
			data: [{value: 10, label: 'X'}, {value: 5, label: 'V'}, { value: 8, label: 'VIII' }]
		},
		{
			color: '#559955',
			data: [{value: 3, label: 'III'}, {value: 4, label: 'IV'}, { value: 1, label: 'I' }]
		}
	]
});		
EOF
	);
		$oPage->add('</div>');
	}

	public function RenderNoData($oPage, $bEditMode = false, $aExtraParams = array())
	{
		$sTitle = $this->aProperties['title'];
		$sQuery = $this->aProperties['query'];
		$bShowMenu = $this->aProperties['menu'];

		$oPage->add('<div class="dashlet-content">');
		$sJsonTitle = json_encode(htmlentities($this->oModelReflection->DictString($sTitle), ENT_QUOTES, 'UTF-8'));
	
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/raphael.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.raphael.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.line.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.bar.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/barchart.js");
		
		$sUniqueId  = 'dashlet_bar_chart_'.$this->GetID().'_'.$bEditMode;
		$oPage->add('<div id="'.$sUniqueId.'"></div>');
		$oPage->add_ready_script(
<<<EOF
$('#$sUniqueId').barchart({
	y_axis: {min: 0, max: 'auto', step: 'auto'},
	x_axis: {labels: ['One', 'Two', 'Three'] },
	style: 'none',
	type: 'square',
	chart_title: { text: $sJsonTitle },
	series: [
		{
			color: '#995555',
			data: [{value: 10, label: 'X'}, {value: 5, label: 'V'}, { value: 8, label: 'VIII' }]
		},
		{
			color: '#559955',
			data: [{value: 3, label: 'III'}, {value: 4, label: 'IV'}, { value: 1, label: 'I' }]
		}
	]
});		
EOF
		);
		$oPage->add('</div>');
	}

	public function GetPropertiesFields(DesignerForm $oForm)
	{
		$oField = new DesignerTextField('title', Dict::S('UI:DashletBarChart:Prop-Title'), $this->aProperties['title']);
		$oForm->AddField($oField);

		$oField = new DesignerLongTextField('query', Dict::S('UI:DashletBarChart:Prop-Query'), $this->aProperties['query']);
		$oField->SetMandatory();
		$oForm->AddField($oField);
		
		$oField = new DesignerComboField('legend', Dict::S('UI:DashletBarChart:Prop-TimeSpan'), $this->aProperties['time_span']);
		$aAllowedValues = array('week' => Dict::S('UI:DashletBarChart:Prop-TimeSpan:Week'), 'month' => Dict::S('UI:DashletBarChart:Prop-TimeSpan:Month'));
		$oField->SetMandatory();
		$oField->SetAllowedValues($aAllowedValues);
		$oForm->AddField($oField);
	
		$oField = new DesignerComboField('date_group_by', Dict::S('UI:DashletBarChart:Prop-DateGroupBy'), $this->aProperties['date_group_by']);
		$aAllowedValues = $this->GetDateGroupByOptions($this->aProperties['query']);
		$oField->SetMandatory();
		$oField->SetAllowedValues($aAllowedValues);
		$oForm->AddField($oField);
	
		$oField = new DesignerComboField('second_group_by', Dict::S('UI:DashletBarChart:Prop-SecondGroupBy'), $this->aProperties['second_group_by']);
		$aAllowedValues = $this->GetDateGroupByOptions($this->aProperties['query']);
		$oField->SetMandatory();
		$oField->SetAllowedValues($aAllowedValues);
		$oForm->AddField($oField);
	
		$oField = new DesignerBooleanField('legend', Dict::S('UI:DashletBarChart:Prop-Legend'), $this->aProperties['legend']);
		$oField->SetMandatory();
		$oForm->AddField($oField);
	}

	protected function GetDateGroupByOptions($sOql)
	{
		$oQuery = $this->oModelReflection->GetQuery($sOql);
		$sClass = $oQuery->GetClass();
		$aGroupBy = array();
		foreach($this->oModelReflection->ListAttributes($sClass) as $sAttCode => $sAttType)
		{
			if (($sAttType != 'AttributeDate') && ($sAttType != 'AttributeDateTime')) continue;

			$sLabel = $this->oModelReflection->GetLabel($sClass, $sAttCode);
			$aGroupBy[$sAttCode] = $sLabel;
		}
		asort($aGroupBy);
		return $aGroupBy;
	}

	protected function GetSecondGroupByOptions($sOql)
	{
		$oQuery = $this->oModelReflection->GetQuery($sOql);
		$sClass = $oQuery->GetClass();
		$aGroupBy = array();
		foreach($this->oModelReflection->ListAttributes($sClass) as $sAttCode => $sAttType)
		{
			if ($sAttType == 'AttributeLinkedSet') continue;
			if (is_subclass_of($sAttType, 'AttributeLinkedSet')) continue;
			if ($sAttType == 'AttributeFriendlyName') continue;
			if (is_subclass_of($sAttType, 'AttributeFriendlyName')) continue;
			if ($sAttType == 'AttributeExternalField') continue;
			if (is_subclass_of($sAttType, 'AttributeExternalField')) continue;
			if (($sAttType == 'AttributeDate') || ($sAttType == 'AttributeDateTime')) continue;

			$sLabel = $this->oModelReflection->GetLabel($sClass, $sAttCode);
			$aGroupBy[$sAttCode] = $sLabel;
		}
		asort($aGroupBy);
		return $aGroupBy;
	}
		
	public function Update($aValues, $aUpdatedFields)
	{
		if (in_array('query', $aUpdatedFields))
		{
			try
			{
				$sCurrQuery = $aValues['query'];
				$oCurrSearch = $this->oModelReflection->GetQuery($sCurrQuery);
				$sCurrClass = $oCurrSearch->GetClass();
	
				$sPrevQuery = $this->aProperties['query'];
				$oPrevSearch = $this->oModelReflection->GetQuery($sPrevQuery);
				$sPrevClass = $oPrevSearch->GetClass();
	
				if ($sCurrClass != $sPrevClass)
				{
					$this->bFormRedrawNeeded = true;
					// wrong but not necessary - unset($aUpdatedFields['group_by']);
					$this->aProperties['date_group_by'] = '';
					$this->aProperties['second_group_by'] = '';
				}
			}
			catch(Exception $e)
			{
				$this->bFormRedrawNeeded = true;
			}
		}
		$oDashlet = parent::Update($aValues, $aUpdatedFields);
		
		return $oDashlet;
	}
	static public function GetInfo()
	{
		// URL MUST be relative to UrlAppRoot...
		$sIconUrl = substr(utils::GetAbsoluteUrlModulesRoot(), strlen(utils::GetAbsoluteUrlAppRoot())).'itop-time-tracking/images/dashlet-barchart.png';
		return array(
			'label' => Dict::S('UI:DashletBarChart:Label'),
			'icon' => $sIconUrl,
			'description' => Dict::S('UI:DashletBarChart:Description'),
		);
	}
	
	/**
	 * @return bool
	 */
	static public function IsVisible()
	{
		return false;
	}
	
	/**
	 * @inheritdoc
	 */
	static public function CanCreateFromOQL()
	{
		return false;
	}
}


/**
 * Helper class to display a stacked bar chart inside an iTop page
 */
class StackedBarChartView
{
	protected $oSet;
	protected $sType;
	protected $sGroupByDate;
	protected $sSecondGroupBy;
	protected $sQuantity;
	protected $aSeriesData;
	protected $iMaxMonthDays;
	protected $iFirstDayOfWeek;

	/**
	 * constructor of the of the "view" object
	 * @param DBObjectSet $oSet The set of objects to use for the computation
	 * @param string $sType The type of chart, either 'weekly' or 'monthly'
	 * @param string $sGroupByDate The attribute code from which to read the date (for grouping by date)
	 * @param string $sSecondGroupBy The attribute code on which to aggregate the values
	 * @param string $sQuantity The attribute code contaiing the value to summ. Either it's a number of seconds (AttributeDuration) or a string with a time format (hh:mm:ss)
	 * @param int $iFirstDayOfWeek When displaying a weekly chart, which day sould be the first day of the week: 0 => Sunday, 1 => Monday ... 6 =>  Saturday
	 */
	public function __construct(DBObjectSet $oSet, $sType, $sGroupByDate, $sSecondGroupBy, $sQuantity, $iFirstDayOfWeek = 1)
	{
		$this->oSet = $oSet;
		$this->sType = $sType;
		$this->sGroupByDate = $sGroupByDate;
		$this->sSecondGroupBy = $sSecondGroupBy;
		$this->sQuantity = $sQuantity;
		$this->iMaxMonthDays = 0;
		$this->iFirstDayOfWeek = $iFirstDayOfWeek;
	}

	/**
	 * Extract the data from the set and format it as appropriate for building the chart
	 * @return hash
	 */
	protected function GetData()
	{
		$this->oSet->Rewind(); // just in case
		switch($this->sType)
		{
			case 'monthly':
				$sDateFormat = 'j';
				break;

			case 'weekly':
				$sDateFormat = 'w';
				$aRawData = array();
				for($i=0; $i<7; $i++)
				{
					$aRawData[$i] = array();
				}
				break;
		}
		$aSeries = array();
		while($oObj = $this->oSet->Fetch())
		{
			$oDate = new DateTime($oObj->Get($this->sGroupByDate));
			$sRawIdx = $oDate->format($sDateFormat);
			if (($this->sType == 'monthly') && ($this->iMaxMonthDays == 0))
			{
				$this->iMaxMonthDays = (int)$oDate->format('t'); // t => number of days in the month
			}
			$sSeriesIdx = $oObj->Get($this->sSecondGroupBy);
			if (!array_key_exists($sSeriesIdx, $aSeries))
			{
				$aSeries[$sSeriesIdx]['data'] = $this->CreateEmptySeries();
				$aSeries[$sSeriesIdx]['label'] = $this->GetSeriesLabel($oObj);
			}
			$amount = $oObj->Get($this->sQuantity);
			$this->AddToSeries($sRawIdx, $amount, $aSeries[$sSeriesIdx]['data'], $aSeries[$sSeriesIdx]['label']);
		}
		return $aSeries;
	}

	/**
	 * Help function to create an empty series of the appropriate length
	 * @return array
	 */
	protected function CreateEmptySeries()
	{
		$aEmpty = array();
		switch($this->sType)
		{
			case 'monthly':
				for($i=0; $i<$this->iMaxMonthDays; $i++)
				{
					$aEmpty[$i]['value'] = 0;
					$aEmpty[$i]['label'] = '';
				}
				break;

			case 'weekly':
				for($i=0; $i<7; $i++)
				{
					$aEmpty[$i]['value'] = 0;
					$aEmpty[$i]['label'] = '';
				}
				break;
		}

		return $aEmpty;
	}

	/**
	 * Add a point to the given series
	 * @param string $sRawIdx The index of the point in the series 1..31 for months, 0..6 for weeks
	 * @param mixed $amount The amount to add to the given point
	 * @param array $aSeries The series to modify
	 * @param string $sSeriesLabel The label of the series (repeated in the label of each point)
	 */
	protected function AddToSeries($sRawIdx, $amount, &$aSeries, $sSeriesLabel)
	{
		//$amount = _Activity::ConvertToSeconds($amount);

		switch($this->sType)
		{
			case 'monthly':
				$aSeries[(int)$sRawIdx - 1]['value'] += $amount/3600;
				$aSeries[(int)$sRawIdx - 1]['label'] = $sSeriesLabel."\n".$this->GetPointLabel($aSeries[(int)$sRawIdx - 1]['value']);
				break;

			case 'weekly':
				$aSeries[(7 - $this->iFirstDayOfWeek + (int)$sRawIdx) % 7]['value'] += $amount/3600;
				$aSeries[(7 - $this->iFirstDayOfWeek + (int)$sRawIdx) % 7]['label'] = $sSeriesLabel."\n".$this->GetPointLabel($aSeries[(7 - $this->iFirstDayOfWeek + (int)$sRawIdx) % 7]['value']);
				break;
		}
	}

	/**
	 * Helper function to retrieve the label of the series, based on the value of the object
	 * @param DBObject $oObj The object in the set for the given point in the series
	 * @return string
	 */
	protected function GetSeriesLabel(DBObject $oObj)
	{
		$sClass = $this->oSet->GetClass();
		$oAttDef = MetaModel::GetAttributeDef($sClass, $this->sSecondGroupBy);
		$sRet = '';
		if ($oAttDef->IsExternalKey())
		{
			// Special processing for external keys: the name if either the "friendly name" 
			// of the object, or "undefined" if the value is empty 
			if ($oObj->Get($this->sSecondGroupBy) == 0)
			{
				$sRet = Dict::S('UI:UndefinedObject');
			}
			else
			{
				$sRet = $oObj->Get($this->sSecondGroupBy.'_friendlyname');
			}
		}
		else
		{
			$sRet = $oObj->GetEditValue($this->sSecondGroupBy);
		}
		return $sRet;
	}

	/**
	 * Constructs the label of the given point / value
	 * @param int $value
	 * @return string
	 */
	protected function GetPointLabel($value)
	{
		$sLabel = '';
		if ($value != 0)
		{
			$sLabel = AttributeDuration::FormatDuration(3600*$value);
		}
		return $sLabel;
	}

	/**
	 * Display the chart in the given page
	 * @param WebPage $oPage The page into which to display the chart
	 * @param string $sId A unique (in this page)identifier for the chart
	 * @param bool $bLegend Whether or not to display the legend at the right of the chart
	 * @param string $sTitle The title of the chart
	 * @return void
	 */
	public function Display(WebPage $oPage, $sId, $bLegend, $sTitle)
	{
		$aSeries = $this->GetData();
		$aData = array();
		// make sure we have a zero based index
		foreach($aSeries as $aSeriesData)
		{
			$aData[] = $aSeriesData;
		}
		$sJsonData = json_encode($aData);

		//$oPage->add('<pre>'.$sJsonData.'</pre>');

		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/raphael.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.raphael.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.line.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/g.bar.js");
		$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."itop-time-tracking/js/barchart.js");

		$sUniqueId  = 'dashlet_bar_chart_'.$sId;
		$oPage->add('<div id="'.$sUniqueId.'" class="raphael-bar-chart"></div>');
		$sJsonTitle = json_encode($sTitle);

		switch($this->sType)
		{
			case 'weekly':
				$aLabels = array();
				for($i = 0; $i < 7; $i++)
				{
					$aLabels[] = Dict::S('TimeTracking:DayOfWeek-'.(($this->iFirstDayOfWeek + $i) % 7));
				}
				$sJsonLabels = json_encode($aLabels);
				break;

			case 'monthly':
				$aDays = array();
				for($i = 1; $i <= $this->iMaxMonthDays; $i++)
				{
					$aDays[] = $i;
				}
				$sJsonLabels = json_encode($aDays);
				break;
		}

		$oPage->add_ready_script(
			<<<EOF
$('#$sUniqueId').barchart({
	y_axis: {min: 0, max: 'auto', step: 'auto'},
	x_axis: {labels: $sJsonLabels },
	style: 'stacked',
	type: 'square',
	chart_title: { text: $sJsonTitle },
	series: $sJsonData
});		
EOF
		);
	}
}
