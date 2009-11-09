function getViewportHeight() {
    if (window.innerHeight != window.undefined) return window.innerHeight;
    if (document.compatMode == 'CSS1Compat') return document.documentElement.clientHeight;
    if (document.body) return document.body.clientHeight;
    return window.undefined;
}


function InitP2() {
    $("#proAlert").attr("class", "").text("恭喜您！商品信息抓取成功，您可以修改购买数量和填写商品备注！");
    $("#productUrl").val("");
    $("#productName").val("").attr("class", "addpanel_k").removeAttr("disabled").unbind("focus").unbind("blur");
    $("#productPrice").val("").attr("class", "").removeAttr("disabled").unbind("focus").unbind("blur");
    $("#productSendPrice").val("").attr("class", "").unbind("focus");
    $("#productNum").val("1");
    $("#productImg").hide();
    $("#isAuction").hide();
    $("#productImg img").attr("src", "");
    $("#productRemark").attr("class", "addpanel_still").text("请选填颜色、尺寸等要求！");
    $("#successBtn").attr("disabled", "disabled").attr("class", "addpanel_next_no");
}

function p3Init() {
    $("#p3_img").attr("src", "../images/noimg80.gif");
}

function AddItemShow() {
    $(".addpanel_overlay").height($(document).height()).show();
    $(".addpanel_dialog").show();
    if ($("#p1 div").length == 0)
        $("#p1").load("assets/html/AddItemPanel1.html", function() { $("#p0").remove(); $("#p1").show(); $("#itemUrl").focus(); });
    else $("#itemUrl").focus();
    if ($("#p2 div").length == 0)
        $("#p2").load("assets/html/AddItemPanel2.html", function() { $("#p2").hide(); });
    if ($("#p3 div").length == 0)
        $("#p3").load("assets/html/AddItemPanel3.html", function() { $("#p3").hide(); });
}
function AddItemClose() {
    $(".addpanel_dialog").hide();
    $(".addpanel_overlay").hide();
    if ($("#p2 div").length >= 1) {
        $("#p2").hide();
        InitP2();
    }
    if ($("#p3 div").length >= 1) {
        $("#p3").hide();
        p3Init();
    }
    $(".addpanel_address_").attr("class", "addpanel_address");
    $("#itemUrl").removeAttr("disabled").val("");

    $("#promptInfo").attr("class", "addpanel_dhk").find("img").remove();
    $("#promptInfo p").text("请将您想代购商品的详细页网址粘贴到输入框中提交!");
    $("#addpanel_submit").removeAttr("disabled").attr("class", "addpanel_tijiao");

    $("#p1").show();
}

$("#closeBtn").click(AddItemClose);
var browser = navigator.appName;
var b_version = navigator.appVersion;
var version = parseFloat(b_version);
navigator.compitable

if (typeof document.body.style.maxHeight == "undefined") {
    //if ($.browser.msie && $.browser.version == "6.0") {
    $(".addpanel_dialog").css("position", "absolute").css("margin-top", "0px");
    var divY = (getViewportHeight() - $(".addpanel_dialog").outerHeight()) / 2;
    $(".addpanel_dialog").css("top", (divY + document.documentElement.scrollTop).toString());
    $(window).scroll(function() { $(".addpanel_dialog").css("top", divY + document.documentElement.scrollTop + ""); });
}



try {
    var uri = window.location;
    var url = uri.href;
    url = url.toLowerCase();
    $("#allPages li").removeClass();
    if (url.indexOf("/free_postage/") > 0) {
        $("#Free_postage").addClass("xt");
    } else if (url.indexOf("/see/") > 0) {
        $("#see").addClass("xt");
    } else if (url.indexOf("/panlirecommend/") > 0) {
        $("#PanliRecommend").addClass("xt");
    } else if (url.indexOf("/special/") > 0) {
        $("#Special").addClass("xt");
    } else if (url.indexOf("/discount/") > 0) {
        $("#Discount").addClass("xt");
    } else if (url.indexOf("localhost/~darkmoon") > 0 || url.length - url.indexOf("panli.com") <= 10) {
        $("#Default").addClass("xt");
    } else if (url.indexOf("/piece/") > 0) {
        $("#Piece").addClass("xt");
    } else {

    }
} catch (e) { alert(e); }