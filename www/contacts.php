<?php
$timeconstraint='';
if(isset($_GET['start'])) {
   $timeconstraint="&start=".$_GET['start'];
}
if(isset($_GET['end'])) {
   $timeconstraint.="&end=".$_GET['end'];
}
?>
<html>
   <head>
      <script type="text/javascript" charset="utf-8" src="jquery.js"></script>
      <script type="text/javascript" charset="utf-8" src="jquery.sparkline.js" ></script>
      <script type="text/javascript" charset="utf-8" src="ui.core.js"></script>
      <script type="text/javascript" charset="utf-8" src="ui.draggable.js"></script>
      <script type="text/javascript" charset="utf-8" src="ui.slider.js"></script>
      <script type="text/javascript" charset="utf-8" src="tagcloud.js"></script>
      <script type="text/javascript" charset="utf-8" src="timecloud.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            var query="mailyze.php?op=contactTimeCloud<?php print $timeconstraint;?>";
            $.getJSON(query,function(data) { $('#timecloud').timecloud({'timecloud':data})});
         })
      </script>
      <link href="style.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
      <div id="content">
         <div id="header">
            <h1>TimeCloud</h1>
            <div id="timecloud" />
         </div>
      </div>
   </body>
</html>
