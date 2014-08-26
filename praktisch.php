<?php
try
{
	session_start();

	require_once("BaseDocument.php");

	$doc = CreateBaseDocument("Praktische info", "praktisch", "praktisch");
	$content = $doc->FindElementById("content");
	{
		$content->AddTag("h1", array(), "Praktische info");

		$ul = $content->AddTag("ul", array("class" => "linklist"));
		{
			$li = $ul->AddTag("li");
			{
				$li->AddTag("a", array("href" => "reglement.pdf"), "&nbsp;");
				$li->AddTag("span", array(), "schoolreglement");
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
