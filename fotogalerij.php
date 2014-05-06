<?php
try
{
	require_once("BaseDocument.php");

	$doc = CreateBaseDocument("Fotogalerij", "fotogalerij", "fotogalerij");
	$head = $doc->GetHead();
	{
		$head->AddTag("script", array("type" => "text/javascript", "src" => "http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"));
		$head->AddTag("link", array("href" => "pwi/js/jquery.fancybox/jquery.fancybox.css", "rel" => "stylesheet", "type" => "text/css"));
		$head->AddTag("script", array("src" => "pwi/js/jquery.fancybox/jquery.fancybox.pack.js", "type" => "text/javascript"));
		$head->AddTag("link", array("href" => "pwi/js/jquery.fancybox/helpers/jquery.fancybox-buttons.css?v=2.0.5", "rel" => "stylesheet", "type" => "text/css"));
		$head->AddTag("script", array("type" => "text/javascript", "src" => "pwi/js/jquery.fancybox/helpers/jquery.fancybox-buttons.js?v=2.0.5"));
		$head->AddTag("script", array("src" => "pwi/js/jquery.blockUI.js", "type" => "text/javascript"));
		$head->AddTag("link", array("href" => "pwi/css/pwi.css", "rel" => "stylesheet", "type" => "text/css"));
		$head->AddTag("script", array("src" => "pwi/js/jquery.pwi-min.js", "type" => "text/javascript"));
		$settings = '{
			username: "gvb3koningen",
			thumbCrop: 1,
			albumThumbSize: 72,
			showAlbumPhotoCount: false,
			showAlbumDescription: false,
			labels: {
				photo: "foto",
				photos: "fotos",
				downloadphotos: "Download fotos",
				albums: "Terug naar albums",
				page: "Pagina",
				prev: "Vorige",
				next: "Volgende",
				showPermalink: "Show PermaLink",
				showMap: "Toon kaart",
				videoNotSupported: "Video niet ondersteund"
			}
		}';
        $head->AddTag("script", array("type" => "text/javascript"), '$(document).ready(function(){$("#picasacontainer").pwi('. $settings . ');});');
	}
	$content = $doc->FindElementById("content");
	{
		$content->AddTag("div", array("id" => "picasacontainer"));
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
