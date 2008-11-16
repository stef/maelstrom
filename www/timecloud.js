var cstart=0;
var window_size = 30;
var steps = 1;
var timeout=200;
var play=0;
var frames=[];
var oneday=24*60*60*1000;
var tags=[];
var sparkline=[];
var sparklineStyle={ type:'line', lineColor:'Navy', spotColor:'red', height:'30px', width:'800px' };

function toSliderScale(num) {
   return (num/frames.length)*100;
}

function listToDict(lst) {
   var dict=[];
   // convert tags into list for drawTagcloud
   for ( tag in lst) {
      dict.push([tag, lst[tag].count, lst[tag].currentDate]);
   }
   return dict;
}

function drawSparkline(data,target,style) {
   // data might be sparse, insert zeroes into list
   var startdate=strToDate(data[0]['date']);
   var enddate=strToDate(data[data.length-1]['date']);
   var nextdate=startdate;
   var lst=[];
   for (id in data) {
      var curdate=strToDate(data[id]['date']);
      while(nextdate<curdate) {
         lst.push(0);
         nextdate=addDay(nextdate,1);
      }
      lst.push(parseInt(data[id]['count']));
      nextdate=addDay(nextdate,1);
   }
   $(target+' .startdate').text(dateToStr(startdate));
   $(target+' .enddate').text(dateToStr(enddate));
   $(target+' .sparkline').sparkline(lst, style);
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
      var curday=frames[i];
      var currentDate=curday[0];
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
         tags[tag].currentDate=currentDate;
         cnt+=count;
      }
      sparkline.push({'date': currentDate, 'count': cnt});
      i+=1;
   }
   return [tags, sparkline];
}

function loadTimecloud(data) {
   var nextdate;
   for (id in data) {
      // data received can be sparse, we fill any missing timesegments with
      // empty data 
      var curdate=strToDate(data[id][0]);
      while(nextdate && nextdate<curdate) {
         frames.push([dateToStr(nextdate),[]]);
         nextdate=addDay(nextdate,1);
      }
      nextdate=curdate;

      // push non-sparse data
      frames.push([data[id][0],data[id][1]]);
   }

   // draw first frame
   [tags,sparkline]=initTags();
   drawSparkline(sparkline,'#zoomGraph',sparklineStyle);
   drawTagcloud(listToDict(tags));
}

function animate() {
   var totalFrames=frames.length;

   // iterate over all frames
   if((cstart+window_size+steps)<totalFrames) {
      // substract all days tags leaving the sliding window
      var i=0;
      while(i<steps) {
         var curDay=frames[cstart+i][1];
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
         var curDay=frames[cstart+window_size+i][1];
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
            tags[item[0]].currentDate=frames[cstart+window_size+i][0];
         }
         sparkline.push({'date': frames[cstart+window_size+i][0], 'count': cnt});
         i+=1;
      }
      // advance cstart with steps
      cstart+=steps;
      $('#slide').slider("moveTo", toSliderScale(cstart), 0)
      $('#slide').slider("moveTo", toSliderScale(cstart+window_size), 1)
      drawSparkline(sparkline,'#zoomGraph',sparklineStyle);

      // draw tagcloud (current frame)
      drawTagcloud(listToDict(tags));
   }
   if(play) { 
      setTimeout("animate()", timeout); 
   }
}
