<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	if (function_exists("date_default_timezone_set"))
		date_default_timezone_set('Europe/Brussels');

	$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
	mysql_select_db("3koningen_be") or die("could not find database");

	$dateInfo = getdate();
	$schooljaar = $dateInfo['year'];
	if ($dateInfo['mon'] <= 8)
		--$schooljaar;
	$volgendjaar = $schooljaar + 1;

	$begindatum = Date("Y-m-d");
	$einddatum = "$volgendjaar-07-31";

	$doc = CreateBaseDocument("File upload", "brieven", "");
	$head = $doc->GetHead();
	{
		$head->AddTag("link", array("rel" => "stylesheet", "href" => "http://code.jquery.com/ui/1.9.1/themes/base/jquery-ui.css"));
		$head->AddTag("script", array("type" => "text/javascript", "src" => "http://code.jquery.com/jquery-1.8.2.js"));
		$head->AddTag("script", array("type" => "text/javascript", "src" => "http://code.jquery.com/ui/1.9.1/jquery-ui.js"));
		$head->AddTag("script", array(), '$(function() { $("#begindatum").datepicker({dateFormat:"yy-mm-dd"}); $("#einddatum").datepicker({dateFormat:"yy-mm-dd"}); });');
	}
	$content = $doc->FindElementById("content");
	{
		if (isset($_FILES["filefield"]))
		{
			$allowedExts = array("PDF");
			$allowedMime = array("application/pdf");
			$extension = strtoupper(end( explode( ".", $_FILES["filefield"]["name"])));
			if (in_array($_FILES["filefield"]["type"], $allowedMime) &&
				in_array($extension, $allowedExts))
			{
				if ( $_FILES['filefield']['error'] > 0)
					$content->AddTag("p", array(), "Error: {$_FILES['filefield']['error']}");
				else
				{
					$titel = $_POST['titel'];
					$context = $_POST['context'];
					$begindatum = $_POST['begindatum'];
					$einddatum = $_POST['einddatum'];
					$content->AddTag("p", array(), "Upload: {$_FILES['filefield']['name']}");
					$content->AddTag("p", array(), "Type: {$_FILES['filefield']['type']}");
					$content->AddTag("p", array(), "Grootte: {$_FILES['filefield']['size']}");
					$destination = "uploads/$context/{$_FILES['filefield']['name']}";
					if (move_uploaded_file($_FILES['filefield']['tmp_name'], $destination))
					{
						mysql_query("INSERT INTO sp_timestamp (timestamp) VALUES (now())");
						mysql_query("INSERT INTO sp_file_uploads (timestamp_key, url) VALUES (LAST_INSERT_ID(), '$destination')");
						mysql_query("INSERT INTO sp_file_context (upload_key, titel, schooljaar, context, begindatum, einddatum) VALUES (LAST_INSERT_ID(), '$titel', $schooljaar, '$context', '$begindatum', '$einddatum')");
						$content->AddTag("p", array(), "Bewaard als: $destination");
					}
					else
						$content->AddTag("p", array(), "Error: failed to move to $destination");
				}
			}
			else
				$content->AddTag("p", array(), "Error: invalid file");
		}

		$form = $content->AddTag("form", array("action" => $_SERVER["PHP_SELF"], "method" => "post", "enctype" => "multipart/form-data"));
		$field = $form->AddTag("fieldset");
		$field->AddTag("label", array("for" => "filefield"), "File name:");
		$field->AddTag("input", array("type" => "file", "name" => "filefield", "id" => "filefield"));
		$field = $form->AddTag("fieldset");
		$field->AddTag("label", array("for" => "titel"), "Titel:");
		$field->AddTag("input", array("type" => "text", "name" => "titel", "id" => "titel", "size" => "35", "value" => ""));
		$field = $form->AddTag("fieldset");
		$field->AddTag("label", array("for" => "context"), "Context:");
		$field->AddTag("input", array("type" => "radio", "name" => "context", "id" => "context", "value" => "brief", "checked" => "checked"), "brief");
		$field->AddTag("input", array("type" => "radio", "name" => "context", "id" => "context", "value" => "schat"), "De Schat");
		$field->AddTag("input", array("type" => "radio", "name" => "context", "id" => "context", "value" => "weekagenda"), "Weekagenda");
		$field = $form->AddTag("fieldset");
		$field->AddTag("label", array("for" => "begindatum"), "Verschijningsdatum:");
		$field->AddTag("input", array("type" => "text", "name" => "begindatum", "id" => "begindatum", "size" => "12", "value" => $begindatum));
		$field = $form->AddTag("fieldset");
		$field->AddTag("label", array("for" => "einddatum"), "Vervaldatum:");
		$field->AddTag("input", array("type" => "text", "name" => "einddatum", "id" => "einddatum", "size" => "12", "value" => $einddatum));
		$field = $form->AddTag("fieldset");
		$field->AddTag("input", array("type" => "submit", "name" => "submit", "value" => "Opslaan"));
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
