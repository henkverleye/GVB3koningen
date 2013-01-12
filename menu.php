<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	$maanden = array("", "januari", "februari", "maart", "april", "mei", "juni",
		"juli", "augustus", "september", "oktober", "november", "december");

	$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
	mysql_select_db("3koningen_be") or die("could not find database");

	$vandaag = getdate();
	$thisjaar = $vandaag["year"];
	$thismaand = $vandaag["mon"];

	$prevjaar = $thisjaar;
	$prevmaand = $thismaand - 1;
	if ($prevmaand < 1)
	{
		--$prevjaar;
		$prevmaand = 12;
	}

	$nextjaar = $thisjaar;
	$nextmaand = $thismaand - 1;
	if ($nextmaand > 12)
	{
		++$nextjaar;
		$nextmaand = 1;
	}
	$eerstemaanddag = mktime(12,0,0,$thismaand,1,$thisjaar);
	$prevmaanddagen = array();
	while (true)
	{
		$eerstemaanddag -= 24 * 60 * 60;
		$diedag = getdate($eerstemaanddag);
		$wday = $diedag["wday"];
		if (($wday == 0) || ($wday == 6) || ($wday == 7)) // zondag
			break;
		if (($wday == 1) || ($wday == 2) || ($wday == 4) || ($wday == 5))
			$prevmaanddagen[] = $diedag["mday"];
	}

	$laatstemaanddag = mktime(12,0,0,$nextmaand,1,$nextjaar) - 24 * 60 * 60;
	$nextmaanddagen = array();
	while (true)
	{
		$laatstemaanddag += 24 * 60 * 60;
		$diedag = getdate($laatstemaanddag);
		$wday = $diedag["wday"];
		if (($wday == 0) || ($wday == 6) || ($wday == 7)) // zondag
			break;
		if (($wday == 1) || ($wday == 2) || ($wday == 4) || ($wday == 5))
			$nextmaanddagen[] = $diedag["mday"];
	}

	$query = "SELECT year(datum), month(datum), dayofmonth(datum), menu FROM maandmenu WHERE (((year(datum) = $thisjaar) AND (month(datum) = $thismaand))";
	if (count($prevmaanddagen) > 0)
	{
		sort($prevmaanddagen);
		$first = $prevmaanddagen[0];
		$last = end($prevmaanddagen);
		$query .= " || ((year(datum) = $prevjaar) AND (month(datum) = $prevmaand) AND (dayofmonth(datum) >= $first) AND (dayofmonth(datum) <= $last))";
	}
	if (count($nextmaanddagen) > 0)
	{
		sort($nextmaanddagen);
		$first = $nextmaanddagen[0];
		$last = end($nextmaanddagen);
		$query .= " || ((year(datum) = $nextjaar) AND (month(datum) = $nextmaand) AND (dayofmonth(datum) >= $first) AND (dayofmonth(datum) <= $last))";
	}
	$query .= ") ORDER BY datum";
	$result = mysql_query($query);
	if (!$result)
		echo "query failed";
	$menus = array();
	while (list($jaar, $maand, $maanddag, $menu) = mysql_fetch_array($result))
	{
		$datum = sprintf("%04d-%02d-%02d", $jaar, $maand, $maanddag);
		$menus[$datum] = $menu;
	}

	$doc = CreateBaseDocument("Maandmenu: {$maanden[$thismaand]} $thisjaar", "maandmenu", "schoolmenu");
	$content = $doc->FindElementById("content");
	{
		$content->AddTag("h1", array(), "{$maanden[$thismaand]} $thisjaar");

		$table = $content->AddTag("table", array("id" => "maandmenu", "summary" => "maandmenu"));
		{
			$tr = $table->AddTag("thead")->AddTag("tr");
			$tr->AddTag("td")->AddTag("h3", array(), "maandag");
			$tr->AddTag("td")->AddTag("h3", array(), "dinsdag");
			$tr->AddTag("td")->AddTag("h3", array(), "donderdag");
			$tr->AddTag("td")->AddTag("h3", array(), "vrijdag");

			$tbody = $table->AddTag("tbody");
			{
				$weektimestamp = mktime(12,0,0,$thismaand,1,$thisjaar);
				$weekdate = getdate($weektimestamp);
				while (($weekdate["wday"] != 0) && ($weekdate["wday"] != 6) && ($weekdate["wday"] != 7))
				{
					$weektimestamp -= 24 * 60 * 60;
					$weekdate = getdate($weektimestamp);
				}
				while ($weekdate["wday"] != 1)
				{
					$weektimestamp += 24 * 60 * 60;
					$weekdate = getdate($weektimestamp);
				}

				while (($weekdate["mon"] == $thismaand) || ($weekdate["mon"] == $prevmaand))
				{
					$tr = $tbody->AddTag("tr");

					$dagtimestamp = $weektimestamp;
					$dagdate = $weekdate;
					foreach (array(1, 2, 4, 5) as $wday)
					{
						while ($dagdate["wday"] != $wday)
						{
							$dagtimestamp += 24 * 60 * 60;
							$dagdate = getdate($dagtimestamp);
						}
						$datum = sprintf("%04d-%02d-%02d", $dagdate["year"], $dagdate["mon"], $dagdate["mday"]);
						$menu = isset($menus[$datum]) ? $menus[$datum] : "";

						$td = $tr->AddTag("td");
						{
							$td->AddTag("h3", array(), $dagdate["mday"]);
							$td->AddHTMLFragment($menu);
						}
					}
					$weektimestamp += 7 * 24 * 60 * 60;
					$weekdate = getdate($weektimestamp);
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
