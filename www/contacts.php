<?php
/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */
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
      <!--[if IE]><script language="javascript" type="text/javascript" src="/timecloud/include/excanvas.js"></script><![endif]-->
      <script type="text/javascript" charset="utf-8" src="/timecloud/include/jquery.js"></script>
      <script type="text/javascript" charset="utf-8" src="/timecloud/include/jquery.sparkline.js" ></script>
      <script type="text/javascript" charset="utf-8" src="/timecloud/include/jquery-ui-p.min.js"></script>
      <script type="text/javascript" charset="utf-8" src="/timecloud/include/jquery.event.wheel.js"></script>
      <script type="text/javascript" charset="utf-8" src="/timecloud/include/tagcloud.js"></script>
      <script type="text/javascript" charset="utf-8" src="/timecloud/timecloud.js"></script>
      <style type="text/css">
         * { margin:  0; padding: 0; font-family: Georgia,Garamond,"Times New Roman",serif;}
         #content {width: 90%; margin-left: auto ; margin-right: auto ;}
      </style>
      <script type="text/javascript">
         $(document).ready(function() {
            var query="maelstrom.php?op=contactTimeCloud<?php print $timeconstraint;?>";
            $.getJSON(query,function(data) { $('#timecloud').timecloud({
                    'timecloud':data,
                    'urlprefix': 'contact.php?c='});});
         })
      </script>
      <link href="/timecloud/style.css" rel="stylesheet" type="text/css" />
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
