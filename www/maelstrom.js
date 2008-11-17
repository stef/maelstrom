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

