<?php
$timeconstraint='';
if(isset($_GET['start'])) {
   $timeconstraint="&start=".$_GET['start'];
}
if(isset($_GET['end'])) {
   $timeconstraint.="&end=".$_GET['end'];
}

if($_GET['op']=="timecloud" or $_GET['op']=="volume") { 
   //header("Content-type: text/plain");
   //print json_encode(contactTimeCloud($db));
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
      <script src="jquery.js" type="text/javascript" charset="utf-8"></script>
      <script src="jquery.sparkline.js" type="text/javascript" charset="utf-8"></script>
      <script type="text/javascript" src="ui.core.js"></script>
      <script type="text/javascript" src="ui.draggable.js"></script>
      <script type="text/javascript" src="ui.slider.js"></script>
      <script type="text/javascript" src="tagcloud.js"></script>
      <script type="text/javascript" src="timecloud.js"></script>
      <script type="text/javascript">
         $(document).ready(function() {
            $("#pause").click(function () { $(this).val(togglePlay()); });
            $("#step").click(function () { animate(); });
            var query="delicious.php?op=volume'.$timeconstraint.'";
            $.getJSON(query,function(data) { drawSparkline(data,"#overviewGraph",sparklineStyle)});
            var query="delicious.php?op=timecloud'.$timeconstraint.'";
            $.getJSON(query,function(data) { loadTimecloud(data,"#timecloud")});
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
</html>';
?>
