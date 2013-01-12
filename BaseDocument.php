<?php
require_once("HTMLDocument.php");

function CreateBaseDocument($titel, $style, $tellerKey)
{
	$doc = new HTMLDocument();
	{
		$head = $doc->GetHead();
		{
			$head->AddTag("meta", array("http-equiv" => "content-type", "content" => "text/html;charset=utf-8"));
			$head->AddTag("meta", array("name" => "keywords", "content" => "GVB, gesubsidieerde, vrije, basisschool, driekoningen, torhout, steenveldstraat, rudy, vandeputte"));
			$head->AddTag("title", array(), $titel);
			$head->AddTag("link", array("rel" => "stylesheet", "type" => "text/css", "href" => "style/screen.css", "media" => "screen"));

			$d = new DOMDocument();
			$ieLink = $d->createElement("link");
			$ieLink->setAttribute("rel", "stylesheet");
			$ieLink->setAttribute("type", "text/css");
			$ieLink->setAttribute("href", "../style/iescreen.css");
			$ieLink->setAttribute("media", "screen");
			$head->AddComment("[if IE]>" . $d->saveXML($ieLink) . "<![endif]");

			$head->AddTag("script", array("type" => "text/javascript", "src" => "scripts/tellerroot.js"));
			$head->AddTag("script", array("type" => "text/javascript", "src" => "scripts/teller.js"));
		}

		$body = $doc->GetBody();
		$body->SetAttribute("class", "subpagina");
		{
			$container = $body->AddTag("div", array("id" => "container"));
			{
				$container->AddTag("div", array("id" => "header"));

				$nav = $container->AddTag("div", array("id" => "navigation"));
				{
					$nav->AddTag("p", array("class" => $style), $titel);
					$nav->AddTag("ul")->AddTag("li")->AddTag("a", array("href" => "index.html"), "terug naar de homepagina");
				}

				$content = $container->AddTag("div", array("id" => "content"));

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

				if (strlen($tellerKey) > 0)
				{
					$checkPass = 0;
					for ($idx = 0; $idx < strlen($tellerKey); ++$idx)
					{
						$num = ord(substr($tellerKey, $idx, 1));
						$checkPass += $num * $num;
					}
					while ($checkPass < 4242)
						$checkPass *= $checkPass;
					$checkPass %= 4242;
					$container->AddTag("script", array("type" => "text/javascript"), "ToonTeller(\"$tellerKey\", $checkPass);");
				}
			}
		}
	}
	return $doc;
}
?>
