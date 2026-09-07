<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

class ActivityColors
{
	const ENUM_COLOR_SCHEME_BASE_ORANGE = 'orange';
	const ENUM_COLOR_SCHEME_BASE_LIME = 'lime';
	const ENUM_COLOR_SCHEME_BASE_CYAN = 'cyan';
	const ENUM_COLOR_SCHEME_BASE_PURPLE = 'purple';
	const ENUM_COLOR_SCHEME_BASE_RED = 'red';
	const ENUM_COLOR_SCHEME_BASE_BLUE = 'blue';
	const ENUM_COLOR_SCHEME_BASE_BLUEGREY = 'bluegrey';
	const ENUM_COLOR_SCHEME_BASE_GREEN = 'green';
	const ENUM_COLOR_SCHEME_BASE_GREY = 'grey';
	const ENUM_COLOR_SCHEME_BASE_RAINBOW = 'rainbow';

	public static function GetColorSchemes()
	{
		return array(
			static::ENUM_COLOR_SCHEME_BASE_ORANGE => array(
				array('text' => '#ffffff', 'background' => '#FB8C00'),
				array('text' => '#ffffff', 'background' => '#FFA726'),
				array('text' => '#ffffff', 'background' => '#FF6F00'),
				array('text' => '#ffffff', 'background' => '#FFCC80'),
				array('text' => '#ffffff', 'background' => '#FFE082'),
				array('text' => '#ffffff', 'background' => '#FFCA28'),
				array('text' => '#ffffff', 'background' => '#FFB300'),
				array('text' => '#ffffff', 'background' => '#FF8F00'),
				array('text' => '#ffffff', 'background' => '#EF6C00'),
			),
			static::ENUM_COLOR_SCHEME_BASE_LIME => array(
				array('text' => '#ffffff', 'background' => '#CDDC39'),
				array('text' => '#ffffff', 'background' => '#DCE775'),
				array('text' => '#ffffff', 'background' => '#C5E1A5'),
				array('text' => '#ffffff', 'background' => '#9CCC65'),
				array('text' => '#ffffff', 'background' => '#7CB342'),
				array('text' => '#ffffff', 'background' => '#558B2F'),
				array('text' => '#ffffff', 'background' => '#33691E'),
				array('text' => '#ffffff', 'background' => '#9E9D24'),
			),
			static::ENUM_COLOR_SCHEME_BASE_PURPLE => array(
				array('text' => '#ffffff', 'background' => '#c62994'),
				array('text' => '#ffffff', 'background' => '#d673bd'),
				array('text' => '#ffffff', 'background' => '#e596cd'),
				array('text' => '#ffffff', 'background' => '#f6c8e9'),
				array('text' => '#ffffff', 'background' => '#b584dd'),
				array('text' => '#ffffff', 'background' => '#946cb5'),
				array('text' => '#ffffff', 'background' => '#63298c'),
				array('text' => '#ffffff', 'background' => '#731c53'),
				array('text' => '#ffffff', 'background' => '#9c2973'),
			),
			static::ENUM_COLOR_SCHEME_BASE_CYAN => array(
				array('text' => '#ffffff', 'background' => '#00ACC1'),
				array('text' => '#ffffff', 'background' => '#26C6DA'),
				array('text' => '#ffffff', 'background' => '#80DEEA'),
				array('text' => '#ffffff', 'background' => '#B2EBF2'),
				array('text' => '#ffffff', 'background' => '#81D4FA'),
				array('text' => '#ffffff', 'background' => '#29B6F6'),
				array('text' => '#ffffff', 'background' => '#039BE5'),
				array('text' => '#ffffff', 'background' => '#0277BD'),
				array('text' => '#ffffff', 'background' => '#00838F'),
			),
			static::ENUM_COLOR_SCHEME_BASE_RED => array(
				array('text' => '#ffffff', 'background' => '#ef2108'),
				array('text' => '#ffffff', 'background' => '#ef6352'),
				array('text' => '#ffffff', 'background' => '#ef948c'),
				array('text' => '#ffffff', 'background' => '#f1cdc6'),
				array('text' => '#ffffff', 'background' => '#f7a57b'),
				array('text' => '#ffffff', 'background' => '#f77d3b'),
				array('text' => '#ffffff', 'background' => '#f75a08'),
				array('text' => '#ffffff', 'background' => '#b94c1f'),
				array('text' => '#ffffff', 'background' => '#8d3c16'),
			),
			static::ENUM_COLOR_SCHEME_BASE_BLUE => array(
				array('text' => '#ffffff', 'background' => '#1A237E'),
				array('text' => '#ffffff', 'background' => '#283593'),
				array('text' => '#ffffff', 'background' => '#3949AB'),
				array('text' => '#ffffff', 'background' => '#5C6BC0'),
				array('text' => '#ffffff', 'background' => '#9FA8DA'),
				array('text' => '#ffffff', 'background' => '#C5CAE9'),
				array('text' => '#ffffff', 'background' => '#90CAF9'),
				array('text' => '#ffffff', 'background' => '#42A5F5'),
				array('text' => '#ffffff', 'background' => '#1E88E5'),
			),
			static::ENUM_COLOR_SCHEME_BASE_BLUEGREY => array(
				array('text' => '#ffffff', 'background' => '#90A4AE'),
				array('text' => '#ffffff', 'background' => '#546E7A'),
				array('text' => '#ffffff', 'background' => '#78909C'),
				array('text' => '#ffffff', 'background' => '#607D8B'),
				array('text' => '#ffffff', 'background' => '#B0BEC5'),
				array('text' => '#ffffff', 'background' => '#455A64'),
				array('text' => '#ffffff', 'background' => '#CFD8DC'),
				array('text' => '#ffffff', 'background' => '#37474F'),
				array('text' => '#ffffff', 'background' => '#263238'),
			),
			static::ENUM_COLOR_SCHEME_BASE_GREEN => array(
				array('text' => '#ffffff', 'background' => '#388E3C'),
				array('text' => '#ffffff', 'background' => '#4CAF50'),
				array('text' => '#ffffff', 'background' => '#81C784'),
				array('text' => '#ffffff', 'background' => '#C8E6C9'),
				array('text' => '#ffffff', 'background' => '#1B5E20'),
				array('text' => '#ffffff', 'background' => '#33691E'),
			),
			static::ENUM_COLOR_SCHEME_BASE_GREY => array(
				array('text' => '#ffffff', 'background' => '#535353'),
				array('text' => '#ffffff', 'background' => '#757575'),
				array('text' => '#ffffff', 'background' => '#a6a6a6'),
				array('text' => '#ffffff', 'background' => '#c2c2c2'),
				array('text' => '#ffffff', 'background' => '#dddddd'),
				array('text' => '#ffffff', 'background' => '#f4f4f4'),
			),
			static::ENUM_COLOR_SCHEME_BASE_RAINBOW => array(
				array('text' => '#ffffff', 'background' => '#FFA726'),
				array('text' => '#ffffff', 'background' => '#FFEE58'),
				array('text' => '#ffffff', 'background' => '#D4E157'),
				array('text' => '#ffffff', 'background' => '#66BB6A'),
				array('text' => '#ffffff', 'background' => '#26A69A'),
				array('text' => '#ffffff', 'background' => '#26C6DA'),
				array('text' => '#ffffff', 'background' => '#42A5F5'),
				array('text' => '#ffffff', 'background' => '#5C6BC0'),
				array('text' => '#ffffff', 'background' => '#AB47BC'),
				array('text' => '#ffffff', 'background' => '#EC407A'),
				array('text' => '#ffffff', 'background' => '#ef5350'),
				array('text' => '#ffffff', 'background' => '#FF7043'),
			),
		);
	}
	public static function GetShadeOf($sColorName = 'green')
	{
		$aColors = self::GetColorSchemes();
		return $aColors[$sColorName][rand(0, count($aColors[$sColorName]) - 1)];
	}
	public static function GetRandomShade()
	{
		$aColors = array_values(self::GetColorSchemes());
		$iColorKey = rand(0, count($aColors) - 1);
		return $aColors[$iColorKey][rand(0, count($aColors[$iColorKey]) - 1)];
	}
	public static function GetColor($sColor, $sMode = 'background')
	{
		if(preg_match('/^#.*$/', $sColor))
		{
			return $sColor;
		}
		else if(preg_match('/^shadeof:(.*)$/', $sColor))
		{
			return self::GetShadeOf(substr($sColor, 8))[$sMode];
		}
		return self::GetRandomShade()[$sMode];
	}
}
