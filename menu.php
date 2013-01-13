<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	if (function_exists("date_default_timezone_set"))
		date_default_timezone_set('Europe/Brussels');

	$maanden = array("", "januari", "februari", "maart", "april", "mei", "juni",
		"juli", "augustus", "september", "oktober", "november", "december");
	$numWeken = 2;

	$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
	mysql_select_db("3koningen_be") or die("could not find database");

	$vandaag = getdate();
	$thisjaar = $vandaag["year"];
	$thismaand = $vandaag["mon"];
	$thisdag = $vandaag["mday"];
	$thiswdag = $vandaag["wday"];

	$eersteMaandagD = mktime() - ($thiswdag % 7 - 1) * (24*60*60);
	$eersteMaandagDatum = strftime("%Y-%m-%d", $eersteMaandagD);
	$eersteMaandag = getdate($eersteMaandagD);
	$laatsteVrijdagD = $eersteMaandagD + ($numWeken * 7 - 3) * (24*60*60);
	$laatsteVrijdagDatum = strftime("%Y-%m-%d", $laatsteVrijdagD);
	$laatsteVrijdag = getdate($laatsteVrijdagD);

	$menus = array();
	$query = "SELECT year(datum), month(datum), dayofmonth(datum), menu FROM maandmenu WHERE ((datum >= '$eersteMaandagDatum') AND (datum <= '$laatsteVrijdagDatum')) ORDER by datum";
	$result = mysql_query($query);
	if (!$result)
		echo "query failed";
	while (list($jaar, $maand, $maanddag, $menu) = mysql_fetch_array($result))
	{
		$datum = sprintf("%04d-%02d-%02d", $jaar, $maand, $maanddag);
		$menus[$datum] = $menu;
	}

	$titel = "Maandmenu: ";
	if ($eersteMaandag["mon"] == $laatsteVrijdag["mon"])
	{
		$dag1 = $eersteMaandag["mday"];
		$dag2 = $laatsteVrijdag["mday"];
		$maand = $maanden[$eersteMaandag["mon"]];
		$jaar = $eersteMaandag["year"];
		$titel .= "$dag1 - $dag2 $maand $jaar";
	}
	else if ($eersteMaandag["year"] == $laatsteVrijdag["year"])
	{
		$dag1 = $eersteMaandag["mday"];
		$dag2 = $laatsteVrijdag["mday"];
		$maand1 = $maanden[$eersteMaandag["mon"]];
		$maand2 = $maanden[$laatsteVrijdag["mon"]];
		$jaar = $eersteMaandag["year"];
		$titel .= "$dag1 $maand1 - $dag2 $maand2 $jaar";
	}
	else
	{
		$dag1 = $eersteMaandag["mday"];
		$dag2 = $laatsteVrijdag["mday"];
		$maand1 = $maanden[$eersteMaandag["mon"]];
		$maand2 = $maanden[$laatsteVrijdag["mon"]];
		$jaar1 = $eersteMaandag["year"];
		$jaar2 = $laatsteVrijdag["year"];
		$titel .= "$dag1 $maand1 $jaar1- $dag2 $maand2 $jaar2";
	}
	$doc = CreateBaseDocument($titel, "maandmenu", "schoolmenu");
	$content = $doc->FindElementById("content");
	{
		$content->AddTag("h1", array(), $titel);

		$table = $content->AddTag("table", array("id" => "maandmenu", "summary" => "maandmenu"));
		{
			$tr = $table->AddTag("thead")->AddTag("tr");
			$tr->AddTag("td")->AddTag("h3", array(), "maandag");
			$tr->AddTag("td")->AddTag("h3", array(), "dinsdag");
			$tr->AddTag("td")->AddTag("h3", array(), "donderdag");
			$tr->AddTag("td")->AddTag("h3", array(), "vrijdag");

			$tbody = $table->AddTag("tbody");
			{
				for ($idx = 0; $idx < $numWeken; ++$idx)
				{
					$tr = $tbody->AddTag("tr");
					foreach (array(1, 2, 4, 5) as $wday)
					{
						$dagtimestamp = $eersteMaandagD + ($idx * 7 + $wday - 1) * 24*60*60;
						$dagdate = getdate($dagtimestamp);
						$datum = strftime("%Y-%m-%d", $dagtimestamp);
						$dagtitel = $dagdate["mday"] . " " . $maanden[$dagdate["mon"]];
						$menu = isset($menus[$datum]) ? $menus[$datum] : "";

						$td = $tr->AddTag("td");
						{
							$td->AddTag("h3", array(), $dagtitel);
							$td->AddHTMLFragment($menu);
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
