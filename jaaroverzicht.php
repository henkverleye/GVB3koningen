<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	if (function_exists("date_default_timezone_set"))
		date_default_timezone_set('Europe/Brussels');

	$maanden = array("", "januari", "februari", "maart", "april", "mei", "juni",
		"juli", "augustus", "september", "oktober", "november", "december");

	$days = array('zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag',
		'vrijdag', 'zaterdag', 'zondag');
	$daysShort = array('Z', 'M', 'D', 'W', 'D', 'V', 'Z', 'Z');
	$today = getdate();
	$currentDay = $today['mday'];
	$currentMonth = $today['mon'];
	$currentYear = $today['year'];

	$startYear = ($currentMonth < 8) ? $currentYear - 1 : $currentYear;
	$endYear = $startYear + 1;
	$startMonth = 8;

	$doc = CreateBaseDocument("Jaarkalender $startYear - $endYear", "kalender", "jaaroverzicht");
	$content = $doc->FindElementById("content");
	{
		$cal = $content->AddTag("div", array("class" => "yearCalendar"));
		for ($mIdx = 0; $mIdx < 12; ++$mIdx)
		{
			$calMonth = $cal->AddTag("div", array("class" => "calendarMonth"));
			$month = $startMonth + $mIdx;
			$realMonth = ($month - 1) % 12 + 1;
			$realYear = $startYear + (int)(($month - 1) / 12);
			if (($realYear == $currentYear) && ($realMonth == $currentMonth))
				$calMonth->SetAttribute("id", "currentMonth");
			{
				$calMonth->AddTag("div", array("class" => "date"), "{$maanden[$realMonth]} $realYear");
				$table = $calMonth->AddTag("table");
				{
					$thead = $table->AddTag("thead");
			{
				$tr = $thead->AddTag("tr");
				for ($wIdx = 1; $wIdx <= 7; ++$wIdx)
					$tr->AddTag("td", array(), $daysShort[$wIdx]);
			}
					$tbody = $table->AddTag("tbody");
				{
					$date = strtotime("$realYear/$realMonth/01 12:00:00");
					$dateInfo = getdate($date);
					$day1 = $dateInfo['wday'];
					$daysToAdd = $day1 - 1;
					if ($daysToAdd <= 0) $daysToAdd += 7;
					$date -= $daysToAdd * 24*60*60;
					for ($wIdx = 0; $wIdx < 6; ++$wIdx)
					{
						$tr = $tbody->AddTag("tr");
						for ($dIdx = 0; $dIdx < 7; ++$dIdx)
						{
							$td = $tr->AddTag("td");
							$dateInfo = getdate($date);
							$mday = $dateInfo['mday'];
							$content = "&nbsp;";
							$attr = array();
							if (($dateInfo['mon'] == $realMonth) &&
								($dateInfo['year'] == $realYear))
							{
								if (($dateInfo['mon'] == $currentMonth) &&
									($dateInfo['year'] == $realYear) &&
									($dateInfo['mday'] == $currentDay))
								{
									$attr["class"] = "today";
								}
								$content = $mday;
							}
							$wday = $dateInfo["wday"];
							if ((($wday >= 6) || ($wday == 0))
								&& ($content != "&nbsp;"))
								$td->SetAttribute("class", "weekend");
							$td->AddTag("div", $attr, $content);
							$date += 24*60*60;
						}
					}
				}
				}
			}
		}
	}

	echo $doc->Serialize(true);
}
catch (ErrorException $e)
{
	if (array_key_exists("debug", $_GET))
	{
		print "<pre>" . $e->GetMessage() . "</pre>";
		print "<pre>" . $e->getTraceAsString() . "</pre>";
	}
	else
	{
		print("<h1>Fout in pagina.</h1><h2>Gelieve de school te waarschuwen</h2>");
	}
}
?>
