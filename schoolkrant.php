<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	if (function_exists("date_default_timezone_set"))
		date_default_timezone_set('Europe/Brussels');

	$today = getdate();
	$currentDay = $today['mday'];
	$currentMonth = $today['mon'];
	$currentYear = $today['year'];

	$startYear = ($currentMonth < 8) ? $currentYear - 1 : $currentYear;

	$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
	mysql_select_db("3koningen_be") or die("could not find database");

	$query = "SELECT sp_file_uploads.url, sp_file_uploads.thumbnail_url FROM sp_file_uploads, sp_file_context, sp_timestamp WHERE sp_file_uploads.upload_key = sp_file_context.upload_key AND sp_file_uploads.timestamp_key = sp_timestamp.timestamp_key AND sp_file_context.context = 'schat' AND sp_file_context.schooljaar = $startYear ORDER BY sp_timestamp.timestamp DESC";
	$result = mysql_query($query);

	$doc = CreateBaseDocument("Schoolkrant", "schoolkrant", "schoolkrant");
	$content = $doc->FindElementById("content");
	while (list($url, $thumbnailUrl) = mysql_fetch_array($result))
	{
		$content->AddTag("a", array("class" => "imagelink", "href" => $url))->AddTag("img", array("src" => $thumbnailUrl, "alt" => ""));
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
