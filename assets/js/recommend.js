$(document).ready(function() {
    var ulScroll = $(".Slides");
    var liHeight = $(".Slides li img").outerHeight("Current");
    var destIndex = 0;
    var nowIndex = 0;
    var picNums = $(".Slides li").length;
    var IntervalID = null;
    var TimeOutID = null;
    var loop = true;
    var flag = true;

    function moveToPic(index) {
        flag = true;
        var nowScrollTop = ulScroll.scrollTop();
        $(".SlideTriggers li").removeClass();
        $(".SlideTriggers li:eq(" + index + ")").addClass("Current");
        if (nowScrollTop > index * liHeight) {
            IntervalID = setInterval(function() { maquee(1, index * liHeight, index); }, 1);
        }
        else if (nowScrollTop < index * liHeight) {
            IntervalID = setInterval(function() { maquee(3, index * liHeight, index); }, 1);
        }
    }

    function maquee(type, destHeight, index) {
        var nowScrollTop = ulScroll.scrollTop();
        if (nowScrollTop == destHeight) {
            nowIndex = index;
            clearInterval(IntervalID);
            if (loop) {
                pausePic(true);
            }
        } else {
            if (type == 1) {
                ulScroll.scrollTop(ulScroll.scrollTop() - Math.abs(destIndex - nowIndex) * 55)
            } else {
                ulScroll.scrollTop(ulScroll.scrollTop() + Math.abs(destIndex - nowIndex) * 55)
            }
        }
    }

    function pausePic(pauseType) {
        if (!flag)
            clearTimeout(TimeOutID);
        if (pauseType) {
            if (destIndex < picNums - 1)
            { destIndex++; }
            else { destIndex = 0; }
            flag = false;
            TimeOutID = setTimeout(function() { moveToPic(destIndex); }, 3000);
        }
        else {
            clearInterval(IntervalID);
            loop = false; flag = true;
            moveToPic(destIndex);
        }
    }

    $(".SlideTriggers li").each(function(i) { $(this).hover(function() { if (!flag) { clearTimeout(TimeOutID); } destIndex = i; pausePic(false); }, function() { loop = true; pausePic(true); }); }); //, function() { loop = true; pausePic(true); }); });
    $(".Slides").hover(function() {
        if (flag) { loop = false; }
        else {
            clearTimeout(TimeOutID);
        }
        flag = true;
    }, function() { loop = true; pausePic(true); });
    $(".SlideTriggers li:eq(0)").addClass("Current");
    pausePic(true);

    $(".ph dl").each(function() { $(this).mouseover(function() { $(".ph dl dt").hide(); $(".ph dl dd").show(); $(this).find("dd").hide(); $(this).find("dt").show(); }); });
    $(".ph dl:eq(0)").mouseover();
});