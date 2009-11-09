/// <reference path="../jquery-1.2.6.js" />
/// <reference path="jQuery.extend.js" />

$.fn.Drag = function(options) {
    var defaults = { modal: true, center: true, drag: null, clbtn: null, bgcss: "" };
    var opts = $.extend(defaults, options);
    bg = $("<div></div>");
    var $this = new Object();
    $this[opts.drag] = $(this);
    var posX; var posY; var bg;
    if (opts.clbtn) {
        $("#" + opts.clbtn).click(function() { close(); });
    }
    var mouseup = function() {
        $(this).unbind();
        $this[opts.drag].css({ zIndex: 1001 });
    };
    var move = function(e) {
        var x = e.clientX - posX;
        var y = e.clientY - posY;
        $this[opts.drag].setLocation({ top: y, left: x, zIndex: 1002 });
        return false;
    };
    this.close = function() {
        close();
    };
    this.open = function(modal) {
        opts.modal = modal;
        if (opts.modal) {
            bg.width($(document).width()).height($(document).height()).appendTo("body").show();
            if (opts.bgcss) { bg.addClass(opts.bgcss); }
            bg.setLocation({ zIndex: 1000 });
        }
        open();
        $this[opts.drag].show();
        $(window).resize(open);
        $(window).scroll(open);
    };
    var open = function() {
        var x = document.documentElement.clientWidth/2 - document.documentElement.scrollLeft - $this[opts.drag].width()/2;
        var y = document.documentElement.clientHeight/2 + document.documentElement.scrollTop - $this[opts.drag].height()/2;
        
        x = Math.round(x); y = Math.round(y);
        $this[opts.drag].setLocation({ top: y, left: x, zIndex: 1001 }).show();
    };
    var close = function() {
        $this[opts.drag].hide();
        bg.hide();
        $(window).unbind("resize", open);
        $(window).unbind("scroll", open);
    };
    $("#" + opts.drag).mousedown(
            function(e) {
                posX = e.clientX - $this[opts.drag].offset().left;
                posY = e.clientY - $this[opts.drag].offset().top;
                $(document).mousemove(move);
                $(document).mouseup(mouseup);
                document.onselectstart = function() { return false; };
                return false;
            }
        );
    ;
    return this;
}