$(document).ready(function() {

    $("#searchbtn").click(function() {
        var typeid = $("#categoryID").attr("categoryid");
        var keyword = $.trim($("#keyword").val());
        var para = "?";
        if (typeid != "0") {
            para += ("type=" + typeid)
        }
        if (keyword != "") {
            para += "&keyword=" + encodeURI(keyword);
        }
        window.location = para;

    });


    if ($("#keyword").val().length > 0) {
        $(".tongji").show();
    }

    if ($("#categoryID").val() != "鎵�鏈夊垎绫�") {
        $(".tongji").show();
    }

    if ($("#recordnum").text() == "0") {
        $(".tishi").hide();
        $(".tongji").hide();
        $(".ku").show();
    } else {
        $(".tishi").show();
    }



    $(".s_left").each(function(i) {
        $(this).hover(function() {

            var offset = $(".s_left").eq(i).offset();
            if ($(this).find("img").attr("src") == "assets/images/noimg80.gif") {
            } else {
                $("#LargePicDiv").find("img").attr("src", $(this).find("img").attr("largesrc").toString());
                $("#PicTitle").empty();
                $("#PicTitle").append($(".s_mid h1 a").eq(i).attr("title"));
                $("#LargePicDiv").find("a").attr("href", $(".s_mid h1 a").eq(i).attr("href"));
                $("#LargePicDiv").css("top", offset.top + 20); ;
                $("#LargePicDiv").css("left", offset.left + 82);
                $("#LargePicDiv").show().hover(function() { $(this).show(); }, function() { $(this).hide(); });
            }

        }, function() {
            $("#LargePicDiv").hide();
        });
    });

    var keywordReturn = $("#keyword").val();
    $(".s_mid").each(function() {
        var str = $(this).find("h1 a").text();
        str = str.replace(new RegExp("(" + keywordReturn + ")", "ig"), "<font color='#FF0000'>$1</font>");
        $(this).find("h1 a").html(str);
    });

    $("#keyword").keypress(function(e) {
        if (e.keyCode == 13) {
            $("#searchbtn")[0].click(); return false;
        }
    });

    $(document).mouseup(function() {
        //if ($("#categoryList:visable").length <= 0)
            $("#categoryList").hide();
    });    
});