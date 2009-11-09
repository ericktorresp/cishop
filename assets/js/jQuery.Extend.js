$.fn.setLocation = function(options) {
    var defaults = {
        position: "absolute",
        zIndex: 1000,
        left: 0,
        top: 0
    };
    var opts = $.extend(defaults, options);
    this.css(opts);
    return this;
}