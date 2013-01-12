<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	if (function_exists("date_default_timezone_set"))
		date_default_timezone_set('Europe/Brussels');

	$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
	mysql_select_db("3koningen_be") or die("could not find database");

	$currentJaar = 0 + Date("Y");
	if ((0 + Date("m")) < 8)
		--$currentJaar;
	$query = "SELECT sp_file_context.titel, sp_file_uploads.url, DATE_FORMAT(sp_file_context.begindatum, '%d-%m-%Y') FROM sp_file_uploads INNER JOIN sp_file_context ON sp_file_uploads.upload_key = sp_file_context.upload_key WHERE ((sp_file_context.schooljaar = $currentJaar) AND (sp_file_context.context = 'brief') AND (sp_file_context.begindatum <= now()) AND (sp_file_context.einddatum >= now())) ORDER BY sp_file_context.begindatum DESC";
	$result = mysql_query($query);
	if (!$result)
		echo "query failed: " . mysql_error();

	$doc = CreateBaseDocument("Brieven", "brieven", "brieven");
	$content = $doc->FindElementById("content");
	{
		$content->AddTag("h1", array(), "Brieven");

		$ul = $content->AddTag("ul", array("class" => "brieflist"));
		while (list($titel, $url, $datum) = mysql_fetch_array($result))
		{
			$li = $ul->AddTag("li");
			{
				$li->AddTag("a", array("href" => $url), "&nbsp;");
				$li->AddTag("span", array("class" => "datum"), $datum);
				$li->AddTag("span", array(), $titel);
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
