<?php
$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
mysql_select_db("3koningen_be") or die("could not find database");

if (isset($_POST["datum"]) && isset($_POST["menu"]))
{
  $datum = $_POST["datum"];
  $menu = $_POST["menu"];
  $menu = str_replace("_3KTLT3KT_", "<", $menu);
  $menu = str_replace("_3KTGT3KT_", ">", $menu);
  $menu = str_replace("_3KTAMP3KT_", "&", $menu);
  $menu = str_replace("&nbsp;", " ", $menu);
  while (strpos($menu, "  "))
    $menu = str_replace("  ", " ", $menu);
  $query = "SELECT menu FROM maandmenu WHERE datum=\"$datum\"";
  $result = mysql_query($query);
  if (!$result)
  {
    echo "opslaan faalde";
    return;
  }
  if (!list($dummymenu) = mysql_fetch_array($result))
    $query = "INSERT INTO maandmenu(datum, menu) VALUES(\"$datum\", \"$menu\")";
  else
    $query = "UPDATE maandmenu SET menu=\"$menu\" WHERE datum=\"$datum\"";
  $result = mysql_query($query);
  if (!$result)
  {
    echo "opslaan faalde";
    return;
  }
  else
    echo "opgeslagen";
  return;
}

else if (isset($_GET["verwijder"]) && isset($_GET["jaar"]) && isset($_GET["maand"]))
{
  $jaar = $_GET["jaar"];
  $maand = $_GET["maand"];
  $query = "DELETE FROM maandmenu WHERE ((month(datum) = $maand) AND (year(datum) = $jaar))";
  $result = mysql_query($query);
}

$maanden = array("", "januari", "februari", "maart", "april", "mei", "juni",
    "juli", "augustus", "september", "oktober", "november", "december");

$vandaag = getdate();
$thisjaar = $vandaag["year"];
$thismaand = $vandaag["mon"];

$reqjaar = $thisjaar;
$reqmaand = $thismaand;
if (isset($_GET["toon"]) && isset($_GET["jaar"]) && isset($_GET["maand"]))
{
  $reqjaar = $_GET["jaar"];
  $reqmaand = $_GET["maand"];
}

$prevjaar = $reqjaar;
$prevmaand = $reqmaand - 1;
if ($prevmaand < 1)
{
  --$prevjaar;
  $prevmaand = 12;
}

$nextjaar = $reqjaar;
$nextmaand = $reqmaand - 1;
if ($nextmaand > 12)
{
  ++$nextjaar;
  $nextmaand = 1;
}
$eerstemaanddag = mktime(12,0,0,$reqmaand,1,$reqjaar);
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

$query = "SELECT year(datum), month(datum), dayofmonth(datum), menu FROM maandmenu WHERE (((year(datum) = $reqjaar) AND (month(datum) = $reqmaand))";
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta name="keywords" content="GVB, gesubsidieerde, vrije, basisschool, driekoningen, torhout, steenveldstraat, rudy, vandeputte" />
    <title>Maandmenu in de refter: <?php echo "{$maanden[$reqmaand]} $reqjaar"; ?></title>
    <link rel="stylesheet" type="text/css" href="../style/screen.css" media="screen" />
    <!--[if IE]><link rel="stylesheet" type="text/css" href="../style/iescreen.css" media="screen" /><![endif]-->
    <script type="text/javascript">
var myself = "<?php echo $_SERVER["PHP_SELF"]; ?>";
    </script>
    <script type="text/javascript" src="/fckeditor/fckeditor.js"></script>
    <script type="text/javascript" src="/scripts/ajax.js"></script>
    <script type="text/javascript">
var menu_store_xmlhttprequest = 0;

var fck_editor = 0;

function EditCell(id)
{
  var editor = document.getElementById("celleditor");
  editor.style.display = "block";

  var idfield = document.getElementById("celleditorid");
  idfield.value = id;

  var element = document.getElementById("m" + id);
  FCKeditorAPI.GetInstance("celleditorfield").SetHTML(element.innerHTML);
}

