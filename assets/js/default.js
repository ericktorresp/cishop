$(function() {
    $(document).pngFix();
    $(".kuaijie li>a").each(function() {
        var pos = $(this).position();
        $(this).parent().children('div').css({ "left": (pos.left + 130) + 'px', "top": pos.top });
        $(this).click(function() {
            $(this).blur();
            $(".kuaijie>ul>li").removeClass("tl");
            $(this).parent().addClass("tl");
            $(".kuaijie div").animate({ width: 0, height: 0, left: (pos.left + 127) + 'px', top: pos.top + 'px' }, 'fast', function() { $(this).css('display', 'none') });
            $(this).parent().children('div').animate({ width: 450, height: 199, left: (pos.left + 148) + 'px', top: '150px' }, 'fast');
        });
    });
    $(".close").click(function() {
        $(this).parents('li').removeClass("tl");
        var pos = $(this).parents('li').children('a').eq(0).position();
        $(this).parents('li').children('div').animate({ width: 0, height: 0, left: (pos.left + 127) + 'px', top: pos.top + 'px' }, 'fast', function() { $(this).css('display', 'none') });

    });



    $(".what dt").hide();
    $(".what").Marqee(60, 3000);
    $("#recommend li").each(function(i) {
        $(this).mouseover(function() {
            $("#recommend li").removeClass();
            $(this).addClass("xz");
            $(".shangpin:visible").hide();
            $(".shangpin").eq(i).css("display", "inline");
        });
    });

    $("#recommend li:eq(0)").mouseover();
});     