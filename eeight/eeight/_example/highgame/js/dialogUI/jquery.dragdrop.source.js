/*
* dragdrop
* version: 1.0.0 (01/19/2010)
* @ jQuery v1.4
*
* Copyright 2010 James [ jameskerr2009[at]gmail.com ] 
*  
*/
;(function($){
    $.fn.DragDrop = function(opts){//Main function
        var ps = $.extend({//the default param
            zIndex: 20,
            opacity: 0.7,
            handler: null, //moving handler
            onMove: function(){}, //the callback when the object moving
            onDrop: function(){}  //the callback when the object droped
        }, opts);
        var dragndrop = {
            drag: function(e){//drag the object
                if( !e ){
                    e=window.event; 
                }
                var dragData = e.data.dragData;
                dragData.target.css({//change the position of the object
                  "left": dragData.left + e.pageX - dragData.offLeft,
                  "top": dragData.top + e.pageY - dragData.offTop
                });
                dragData.handler.css({"cursor": 'move'});
                dragData.onMove(e);//callback user-defined
            },
            drop: function(e){//drop the object
                if( !e ){
                    e=window.event; 
                }
                var dragData = e.data.dragData;
                if( dragData.target.releaseCapture ){// release the event single lock on the object
                	 dragData.target.releaseCapture();
                }else if( window.captureEvents ){
                	 window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);
                }
                dragData.target.css(dragData.oldCss); //.css({ 'opacity': '' });
                dragData.handler.css('cursor', 'move');
                dragData.onDrop(e);
                $(document).unbind('mousemove', dragndrop.drag).unbind('mouseup', dragndrop.drop);
            }
        };
        return this.each(function(){//the drag object` action
            var me = this;
            var handler = null;
            if( typeof(ps.handler) == 'undefined' || ps.handler == null ){
                handler = $(me);
            }else{
                handler = ( typeof(ps.handler) == 'string' ? $(ps.handler, this) : ps.handle );
            }
            
            handler.bind('mouseover',function(){//bind mouseover event on handler
                $(this).css({"cursor": 'move'});
            });
            
            handler.bind('mousedown', {e: me}, function(s){//bing mousedown event on handler and send the data about the object
                var target = $(s.data.e);
                var temp   = target.offset();
                var oldTempCss = {//get the real position of the object
                    "left"  :  temp.left,
                    "top"   :  temp.top
                };
                if( target.css('position') != 'absolute' ){//change position to absoulte
                    try{
                        target.css(oldTempCss);
                    }catch(ex){ }
                    target.css('position', 'absolute');
                }
                var oldCss = {};
                s.preventDefault(); //cancel brower default action
                if( target.setCapture ){//lock single lock on the object
                	  target.setCapture();
                }else if( window.captureEvents ){
                	  window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);
                }
                oldCss.cursor  = target.css('cursor') || 'default';
                oldCss.opacity = target.css('opacity') || 1;
                var dragData   = {
                    left:    parseInt((oldTempCss.left || target.css('left') || 0),10),
                    top :    parseInt((oldTempCss.top  || target.css('top')  || 0),10),
                    width :  target.width()  || target.css('width'),
                    height:  target.height() || target.css('height'),
                    offLeft: s.pageX,
                    offTop:  s.pageY,
                    oldCss:  oldCss,
                    onMove:  ps.onMove,
                    onDrop:  ps.onDrop,
                    handler: handler,
                    target:  target
                }
                target.css('opacity', ps.opacity);//change the object`s opacity when it is moving
                $(document).bind('mousemove', { dragData: dragData }, dragndrop.drag)
                .bind('mouseup', { dragData: dragData }, dragndrop.drop);
            });
        });
    }
})(jQuery); 