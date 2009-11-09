(function($) {
    $.fn.Marqee = function(speeds, timeout) {
        var flag = true;
        var $scroll = $(this);
        var ph = $scroll.outerHeight();
        var ch = 0;
        var timeid;
        var step = 1;
        var liHeight = $scroll.find("dd").height();
        $scroll.scrollTop(0);
        $scroll.children().each(function() { ch += $(this).outerHeight(); });

        $scroll.append($scroll.html());
        var showLi = $scroll.find("dl:eq(1)");
        $(showLi).find("dt").show();
        $(showLi).find("dd").hide();
        var s = function() {
            var t = $scroll.scrollTop();
            if (t >= ch) {
                showLi = $scroll.find("dl:first");
                $scroll.find("dt").hide();
                //$scroll.find("dd").show();
                $(showLi).find("dt").show();
                $(showLi).find("dd").hide();
            }
            $scroll.scrollTop((t >= ch ? 0 : t) + step);

            if ($scroll.scrollTop() % liHeight == 0) _pause();
        }
        timeid = setInterval(s, speeds);


        function _pause() {
            step = 0;
            if (flag) {
                setTimeout(function() {
                    $(showLi).find("dd").show();
                    showLi = $(showLi).next("dl:first");
                    if (!showLi) {
                        showLi = $scroll.find("dl:first");
                    }
                    $scroll.find("dt").hide();
                    //$(showLi).find("dt").show();
                    $(showLi).find("dt").show();
                    $(showLi).find("dd").hide();
                    flag = true;
                    step = 1;
                }, timeout);
            }
            flag = false;
        }

        $scroll.find("dl").mouseover(function() {
            $scroll.find("dt").hide();
            $(this).find("dt").show();
            //$(this).find("dt").show();
            $(this).find("dd").hide();
            clearInterval(timeid);
        });


        $scroll.find("dl").mouseout(function() {
            $scroll.find("dt").hide();
            $(this).find("dd").show();
            $(showLi).find("dt").show();
            //$(showLi).find("dt").show();
            $(showLi).find("dd").hide();
            timeid = setInterval(s, speeds);
        });


    }
}
)(jQuery);
