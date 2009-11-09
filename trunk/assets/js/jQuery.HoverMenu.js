$.fn.PopMenu = function(options) {
    var $this = $(this);
    var defaults = { pup: null, left: 0, top: 0 };
    var opts = $.extend(defaults, options);
    var timeid = null;
    var clearid = function() { if (timeid) { clearTimeout(timeid); } };
    var hideDelegate = function() { timeid = setTimeout(function() { opts.pup.hide("fast"); }, 100) };
    $this.hover(function() {
        opts.pup.setLocation({ top: $this.offset().top + $this.height() + opts.top, left: $this.offset().left - opts.left });
        opts.pup.show();
        clearid();
    }, hideDelegate
    );
    opts.pup.hover(clearid, hideDelegate);
}