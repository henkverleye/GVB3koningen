<?php
try
{
	session_start();

	require_once("HTMLDocument.php");

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

	$doc = new HTMLDocument();
	{
		$head = $doc->GetHead();
		{
			$head->AddTag("meta", array("http-equiv" => "content-type", "content" => "text/html;charset=utf-8"));
			$head->AddTag("meta", array("name" => "keywords", "content" => "GVB, gesubsidieerde, vrije, basisschool, driekoningen, torhout, steenveldstraat, rudy, vandeputte"));
			$head->AddTag("title", array(), "Schoolkrant");
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
					$nav->AddTag("p", array("class" => "schoolkrant"), "Schoolkrant");
					$nav->AddTag("ul")->AddTag("li")->AddTag("a", array("href" => "index.html"), "terug naar de homepagina");
				}

				$content = $container->AddTag("div", array("id" => "content"));
				while (list($url, $thumbnailUrl) = mysql_fetch_array($result))
				{
					$content->AddTag("a", array("class" => "imagelink", "href" => $url))->AddTag("img", array("src" => $thumbnailUrl, "alt" => ""));
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
