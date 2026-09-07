<?php
/**
 * Module itop-time-tracking
 *
 * @copyright   Copyright (C) 2012-2019 Combodo SARL
 * @copyright   Copyright (C) 2012-2019 ITOMIG GmbH
 * @license     https://www.combodo.com/documentation/combodo-software-license.html
 */

Dict::Add('DE DE', 'German', 'Deutsch', array(
	'TimeTracking:TitlePage' => 'Zeiterfassung',
	'TimeTracking:TrackingTimeOn' => 'Zeiterfassung für',
	'TimeTracking:PressButtonToStartTrackingOn' => 'Play drücken um Zeiterfassung zu starten',
	'TimeTracking:TrackingTimeOnDiffObj' => 'Zeiterfassung für ein anderes Objekt',
	'TimeTracking:TrackingTimeStopwatchForcedStop' => 'Halt der Stopuhr erzwungen',
	'TimeTracking:Category:Favorite' => 'Favorit',
	'TimeTracking:Category:All' => 'Alle',
	'TimeTracking:TabLabel' => 'Meine Zeit erfassen',
	'TimeTracking:ReportTabLabel' => 'Zeiterfassungsbericht',
	'TrackingTime:AddTimeSpent' => 'Zeiterfassungseintrag hinzufügen',
	'TimeTracking:ClassicMode' => 'Klassischer Modus',
	'TimeTracking:ModalMode' => 'Modaler Modus',
	'TimeTracking::TrackedFrom:calendar' => 'Erfasst im Kalender',
	'TimeTracking::TrackedFrom:stopwatch' => 'Erfasst per Stopuhr',
	'TimeTracking::TrackedFrom:manual' => 'Manuell erfasst',

	'TimeTracking:SelectColorForActivity' => 'Aktivitätsfarbe',
	'TimeTracking:SetColor' => 'Anwenden',
	'TimeTacking:Stopwatch:Start' => 'Start',
	'TimeTacking:Stopwatch:Stop' => 'Stop',
	'TimeTacking:Stopwatch:Reset' => 'Zurücksetzen',
	'TimeTracking:SelectActivity:Placeholder' => 'Aktivität wählen',
	'TimeSpent:Edition' => 'Edition',
	'TimeSpent:Information' => 'Weitere Informationen',

	'TimeTracking:DayOfWeek-0' => 'So',
	'TimeTracking:DayOfWeek-1' => 'Mo',
	'TimeTracking:DayOfWeek-2' => 'Di',
	'TimeTracking:DayOfWeek-3' => 'Mi',
	'TimeTracking:DayOfWeek-4' => 'Do',
	'TimeTracking:DayOfWeek-5' => 'Fr',
	'TimeTracking:DayOfWeek-6' => 'Sa',

	'TimeTracking:ReportActivityPerUser' => 'Kumulierte Aktivitäten pro Benutzer (in Stunden)',
	'TimeTracking:ReportActivityPerCustomer' => 'Kumulierte Aktivitäten pro Kunden (in Stunden)',

	'TimeTracking:Title:MyActivities' => 'Mein Zeiterfassungsbericht',
	'TimeTracking:Title:Report' => 'Zeiterfassungsbericht',
	'TimeTracking:Button:Close' => 'Schließen',
	'TimeTracking:Today' => 'Heute',
	'TimeTracking:Month' => 'Monat',
	'TimeTracking:Week' => 'Woche',
	'TimeTracking:Day' => 'Tag',
	'TimeTracking:Total_Duration' => 'Dauer insgesamt: %1$s',
	'TimeTracking:Daily' => 'Täglicher Bericht',
	'TimeTracking:Weekly' => 'Wöchentlicher Bericht',
	'TimeTracking:Monthly' => 'Monatlicher Bericht',
	'TimeTracking:DailyNext' => ' Nächster Tag ► ',
	'TimeTracking:DailyPrev' => ' ◄ Vorh. Tag ',
	'TimeTracking:WeeklyNext' => ' Nächste Woche ► ',
	'TimeTracking:WeeklyPrev' => ' ◄ Vorh. Woche ',
	'TimeTracking:MonthlyNext' => ' Next month ► ',
	'TimeTracking:MonthlyPrev' => ' ◄ Vorh. Monat ',
	'TimeTracking:Report_From_To' => 'Von %1$s bis %2$s',
	'TimeTracking:DateRangeFormat' => 'd/m',
	'TimeTracking:DayFormat' => 'd/m/Y',
	'TimeTracking:MoreCriteria' => 'Mehr Kriterien',
	'TimeTracking:LessCriteria' => 'Weniger Kriterien',
	'TimeTracking:ReportOverview:Label' => 'Time tracking overview~~',
	'TimeTracking:ReportOverview:ContactHeader' => 'Contact',
	'TimeTracking:ReportOverview:DurationHeader' => 'Duration',
	'TimeTracking:ReportOverview:RequiredDurationHeader' => 'Required duration',
	'TimeTracking:TimeSpentReport:Label' => 'Time spent report~~',
	
	'TimeTracking:Error:Generic' => 'Es ist etwas schief gegangen',
	'TimeTracking:Error:WrongActivity' => 'Aktion konnte für diese Aktivität nicht ausgeführt werden',
	'TimeTracking:Error:DeleteExpired' => 'Dieses Ereignis kann nicht gelöscht werden weil es zu alt ist',
	'TimeTracking:Error:DeleteArchived' => 'Dieses Ereignis kann nicht gelöscht werden, weil es bereits archiviert wurde',
	'TimeTracking:Error:DeleteRights' => 'Berechtigungen zum löschen des Ereignisses nicht ausreichend',
	'TimeTracking:Error:UpdateExpired' => 'Dieses Ereignis kann aktualisiert werden, weil es zu alt ist',
	'TimeTracking:Error:UpdateRights' => 'Berechtigungen zum aktualisieren des Ereignisses nicht ausreichend',

	'Menu:TimeTracking' => 'Zeiterfassung',
	'Menu:TimeTrackingPage' => 'Meine Zeiten erfassen',
	'Menu:MyTimeTrackingReport' => 'Mein Zeiterfassungsbericht',
	'Menu:TimeTrackingReport' => 'Zeiterfassungsbericht',

	'Class:Activity' => 'Aktivität',
	'Class:Activity/Attribute:background_color' => 'Hintergrundfarbe',
	'Class:Activity/Attribute:text_color' => 'Textfarbe',
	'Class:Activity/Attribute:obj_class' => 'Objektklasse',
	'Class:Activity/Attribute:obj_id' => 'Objekt-ID',
	'Class:Activity/Attribute:label' => 'Label',


	'Class:TimeSpent' => 'Zeitbedarf',
	'Class:TimeSpent/Attribute:activity_id' => 'Aktivität',
	'Class:TimeSpent/Attribute:activity_label' => 'Aktivitätslabel',
	'Class:TimeSpent/Attribute:contact_id' => 'Kontakt',
	'Class:TimeSpent/Attribute:contact_id_finalclass_recall' => 'Kontaktklasse',
	'Class:TimeSpent/Attribute:duration' => 'Dauer',
	'Class:TimeSpent/Attribute:description' => 'Beschreibung',
	'Class:TimeSpent/Attribute:end_date' => 'Enddatum',
	'Class:TimeSpent/Attribute:org_id' => 'Organisation',
	'Class:TimeSpent/Attribute:origin' => 'Herkunft',
	'Class:TimeSpent/Attribute:origin/Value:calendar' => 'Kalender',
	'Class:TimeSpent/Attribute:origin/Value:stopwatch' => 'Stopuhr',
	'Class:TimeSpent/Attribute:origin/Value:manual' => 'Manuell',

	'Class:TimeSpent/Attribute:start_date' => 'Start Datum',
	'Class:TimeSpent/Attribute:title' => 'Titel',
	'Class:TimeSpent/Attribute:user_id' => 'Benutzer',
	'Class:TimeSpent/Attribute:user_id_finalclass_recall' => 'Benutzerklasse',

	'Class:TimeSpentBackground/Name' => '%1$s %2$s',
	'Class:TimeSpentBackground' => 'Zeitbedarf (aus Ticketstopuhr)',
	'Class:TimeSpentBackground/Attribute:status' => 'Status',


	'Class:FavouriteActivity' => 'Lieblingsaktivität',
	'Class:FavouriteActivity/Attribute:activity_id' => 'Aktivität',
	'Class:FavouriteActivity/Attribute:user_id' => 'Benutzer',
	'Class:FavouriteActivity/Attribute:user_id_finalclass_recall' => 'Benutzerklasse',

	'Class:UserColorActivity' => 'Farbe für Benutzeraktivitäten',
	'Class:UserColorActivity/Attribute:background_color' => 'Hintergrundfarbe',
	'Class:UserColorActivity/Attribute:text_color' => 'Textfarbe',
	'Class:UserColorActivity/Attribute:activity_id' => 'Aktivität',
	'Class:UserColorActivity/Attribute:user_id' => 'Benutzer',

	'Class:TriggerOnForceStopTimeSpentBackground' => 'Trigger (when a time tracking stopwatch times out)~~',
	'Class:TriggerOnForceStopTimeSpentBackground+' => 'Trigger activated when a time tracking stopwatch is force stopped by cron~~',
));

