<?php
if (isset($_GET{"id"}))
  $id = $_GET{"id"};
else
{
  if (isset($_GET{"hve_test"}))
    printf("<p>No id</p>");
  else
  {
    header("Content-type: image/png");
    $im = @imagecreate(10, 10) or die("Cannot Initialize new GD image stream");
    $background_color = imagecolorallocate($im, 255, 0, 0);
    imagepng($im);
    imagedestroy($im);
    //die("incorrect");
  }
  return;
}

if (isset($_GET{"pass"}))
  $pass = $_GET{"pass"} + 0;
else
{
  if (isset($_GET{"hve_test"}))
    printf("<p>No pass</p>");
  else
  {
    header("Content-type: image/png");
    $im = @imagecreate(10, 10) or die("Cannot Initialize new GD image stream");
    $background_color = imagecolorallocate($im, 255, 0, 0);
    imagepng($im);
    imagedestroy($im);
    //die("incorrect");
  }
  return;
}

$check_pass = 0;
for ($idx = 0; $idx < strlen($id); ++$idx)
{
  $num = ord(substr($id, $idx, 1));
  $check_pass += $num * $num;
}
while ($check_pass < 4242)
  $check_pass *= $check_pass;
$check_pass %= 4242;
if ($check_pass != $pass)
{
  if (isset($_GET{"hve_test"}))
    printf("<p>$check_pass != $pass</p>");
  else
  {
    header("Content-type: image/png");
    $im = @imagecreate(10, 10) or die("Cannot Initialize new GD image stream");
    $background_color = imagecolorallocate($im, 0, 255, 0);
    imagepng($im);
    imagedestroy($im);
    //die("incorrect pass");
  }
  return;
}

$db_conn = mysql_connect("localhost", "3koningen_be", "wbEwbfgb") or die("could not connect to database server");
mysql_select_db("3koningen_be") or die("could not find database");
$query = "SELECT count FROM counters WHERE id='$id'";
$result = mysql_query($query);
if (mysql_num_rows($result) == 1)
{
  list($getal) = mysql_fetch_array($result);
  ++$getal;
  $query = "UPDATE counters SET count=$getal WHERE id='$id'";
  $result = mysql_query($query);
}
else
{
  $query = "INSERT INTO counters (id, count) VALUES ('$id', 1)";
  $result = mysql_query($query);
  $getal = 1;
}

while (strlen("$getal") < 5)
  $getal = " " . $getal;
if (isset($_GET{"hve_test"}))
  printf("<p>$getal visits</p>");
else
{
  header("Content-type: image/png");
  $im = @imagecreate(50, 20) or die("Cannot Initialize new GD image stream");
  $background_color = imagecolorallocate($im, 255, 255, 255);
  imagefill($im, 0, 0, $background_color);
  $background_color = imagecolortransparent($im, $background_color);
  $text_color = imagecolorallocate($im, 0, 0, 0);
  imagestring($im, 5, 2, 2, $getal, $text_color);
  imagepng($im);
  imagedestroy($im);
}
?>
