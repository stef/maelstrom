<?php
include_once("maelstrom.php");

if(isset($_GET['c'])) {
   $person=$_GET['c'];
} else {
   $person=$MAILBOXOWNER;
}
?>
<html>
   <head>
      <script type="text/javascript" charset="utf-8" src="timecloud/include/jquery.js"></script>
      <script type="text/javascript" charset="utf-8" src="timecloud/include/jquery.sparkline.js" ></script>
      <script type="text/javascript" charset="utf-8" src="timecloud/include/ui.core.js"></script>
      <script type="text/javascript" charset="utf-8" src="timecloud/include/ui.draggable.js"></script>
      <script type="text/javascript" charset="utf-8" src="timecloud/include/ui.slider.js"></script>
      <script type="text/javascript" charset="utf-8" src="timecloud/include/tagcloud.js"></script>
      <script type="text/javascript" charset="utf-8" src="timecloud/timecloud.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            var params="&c=<?php print urlencode($person)?>";
            var query="maelstrom.php?op=secondContacts"+params;
            $.getJSON(query,function(data) { $('#timecloud').timecloud({'timecloud':data})});
         })
      </script>
      <link href="timecloud/style.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
      <div id="content">
         <div id="header">
            <h1 id="pagetitle"><?print $person?></h1>
            <a href="contacts.php">back to contacts</a>
         </div>

            <div id="timecloud" />
         </div>
      </div>
   </body>
</html>
