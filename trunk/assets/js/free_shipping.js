$(document).ready(function() {
    var btnli = $(".left").find("li");
    btnli.each(function(index) {
        var str = "";
        if (index < 3) {
            str = "<span>" + (index + 1) + "</span><p>" + para[index]["title"] + "</p>";
        }
        else {
            str = "<b>" + (index + 1) + "</b><p>" + para[index]["title"] + "</p>";
        }
        $(this).html(str);


        $(this).click(function() {

            $(".right h2").text("Panli网友最爱去的" + para[index]["title"] + "网站");
            btnli.removeClass("xz");
            $(this).addClass("xz");
            $(".raning_dp").html("");
            for (var i = 0; i < para[index]["href"].length; i++) {
                $(".raning_dp").append("<dl class='p" + (i + 1).toString() + "'><dt><a href='" + para[index]["href"][i] + "' target='_blank'><img src='/~darkmoon/assets/images/free_shipping/logos/" + para[index]["pic"][i] + "' /></a></dt><dd><p><a href='" + para[index]["href"][i] + "' target='_blank'>" + para[index]["name"][i] + "</a></p><span><a href='" + para[index]["href"][i] + "' target='_blank'></a></span></dd></dl>");
            }
        });
    });
    $(btnli[0]).click();
});