<?php
/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */
include_once("maelstrom.php");

if(isset($_GET['c'])) {
  $c=$_GET['c'];
} else {
  die;
}

?>
<html>
   <style type="text/css">
      * { margin:  0; padding: 0; font-family: Georgia,Garamond,"Times New Roman",serif;,color:#333}
      #content {width: 90%; margin-left: auto ; margin-right: auto ;}
      .person { display: block; padding-bottom: 1em;  margin-bottom: 1em; clear: right; background: #ddeeff}
      .bar {
          float: left;
          height: .8em;
          line-height: .8em;
          font-size: .7em;
          margin: 2px;
          padding: 2px;
          border-top: 1px solid black;
          border-bottom: 1px solid black;
      }
      .from { background: #B1D632 !important; }
      .to { background: orange !important; }
      .cc { background: #A6B7BF !important; }
      .vaxis { position: relative; font-size: .8em; text-align: right; width: 40px; left: -40px; }
      .min, .max { position: absolute; }
      .min { bottom: -27px; right:0px; }
      .max { top: 0px; right:0px; }
      .haxis { font-size: .8em;}
      .left {float: left;}
      .right {float: right;}
      </style>
      <link href="/timecloud/style.css" rel="stylesheet" type="text/css" />

      <!--[if IE]><script language="javascript" type="text/javascript" src="/timecloud/include/excanvas.js"></script><![endif]-->
      <script type="text/javascript" charset="utf-8" src="/timecloud/include/jquery.js"></script>
      <script type="text/javascript" charset="utf-8" src="jquery.sparkline.js" ></script>
      <script type="text/javascript">

      $(document).ready(function() {
          loadSparklines($('.person:first'));
        });

      function loadSparklines(target) {
        if(target[0]) {
          params="&c1=<?php print $c?>&c2="+target[0].id;
          query="maelstrom.php?op=getEdgeFrequency"+params;
          $.getJSON(query,function(data) {
              drawSparkline(data,$('.frequency',target));
              loadSparklines($(target).next(".person"));
            });
        }
      };

      function drawSparkline(data,target) {
        // data might be sparse, insert zeroes into list
        //var startdate = strToDate(data[0]['date']);
        //var enddate = strToDate(data[data.length-1]['date']);
        var startdate = strToDate("2006-01-01");
        var enddate = strToDate("2009-01-01");
        var nextdate = startdate;
        var lst = [];
        var min = Infinity;
        var max = -Infinity;
        for (id in data) {
          var curdate = strToDate(data[id]['date']);
          while(nextdate<curdate) {
            lst.push(0);
            nextdate = addDay(nextdate,1);
          }
          var val = parseInt(data[id]['count']);
          if(val>max) max = val;
          if(val<min) min = val;
          lst.push(val);
          nextdate = addDay(nextdate,1);
        }
        // fill dataset with 0 till the enddate
        while(nextdate<enddate) {
          lst.push(0);
          nextdate = addDay(nextdate,1);
        }
        $('.min',target).text(min);
        $('.max',target).text(max);
        $('.left',target).text(dateToStr(startdate));
        $('.right',target).text(dateToStr(enddate));
        $('.sparkline',target).sparkline(lst, {
          type:'line',
          lineColor:'Navy',
          height:'30px',
          chartRangeMin: '0',
          width: $('.sparkline',target).width() });
        $.sparkline_display_visible()
      };

      // helper function to cope with dates
      function dateToStr(dat) {
          var d  = dat.getDate();
          var day = (d < 10) ? '0' + d : d;
          var m = dat.getMonth() + 1;
          var month = (m < 10) ? '0' + m : m;
          var yy = dat.getYear();
          var year = (yy < 1000) ? yy + 1900 : yy;
          return(year + "-" + month + "-" + day);
      };

      // helper function to cope with dates
      function strToDate(str) {
          var frgs = str.split("-");
          return(new Date(frgs[0],frgs[1]-1,frgs[2]));
      };

      // helper function to cope with dates
      function addDay(d,n) {
          var oneday = 24*60*60*1000;
          return new Date(d.getTime() + n*oneday);
      };
      </script>
   </head>
   <body>
      <div id="content">
         <div id="header">
            <h1 id="pagetitle"><?print $c1?></h1>
            <a href="contacts.php">back to contacts</a>
         </div>
      <?php getEdges(); ?>
      </div>
   </body>
</html>

