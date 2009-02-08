/* Maelstrom - visualizing email contacts
   Copyright© 2008-2009 Stefan Marsiske <my name at gmail.com> */
var Log = {
	elem: $('log'),
	write: function(text) {
		if(!this.elem) this.elem = $('log');
		this.elem.set('html', text);
	}
};

function init(q) {
  //computes page layout (not a library function, used to adjust some css thingys on the page)
  Infovis.initLayout();

  //Get width and height properties for the canvas container.
  var infovis = $('infovis');
  var w = infovis.offsetWidth, h = infovis.offsetHeight;
  var fStyle, sStyle, lineWidth;
  //Create a new canvas instance.
  var canvas = new Canvas('mycanvas', {
     //Where to inject the canvas. Any HTML container will do.
     'injectInto':'infovis',
     //Width and height of canvas, default's to 200.
     'width': w,
     'height':h,
     //Canvas styles.
     'styles': {
     'fillStyle': '#ddd',
     'strokeStyle': '#ddd'
     },

     //Add a background canvas to draw the main circle.
     'backgroundCanvas': {
         'styles': {
         'fillStyle': '#ccc',
         'strokeStyle': '#ccc'
         },

         'impl': {
         'init': $empty,
             'plot': function(canvas, ctx) {
                ctx.beginPath();
                ctx.arc(0, 0, ((w < h)? w : h) / 2, 0, Math.PI*2, true);
                ctx.stroke();
                ctx.closePath();
             }
         }
     }
  });

  var ht= new Hypertree(canvas, {
     nodeId: "",  
     nodeName: "",  
     onBeforeCompute: function(node) {
         Log.write("centering " + node.name + "...");
         this.nodeId = node.id;
         this.nodeName = node.name;
      },

      getName: function(node1, node2) {
         for(var i=0; i<node1.data.length; i++) {
            var dataset = node1.data[i];
            if(dataset.key == node2.name) return dataset.value;
         }

         for(var i=0; i<node2.data.length; i++) {
            var dataset = node2.data[i];
            if(dataset.key == node1.name) return dataset.value;
         }
      },

      onCreateLabel: function(domElement, node) {
         var d = $(domElement);
         d.set('tween', { duration: 300 }).set('html', node.name).setOpacity(0.8).addEvents({

            //Call the "onclick" method from the hypertree to move the hypertree correspondingly.
            //This method takes the native event object. Since Mootools uses a wrapper for this
            //event, I have to put e.event to get the native event object.
            'click': function(e) {
               ht.onClick(d.id);
            },

            'mouseenter': function() {
               d.tween('opacity', 1);
            },

            'mouseleave': function() {
               d.tween('opacity', 0.8);
            }
         });
       },

      //Take the left style property and substract half of the label actual width.
      onPlaceLabel: function(tag, node) {
         var width = tag.offsetWidth;
         var intX = tag.style.left.toInt();
         intX -= width/2;
         tag.style.left = intX + 'px';
      },

      //This method searches for nodes that already
      //existed in the visualization and sets the new node's
      //id to the previous one. That way, all existing nodes
      //that exist also in the new data won't be deleted.
      preprocessTree: function(json) {
         var ch = json.children;
         var getNode = function(nodeName) {
            for(var i=0; i<ch.length; i++) {
               if(ch[i].name == nodeName) return ch[i];
            }
            return false;
         };
         json.id = ht.graph.getNode(json.name); //ht.root;
         var root = ht.graph.getNode(ht.root);
         GraphUtil.eachAdjacency(root, function(elem) {
            var nodeTo = elem.nodeTo, jsonNode = getNode(nodeTo.name);
            if(jsonNode) jsonNode.id = nodeTo.id;
         });
      },

      reloadGraph: function() {
         var that = this, id = this.nodeId, name = this.nodeName;
         Log.write("requesting info...");
         var jsonRequest = new Request.JSON({
            'url': URLTEMPLATE + encodeURIComponent(name),
            onSuccess: function(json) {
               Log.write("morphing...");
               //Once me received the data we preprocess the ids of the nodes
               //received to match existing nodes in the graph and perform a
               //morphing operation.
               that.preprocessTree(json);
               GraphOp.morph(ht, json, {
                  'id': id,
                  'type': 'fade',
                  'duration':2000,
                  hideLabels:true,
                  onComplete: function() {
                     Log.write('done');
                  },
                  onAfterCompute: $empty,
                  onBeforeCompute: $empty
               });
            },
            onFailure: function() {
               Log.write("sorry, the request failed");
            }
         }).get();
      },

      refreshDetails: function() {
         var node = GraphUtil.getClosestNodeToOrigin(ht.graph, "pos");
         var that = this;
         //set details for this node.
         var html = "<h4>" + node.name + "</h4><b>Connections:</b>";
         html += "<ul>";
         GraphUtil.eachAdjacency(node, function(adj) {
            var child = adj.nodeTo;
            if(child.data && child.data.length > 0) {
               html += "<li>" + child.name + "</li>"; //+ " " + "<div class=\"relation\">(relation: " + that.getName(node, child) + ")</div></li>";
            }
         });
         html+= "</ul>";
         $('inner-details').set("html", html);

         //hide labels that aren't directly connected to the centered node.
         var GPlot = GraphPlot;
         GraphUtil.eachNode(ht.graph, function(elem) {
            if(elem.id != node.id && !node.adjacentTo(elem)){
               GPlot.hideLabel(elem);
            }
         });
      },
      onAfterCompute: function() {
         var node = GraphUtil.getClosestNodeToOrigin(ht.graph, "pos");
         this.nodeName=node.name;
         this.nodeId=node.id;
         this.refreshDetails();

         this.reloadGraph();
      }
   });

   window.addEvent('domready', function() {
      // optional: set an "onclick" event handler on the canvas tag to animate the tree.
      var mycanvas = $('mycanvas');
      var size = canvas.getSize();
      mycanvas.addEvent('click', function(e) {
         var pos = mycanvas.getPosition();
         var s = Math.min(size.width, size.height) / 2;
         ht.move({
            'x':  (e.page.x - pos.x - size.width  / 2) / s,
            'y':  (e.page.y - pos.y - size.height / 2) / s
         });
      });
      Log.write("Loading data...");
      new Request.JSON({
         'url':q,
         onSuccess: function(json) {
            Log.write("calculating graph...");
            //load data
            ht.loadTreeFromJSON(json);
            //compute positions then plot.
            ht.refresh();
            ht.controller.refreshDetails();
            Log.write("done");
         },
         onFailure: function() {
            Log.write("failed!");
         }
      }).get();
	});

}
