<?php
include_once("mailyze.php");

if(isset($_GET['c'])) {
   $person=$_GET['c'];
} else {
   $person=$MAILBOXOWNER;
}
?>
   <html>
   <head>
      <script type="text/javascript" src="maelstrom.js"></script>
      <script type="text/javascript" src="timecloud.js"></script>
      <script src="jquery.js" type="text/javascript" charset="utf-8"></script>
      <script src="jquery.sparkline.js" type="text/javascript" charset="utf-8"></script>
      <script type="text/javascript" src="tagcloud.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            var params="&c=<?php print urlencode($person)?>";
            var query="mailyze.php?op=contactMails"+params;
            $.getJSON(query,function(data) { drawSparkline(data,'#timegraph',sparklineStyle)});
            var query="mailyze.php?op=secondContacts"+params;
            $.getJSON(query, function(data) { drawTagcloud(data,'#content')} );
         });
      </script>
      <link href="style.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
      <!-- mikor leveleztem vele/mennyit sparkline over all time
      kik a kozos kontaktok? tagcloud
      subjects - simile -->

      <div id="content">
         <div id="header">
            <h1 id="pagetitle"><?print $person?></h1>
            <a href="timecloud.html">back to timecloud</a>
         </div>
         <div id="timegraph" class="timegraph">
            <div class="sparkline" > </div>
            <div class="dates">
               <div class="enddate"></div>
               <div class="startdate"></div>
            </div>
         </div>
         <div class="tagcloud"></div>
      </div>
   </body>
</html>
