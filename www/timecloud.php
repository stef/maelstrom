<?php
$timeconstraint;
if(isset($_GET['start'])) {
   $timeconstraint="&start=".$_GET['start'];
}
if(isset($_GET['end'])) {
   $timeconstraint.="&end=".$_GET['end'];
}
?>
<html>
   <head>
      <script src="jquery.js" type="text/javascript" charset="utf-8"></script>
      <script src="jquery.sparkline.js" type="text/javascript" charset="utf-8"></script>
      <script type="text/javascript" src="ui.core.js"></script>
      <script type="text/javascript" src="ui.slider.js"></script>
      <script type="text/javascript" src="tagcloud.js"></script>
      <script type="text/javascript" src="maelstrom.js"></script>
      <script type="text/javascript" src="timecloud.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            $("#pause").click(function () { $(this).val(togglePlay()); });
            $("#step").click(function () { animate(); });
            var query="mailyze.php?op=mailFrequency<?php print $timeconstraint;?>";
            $.getJSON(query,function(data) { drawSparkline(data,'#overviewGraph',sparklineStyle)});
            var query="mailyze.php?op=contactTimeCloud<?php print $timeconstraint;?>";
            $.getJSON(query,function(data) { loadTimecloud(data,'#timecloud')});
         })
      </script>
      <link href="style.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
      <div id="content">
         <div id="header">
            <h1>TimeCloud</h1>
            <input type="submit" id="pause" value=">" />
            <input type="submit" id="step" value="+" />
         </div>
         <div id="overviewGraph" class="timegraph">
            <div class="sparkline" > </div>
            <div class="dates">
               <span class="enddate" ></span>
               <span class="startdate"></span>
            </div>
            <div class="ui-slider">
              <div class="ui-slider-handle left"></div>
              <div class="ui-slider-handle right"></div>
            </div>
         </div>
         <div id="timecloud">
            <div class="timegraph">
               <div class="sparkline" > </div>
               <div class="dates">
                  <div class="enddate"></div>
                  <div class="startdate"></div>
               </div>
            </div>
            <div class="tagcloud"></div>
         </div>
      </div>
   </body>
</html>
