<?php
require_once("HTMLDocument.php");

$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
mysql_select_db("3koningen_be") or die("could not find database");
$query = "SELECT url, count FROM counters ORDER BY count DESC";
$result = mysql_query($query);

$doc = new HTMLDocument();

$head = $doc->GetHead();
$head->AddTag("title", array(), "Tellers overzicht 3koningen.be");

$body = $doc->GetBody();
if (mysql_num_rows($result) > 0)
{
	$table = $body->AddTag("table", array(border => "1"));
	$tr = $table->AddTag("thead")->AddTag("tr");
	$tr->AddTag("td", array(bgcolor => "#ffcccc", align => "center"), "URL");
	$tr->AddTag("td", array(bgcolor => "#ffcccc", align => "center"), "AANTAL");

	$tbody = $table->AddTag("tbody");
  while (list($url, $getal) = mysql_fetch_array($result))
	{
		$tr = $tbody->AddTag("tr");
		$tr->AddTag("td", array(align => "left"), $url);
		$tr->AddTag("td", array(align => "right"), $getal);
	}
}
else
{
	$body->AddTag("p", array(), "Geen  tellers gevonden");
}

echo $doc->Serialize(true);
?>
