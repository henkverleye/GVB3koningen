<?php
try
{
session_start();

require_once("HTMLDocument.php");

$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
mysql_select_db("3koningen_be") or die("could not find database");


$query = "SELECT url, titel FROM links";
$result = mysql_query($query);
if (!$result)
  echo "query failed";

$doc = new HTMLDocument();
{
  $head = $doc->GetHead();
  {
    $head->AddTag("meta", array("http-equiv" => "content-type", "content" => "text/html;charset=utf-8"));
    $head->AddTag("meta", array("name" => "keywords", "content" => "GVB, gesubsidieerde, vrije, basisschool, driekoningen, torhout, steenveldstraat, rudy, vandeputte"));
    $head->AddTag("title", array(), "Links");
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
        $nav->AddTag("p", array("class" => "links"), "Links");
        $nav->AddTag("ul")->AddTag("li")->AddTag("a", array("href" => "index.html"), "terug naar de homepagina");
      }

      $content = $container->AddTag("div", array("id" => "content"));
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

      $footer = $container->AddTag("div", array("id" => "footer"));
      {
        $ul = $footer->AddTag("ul", array("class" => "adres"));
        $ul->AddTag("li", array(), "Basisschool Driekoningen");
        $ul->AddTag("li", array(), "Steenveldstraat 2");
        $ul->AddTag("li", array(), "8820 Torhout");
        $ul->AddTag("li", array(), "Tel. (050) 22 36 59");
        $ul->AddTag("li", array(), "Fax (050) 21 61 25");
        $ul->AddTag("li")->AddTag("a", array("href" => "mailto:basisschool.driekoningen@sint-rembert.be"), "basisschool.driekoningen@sint-rembert.be");

        $footer->AddTag("p", array(), "Scholengroep Sint-Rembert");
      }

      $container->AddTag("script", array("type" => "text/javascript"), "ToonTeller(\"dummy\", 12344321);");
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
