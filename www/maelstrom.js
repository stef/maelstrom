var oneday=24*60*60*1000;

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

