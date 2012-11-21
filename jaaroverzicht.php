<?php
$_GET["debug"] = 1;
try
{
	//session_start();

	require_once("HTMLDocument.php");

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

	$doc = new HTMLDocument();
	{
		$head = $doc->GetHead();
		{
			$head->AddTag("meta", array("http-equiv" => "content-type", "content" => "text/html;charset=utf-8"));
			$head->AddTag("meta", array("name" => "keywords", "content" => "GVB, gesubsidieerde, vrije, basisschool, driekoningen, torhout, steenveldstraat, rudy, vandeputte"));
			$head->AddTag("title", array(), "Jaarkalender $startYear - $endYear");
			$head->AddTag("link", array("rel" => "stylesheet", "type" => "text/css", "href" => "style/screen.css", "media" => "screen"));

			$d = new DOMDocument();
			$ieLink = $d->createElement("link");
			$ieLink->setAttribute("rel", "stylesheet");
			$ieLink->setAttribute("type", "text/css");
			$ieLink->setAttribute("href", "../style/iescreen.css");
			$ieLink->setAttribute("media", "screen");
			$head->AddComment("[if IE]>" . $d->saveXML($ieLink) . "<![endif]");

			$head->AddTag("script", array("type" => "text/javascript", "src" => "../scripts/tellerroot.js"));
			$head->AddTag("script", array("type" => "text/javascript", "src" => "../scripts/teller.js"));

			$head->AddTag("script", array("type" => "text/javascript"), "document.write(unescape(\"%3Cscript src='\" + ((\"https:\" == document.location.protocol) ? \"https\" : \"http\") + \"://e.mouseflow.com/projects/8e33fe49-7314-48b5-b93a-6159aebddb3b.js' type='text/javascript'%3E%3C/script%3E\"));");
		}

		$body = $doc->GetBody();
		$body->SetAttribute("class", "subpagina");
		{
			$container = $body->AddTag("div", array("id" => "container"));
			{
				$container->AddTag("div", array("id" => "header"));

				$nav = $container->AddTag("div", array("id" => "navigation"));
				{
					$nav->AddTag("p", array("class" => "maandmenu"), "Maandmenu");
					$nav->AddTag("ul")->AddTag("li")->AddTag("a", array("href" => "index.html"), "terug naar de homepagina");
				}

				$content = $container->AddTag("div", array("id" => "content"));
				{
					$cal = $content->AddTag("div", array("class" => "yearCalendar"));
					for ($mIdx = 0; $mIdx < 12; ++$mIdx)
					{
						$calAttr = array("class" => "calendarMonth");
						$month = $startMonth + $mIdx;
						$realMonth = ($month - 1) % 12 + 1;
						$realYear = $startYear + (int)(($month - 1) / 12);
						if (($realYear == $currentYear) && ($realMonth == $currentMonth))
							$calAttr["id"] = "currentMonth";
						$calMonth = $cal->AddTag("div", $calAttr);
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
									$date = strtotime("$realYear/$realMonth/01");
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
											$tr->AddTag("td", $attr, $content);
											$date += 24*60*60;
										}
									}
								}
							}
						}
					}
				}

				$footer = $container->AddTag("div", array("id" => "footer"));
				{
					$ul = $footer->AddTag("ul", array("class" => "adres"));
					$ul->AddTag("li", array(), "Basisschool Driekoningen");
					$ul->AddTag("li", array(), "Steenveldstraat 2");
					$ul->AddTag("li", array(), "8820 Torhout");
					$ul->AddTag("li", array(), "Tel. (050) 22 36 95");
					$ul->AddTag("li", array(), "Fax (050) 21 61 25");
					$ul->AddTag("li")->AddTag("a", array("href" => "mailto:basisschool.driekoningen@sint-rembert.be"), "basisschool.driekoningen@sint-rembert.be");

					$footer->AddTag("p", array(), "Scholengroep Sint-Rembert");
				}

				$container->AddTag("script", array("type" => "text/javascript"), "ToonTeller(\"schoolmenu\", 3485);");
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
