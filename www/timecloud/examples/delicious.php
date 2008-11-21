<?php
$timeconstraint='';
if(isset($_GET['start'])) {
   $timeconstraint="&start=".$_GET['start'];
}
if(isset($_GET['end'])) {
   $timeconstraint.="&end=".$_GET['end'];
}

if($_GET['op']=="timecloud" or $_GET['op']=="volume") { 
   require('phpdelicious/php-delicious.inc.php');
   require('deliciouslogin.inc.php');

   $days=array();
   $oDelicious = new PhpDelicious(DELICIOUS_USER, DELICIOUS_PASS);
   $aPosts = $oDelicious->GetAllPosts();
   if ($aPosts) {
      foreach ($aPosts as $aPost) {
         list($date,$time)=explode(" ",$aPost['updated']);
         if(!isset($days[$date])) {
            $days[$date]=array('count'=>1, 'tags'=>array());
         } else {
            $days[$date]['count']+=1;
         }
         foreach($aPost['tags'] as $tag) {
            if(!isset($days[$date][$tag])) {
               $days[$date]['tags'][$tag]=1;
            } else {
               $days[$date]['tags'][$tag]+=1;
            }
         }
      }
      if($_GET['op']=="timecloud") {
         $result=array();
         ksort($days);
         foreach($days as $day => $opt) {
            $tags=$opt['tags'];
            $tmp=array();
            foreach(array_keys($tags) as $tag) {
               $tmp[]=array($tag,$tags[$tag]);
            }
            $result[]=array($day,$tmp);
         }
         //header("Content-type: text/plain");
         //print print_r($days); 
         print json_encode($result);
      } else { //$_GET['op']=="volume") 
         $result=array();
         ksort($days);
         foreach($days as $day => $opt) {
            $result[]=array('count'=>$opt['count'], 'date'=>$day);
         }
         //header("Content-type: text/plain");
         //print print_r($days); 
         print json_encode($result);
      }
   } else {
      echo $oDelicious->LastErrorString();
   }
} else print '
<html>
   <head>
      <script type="text/javascript" charset="utf-8" src="../include/jquery.js"></script>
      <script type="text/javascript" charset="utf-8" src="../include/jquery.sparkline.js" ></script>
      <script type="text/javascript" charset="utf-8" src="../include/ui.core.js"></script>
      <script type="text/javascript" charset="utf-8" src="../include/ui.draggable.js"></script>
      <script type="text/javascript" charset="utf-8" src="../include/ui.slider.js"></script>
      <script type="text/javascript" charset="utf-8" src="../include/tagcloud.js"></script>
      <script type="text/javascript" charset="utf-8" src="../timecloud.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            var query="delicious.php?op=timecloud'.$timeconstraint.'";
            $.getJSON(query,function(data) { $("#loading").hide(); $("#timecloud").timecloud({"timecloud":data}); });
         })
      </script>
      <link href="../style.css" rel="stylesheet" type="text/css" />
   </head>
   <body>
      <div id="content">
         <div id="header">
            <h1>TimeCloud</h1>
            <div id="timecloud" /> 
            <div id="loading" style="height: 100px; background: transparent url(spinner.gif) no-repeat scroll center center; text-align: center;"><span>Loading...</span></div>
         </div>
      </div>
   </body>
</html>';
?>
