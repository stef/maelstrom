<?php
/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */
include_once("maelstrom.php");

if(isset($_GET['c'])) {
   $person=$_GET['c'];
} else {
   $person=$MAILBOXOWNER;
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
      <script type="text/javascript">
         $(document).ready(function() {
            var params="&c=<?php print urlencode($person)?>";
            var query="maelstrom.php?op=contactOrgs"+params;
            $.getJSON(query,function(data) {
               $('#orgs').timecloud({
                   'timecloud': data,
                   'winSize': 0,
                   'urlprefix': 'org.php?o='})});
            var query="maelstrom.php?op=secondContacts"+params;
            $.getJSON(query,function(data) {
                $('#contacts').timecloud({
                   'timecloud':data,
                   'winSize': 0,
                   'urlprefix': 'contact.php?c='})});
         })
      </script>
      <style type="text/css">
         * { margin:  0; padding: 0; font-family: Georgia,Garamond,"Times New Roman",serif;}
         #content {width: 90%; margin-left: auto ; margin-right: auto ;}
      </style>
      <link href="/timecloud/style.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
      <div id="content">
         <div id="header">
            <h1 id="pagetitle"><?print $person?></h1>
            <a href="contacts.php">back to contacts</a>
         </div>
            <div id="orgs"></div>
            <div id="contacts"></div>
         </div>
      </div>
   </body>
</html>
