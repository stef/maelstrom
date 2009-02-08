<?php
/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */
if(isset($_GET['c'])) {
  $person=$_GET['c'];
} else {
  die;
}
$timeconstraint='';
if(isset($_GET['start'])) {
   $timeconstraint="&start=".$_GET['start'];
}
if(isset($_GET['end'])) {
   $timeconstraint.="&end=".$_GET['end'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>
	Contact network
</title>
<link type="text/css" href="./css/style.css" rel="stylesheet" />
<link type="text/css" rel="stylesheet" href="./css/infovis.css" />
<link type="text/css" rel="stylesheet" href="./css/hypertree.css" />

<script type="text/javascript" src="./js/mootools-1.2.js"></script>


<!--[if IE]><script language="javascript" type="text/javascript" src="/timecloud/include/excanvas.js"></script><![endif]-->
<script language="javascript" type="text/javascript" src="./js/Hypertree.js"></script>
<script language="javascript" type="text/javascript" src="./js/infovis.js"></script>
<script language="javascript" type="text/javascript" src="./js/static1-hypertree.js"></script>
<script language="javascript" type="text/javascript" >
  var URLTEMPLATE='snet.php?<?php print "$timeconstraint&c=";?>';
</script>

</head>

<body onload="init('snet.php?<?php print "c=$person&$timeconstraint";?>');">

<div id="header">


</div>

<div id="left">
	<div id="details" class="toggler left-item">Details</div>
<div class="element contained-item">
	<div class="inner" id="inner-details">
	</div>
</div>


</div>
<div id="infovis"></div>
<div id="label_container"></div>
<div id="log"></div>

</body>
</html>