function StoreCell()
{
  var editor = document.getElementById("celleditor");
  editor.style.display = "none";

  var idfield = document.getElementById("celleditorid");
  var id = idfield.value;

  var contents = FCKeditorAPI.GetInstance("celleditorfield").GetHTML();
  var element = document.getElementById("m" + id);
  element.innerHTML = contents;

  contents = contents.replace(/\</ig, "_3KTLT3KT_");
  contents = contents.replace(/\>/ig, "_3KTGT3KT_");
  contents = contents.replace(/&/ig, "_3KTAMP3KT_");
  var form_submit = "datum=" + id + "&menu=" + contents;

  menu_store_xmlhttprequest = CreateAjaxObject();
  if (!menu_store_xmlhttprequest)
    return false;

  menu_store_xmlhttprequest.open("POST", myself, true);
  menu_store_xmlhttprequest.onreadystatechange =
    function()
    {
      if (menu_store_xmlhttprequest.readyState == 4)
        alert(menu_store_xmlhttprequest.responseText);
    }
  menu_store_xmlhttprequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  menu_store_xmlhttprequest.setRequestHeader("Content-length", form_submit.length);
  menu_store_xmlhttprequest.setRequestHeader("Connection", "close");
  menu_store_xmlhttprequest.send(form_submit);

  return false;
}

// called when FCKeditor is done starting..
function FCKeditor_OnComplete(editor_instance)
{
  editor_instance.LinkedField.form.onsubmit = StoreCell;
}
    </script>
  </head>
  <body>
    <script type="text/javascript" language="JavaScript">var dummy = 0;</script>
    <noscript>JavaScript is niet ingeschakeld in uw browser.</noscript>
    <div id="paginatitel">
      <div id="paginalogo1" style="background-image: url('../titels/maandmenu.png');"></div>
    </div>
    <div id="celleditor" style="display: none;">
      <form>
        <script type="text/javascript">
        fck_editor = new FCKeditor("celleditorfield");
        fck_editor.Config["CustomConfigurationsPath"] = "/scripts/listfckconfig.js";
        fck_editor.BasePath = "/fckeditor/";
        fck_editor.Width = "420px";
        fck_editor.Height = "200px";
        fck_editor.ToolbarSet = "List";
        fck_editor.Create();
        </script>
      </form>
      <input id="celleditorid" type="hidden" value=""></input>
    </div>
    <h1>Maandmenu in de refter: <?php echo "{$maanden[$reqmaand]} $reqjaar"; ?></h1>
    <table id="maandmenu" summary="maandmenu">
      <thead>
        <tr>
          <td><h3>maandag</h3></td>
          <td><h3>dinsdag</h3></td>
          <td><h3>donderdag</h3></td>
          <td><h3>vrijdag</h3></td>
        </tr>
      </thead>
<?php
$weektimestamp = mktime(12,0,0,$reqmaand,1,$reqjaar);
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

while (($weekdate["mon"] == $reqmaand) || ($weekdate["mon"] == $prevmaand))
{
?>
      <tr>
<?php
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
?>
        <td>
          <h3><?php echo $dagdate["mday"]; ?></h3>
          <div id="m<?php echo $datum; ?>"><?php echo $menu; ?></div>
          <button onclick="EditCell('<?php echo $datum; ?>'); return false;">Wijzigen</button>
        </td>
<?php
  }
  $weektimestamp += 7 * 24 * 60 * 60;
  $weekdate = getdate($weektimestamp);
?>
      </tr>
<?php
}
?>
    </table>
    <h3>Beschikbare maanden</h3>
    <table>
<?php
$query = "SELECT year(datum) as jaar, month(datum) as maand FROM maandmenu GROUP BY maand";
$result = mysql_query($query);
if (!$result)
  echo "query failed";
$beschikbare_maanden = array();
while (list($jaar, $maand) = mysql_fetch_array($result))
{
?>
      <tr>
        <td><?php echo "$maanden[$maand] $jaar"; ?></td>
        <td><button onclick="window.location.href=myself + '?toon&jaar=<?php echo $jaar; ?>&maand=<?php echo $maand; ?>'; return false;">Tonen</button></td>
        <td><button onclick="window.location.href=myself + '?verwijder&jaar=<?php echo $jaar; ?>&maand=<?php echo $maand; ?>'; return false;">Verwijderen</button></td>
      </tr>
<?php
}
?>
      <tr>
        <td>
          <select id="nieuwmaand">
<?php
for ($idx = 1; $idx <= 12; ++$idx)
{
?>
            <option value="<?php echo $idx; ?>"><?php echo $maanden[$idx]; ?></option>
<?php
}
?>
          </select>
        </td>
        <td>
          <input type="text" id="nieuwjaar" value="<?php echo $thisjaar; ?>" style="width: 50px;"></input>
        </td>
        <td>
          <button onclick="var jaar = document.getElementById('nieuwjaar').value; var maand = document.getElementById('nieuwmaand').value; window.location.href=myself + '?toon&jaar=' + jaar + '&maand=' + maand; return false;">Tonen</button>
        </td>
      </tr>
    </table>
  </body>
</html>
