<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
	mysql_select_db("3koningen_be") or die("could not find database");


	$query = "SELECT url, titel FROM links";
	$result = mysql_query($query);
	if (!$result)
		echo "query failed";

	$doc = CreateBaseDocument("Links", "links", "links");
	$content = $doc->FindElementById("content");
	{
		$content->AddTag("h1", array(), "Links");

		$ul = $content->AddTag("ul", array("class" => "linklist"));
		while (list($url, $titel) = mysql_fetch_array($result))
		{
			$li = $ul->AddTag("li");
			{
				$li->AddTag("a", array("href" => $url), "&nbsp;");
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
