var cstart=0;
var startdate="2004-02-01";
var start = new Date("Feb 1, 2004");
var end = new Date("Nov 12, 2008");
var window_size = 30;
var steps = 1;
var timeout=200;
var play=0;
var contactVolumes=[];
var oneday=24*60*60*1000;
var tags=[];
var sparkline=[];

function toSliderScale(num) {
   return (num/contactVolumes.length)*100;
}

function dateToStr(date) {
   var d  = date.getDate();
   var day = (d < 10) ? '0' + d : d;
   var m = date.getMonth() + 1;
   var month = (m < 10) ? '0' + m : m;
   var yy = date.getYear();
   var year = (yy < 1000) ? yy + 1900 : yy;
   return(year + "-" + month + "-" + day);
}

function strToDate(string) {
   var frgs=string.split("-");
   return(new Date(frgs[0],frgs[1]-1,frgs[2]));
}

function addDay(d,n) {
   return new Date(d.getTime() + n*oneday);
}

// TODO implement own smooth rendering
// style has format: {fontSize:'1em',padding:'0',position:'absolute',lineHeight:'1'};
function getRenderedStrSize(str,style) {
   pa=document.body;
   var who= document.createElement('div');
   for(var p in style){
      who.style[p]= style[p];
   }
   who.style[visibility]= 'hidden';
   who.appendChild(document.createTextNode(str));
   pa.appendChild(who);
   var fs= [who.offsetWidth,who.offsetHeight];
   pa.removeChild(who);
   return fs;
}

function loadSparkline(data,target) {
   var nextdate=strToDate(data[0]['delivered']);
   var mails=[];
   for (id in data) {
      var curdate=strToDate(data[id]['delivered']);
      while(nextdate<curdate) {
         mails.push(0);
         nextdate=addDay(nextdate,1);
      }
      mails.push(parseInt(data[id]['count']));
      nextdate=addDay(nextdate,1);
   }
   $(target).sparkline(mails, { type:'line', barColor:'orange', spotColor:'red', height:'30px', width:'800px' });
}

function drawTagcloud(data) {
   var tc;
   tc=TagCloud.create();
   for (id in data) {
      var timestamp;
      if(data[id][2]) {
         timestamp=strToDate(data[id][2]);
      }
      if(parseInt(data[id][1]) ) {
            // name
         tc.add(data[id][0],
            // count
            parseInt(data[id][1]),
            'contact.php?c='+data[id][0], //name
            timestamp); // epoch
      }
   }
   tc.loadEffector('CountSize').base(24).range(12);
   tc.loadEffector('DateTimeColor');
   tc.setup('tagcloud');
}

// calculate a list of tags by summing all corresponding tags in timetags from
// (cstart,cstart+window_size[
// result is a dict of { tags : counts } 
function initTags() {
   var i=0;
   var tags=[];
   var sparkline=[];
   // iterate over window_size
   while(i<window_size) {
      // fetch current day
      var curday=contactVolumes[i];
      var lastseen=curday[0];
      //iterate over tags in day
      var item;
      var cnt=0;
      for(item in curday[1]) {
         var tag=curday[1][item][0];
         var count=parseInt(curday[1][item][1]);
         if(tags[tag]) {
            // add count
            tags[tag].count+=count;
         } else {
            // add tag
            tags[tag]=[];
            tags[tag].count=count;
         }
         tags[tag].lastseen=lastseen;
         cnt+=count;
      }
      sparkline.push({'delivered': lastseen, 'count': cnt});
      i+=1;
   }
   return [tags, sparkline];
}

function listToDict(lst) {
   var tagcloud=[];
   // convert tags into list for drawTagcloud
   for ( tag in lst) {
      tagcloud.push([tag, lst[tag].count, lst[tag].lastseen]);
   }
   return tagcloud;
}

function loadTimecloud(data) {
   var nextdate=start;
   for (id in data) {
      // data received can be sparse, we fill any missing timesegments with
      // empty data 
      var curdate=strToDate(data[id][0]);
      while(nextdate<curdate) {
         contactVolumes.push([dateToStr(nextdate),[]]);
         nextdate=addDay(nextdate,1);
      }
      nextdate=addDay(nextdate,1);

      // push valid data
      contactVolumes.push([data[id][0],data[id][1]]);
   }

   // draw first frame
   [tags,sparkline]=initTags();
   startdate=contactVolumes[cstart][0];
   enddate=contactVolumes[cstart+window_size][0];
   $('#startdate').text(startdate);
   $('#enddate').text(enddate);
   loadSparkline(sparkline,'#sparkline');
   var cend=toSliderScale(window_size);
   $('#slide').slider({ handles: [{start: 0, id:'handle1'}, {start: cend, id:'handle2'}],
         range: true, change: function(e,ui) { console.log(ui.range); } });
   drawTagcloud(listToDict(tags));
}

function animate() {
   var totalFrames=contactVolumes.length;

   // iterate over all frames
   if((cstart+window_size+steps)<totalFrames) {
      // substract all days tags leaving the sliding window
      var i=0;
      while(i<steps) {
         var curDay=contactVolumes[cstart+i][1];
         for (tag in curDay) {
            var item=curDay[tag];
            tags[item[0]].count-=parseInt(item[1]);
            if(tags[item[0]].count<=0) {
               delete tags[item[0]];
            }
         }
         i+=1;
      }
      sparkline.splice(0,steps);
         
      // add days cstart+window_size - cstart+window_size+steps
      var i=0;
      while(i<steps) {
         var curDay=contactVolumes[cstart+window_size+i][1];
         var tag;
         var cnt=0;
         for (tag in curDay) {
            var item=curDay[tag];
            if(tags[item[0]]) {
                  tags[item[0]].count+=parseInt(item[1]);
            } else {
               tags[item[0]]=new Array();
               tags[item[0]].count=parseInt(item[1]);
            }
            cnt+=parseInt(item[1]);
            tags[item[0]].lastseen=contactVolumes[cstart+window_size+i][0];
         }
         sparkline.push({'delivered': contactVolumes[cstart+window_size+i][0], 'count': cnt});
         i+=1;
      }
      // advance cstart with steps
      cstart+=steps;
      $('#startdate').text(contactVolumes[cstart][0]);
      $('#enddate').text(contactVolumes[cstart+window_size][0]);
      $('#slide').slider("moveTo", toSliderScale(cstart), 0)
      $('#slide').slider("moveTo", toSliderScale(cstart+window_size), 1)
      loadSparkline(sparkline,'#sparkline');

      // draw tagcloud (current frame)
      drawTagcloud(listToDict(tags));
   }
   if(play) { 
      setTimeout("animate()", timeout); 
   }
}
