<?php
try
{
session_start();

require_once("HTMLDocument.php");

$maanden = array("", "januari", "februari", "maart", "april", "mei", "juni",
    "juli", "augustus", "september", "oktober", "november", "december");

$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
mysql_select_db("3koningen_be") or die("could not find database");

$vandaag = getdate();
$thisjaar = $vandaag["year"];
$thismaand = $vandaag["mon"];

$prevjaar = $thisjaar;
$prevmaand = $thismaand - 1;
if ($prevmaand < 1)
{
  --$prevjaar;
  $prevmaand = 12;
}

$nextjaar = $thisjaar;
$nextmaand = $thismaand - 1;
if ($nextmaand > 12)
{
  ++$nextjaar;
  $nextmaand = 1;
}
$eerstemaanddag = mktime(12,0,0,$thismaand,1,$thisjaar);
$prevmaanddagen = array();
while (true)
{
  $eerstemaanddag -= 24 * 60 * 60;
  $diedag = getdate($eerstemaanddag);
  $wday = $diedag["wday"];
  if (($wday == 0) || ($wday == 6) || ($wday == 7)) // zondag
    break;
  if (($wday == 1) || ($wday == 2) || ($wday == 4) || ($wday == 5))
    $prevmaanddagen[] = $diedag["mday"];
}

$laatstemaanddag = mktime(12,0,0,$nextmaand,1,$nextjaar) - 24 * 60 * 60;
$nextmaanddagen = array();
while (true)
{
  $laatstemaanddag += 24 * 60 * 60;
  $diedag = getdate($laatstemaanddag);
  $wday = $diedag["wday"];
  if (($wday == 0) || ($wday == 6) || ($wday == 7)) // zondag
    break;
  if (($wday == 1) || ($wday == 2) || ($wday == 4) || ($wday == 5))
    $nextmaanddagen[] = $diedag["mday"];
}

$query = "SELECT year(datum), month(datum), dayofmonth(datum), menu FROM maandmenu WHERE (((year(datum) = $thisjaar) AND (month(datum) = $thismaand))";
if (count($prevmaanddagen) > 0)
{
  sort($prevmaanddagen);
  $first = $prevmaanddagen[0];
  $last = end($prevmaanddagen);
  $query .= " || ((year(datum) = $prevjaar) AND (month(datum) = $prevmaand) AND (dayofmonth(datum) >= $first) AND (dayofmonth(datum) <= $last))";
}
if (count($nextmaanddagen) > 0)
{
  sort($nextmaanddagen);
  $first = $nextmaanddagen[0];
  $last = end($nextmaanddagen);
  $query .= " || ((year(datum) = $nextjaar) AND (month(datum) = $nextmaand) AND (dayofmonth(datum) >= $first) AND (dayofmonth(datum) <= $last))";
}
$query .= ") ORDER BY datum";
$result = mysql_query($query);
if (!$result)
  echo "query failed";
$menus = array();
while (list($jaar, $maand, $maanddag, $menu) = mysql_fetch_array($result))
{
  $datum = sprintf("%04d-%02d-%02d", $jaar, $maand, $maanddag);
  $menus[$datum] = $menu;
}

$doc = new HTMLDocument();
{
  $head = $doc->GetHead();
  {
    $head->AddTag("meta", array("http-equiv" => "content-type", "content" => "text/html;charset=utf-8"));
    $head->AddTag("meta", array("name" => "keywords", "content" => "GVB, gesubsidieerde, vrije, basisschool, driekoningen, torhout, steenveldstraat, rudy, vandeputte"));
    $head->AddTag("title", array(), "Maandmenu: {$maanden[$thismaand]} $thisjaar");
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
        $nav->AddTag("p", array("class" => "maandmenu"), "Maandmenu");
        $nav->AddTag("ul")->AddTag("li")->AddTag("a", array("href" => "index.html"), "terug naar de homepagina");
      }

      $content = $container->AddTag("div", array("id" => "content"));
      {
        $content->AddTag("h1", array(), "{$maanden[$thismaand]} $thisjaar");

        $table = $content->AddTag("table", array("id" => "maandmenu", "summary" => "maandmenu"));
        {
          $tr = $table->AddTag("thead")->AddTag("tr");
          $tr->AddTag("td")->AddTag("h3", array(), "maandag");
          $tr->AddTag("td")->AddTag("h3", array(), "dinsdag");
          $tr->AddTag("td")->AddTag("h3", array(), "donderdag");
          $tr->AddTag("td")->AddTag("h3", array(), "vrijdag");

          $tbody = $table->AddTag("tbody");
          {
            $weektimestamp = mktime(12,0,0,$thismaand,1,$thisjaar);
            $weekdate = getdate($weektimestamp);
            while (($weekdate["wday"] != 0) && ($weekdate["wday"] != 6) && ($weekdate["wday"] != 7))
            {
              $weektimestamp -= 24 * 60 * 60;
              $weekdate = getdate($weektimestamp);
            }
            while ($weekdate["wday"] != 1)
            {
              $weektimestamp += 24 * 60 * 60;
              $weekdate = getdate($weektimestamp);
            }

            while (($weekdate["mon"] == $thismaand) || ($weekdate["mon"] == $prevmaand))
            {
              $tr = $tbody->AddTag("tr");

              $dagtimestamp = $weektimestamp;
              $dagdate = $weekdate;
              foreach (array(1, 2, 4, 5) as $wday)
              {
                while ($dagdate["wday"] != $wday)
                {
                  $dagtimestamp += 24 * 60 * 60;
                  $dagdate = getdate($dagtimestamp);
                }
                $datum = sprintf("%04d-%02d-%02d", $dagdate["year"], $dagdate["mon"], $dagdate["mday"]);
                $menu = isset($menus[$datum]) ? $menus[$datum] : "";
                $menu = "<span>$menu</span>";

                $td = $tr->AddTag("td");
                {
                  $td->AddTag("h3", array(), $dagdate["mday"]);
                  $td->AppendChild(DOMDocument::loadXML($menu)->documentElement);
                }
              }
              $weektimestamp += 7 * 24 * 60 * 60;
              $weekdate = getdate($weektimestamp);
            }
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
