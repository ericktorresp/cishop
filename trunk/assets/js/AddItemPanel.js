function p1UnLock() {
    $(".addpanel_address_").attr("class", "addpanel_address");
    $("#itemUrl").removeAttr("disabled");
}

function p1Lock() {
    $("#itemUrl").attr("disabled", "disabled");
    $(".addpanel_address").attr("class", "addpanel_address_");
}

function noPrice(price) {
    if (price != -1)
        $("#productPrice").val(price).attr("disabled", "disabled").attr("class", "addpanel_hui");
    else {
        $("#productPrice").attr("class", "addpanel_red").focus(function() { if ($(this).attr("class") == "addpanel_red") $(this).val(""); $(this).attr("class", ""); }).blur(function() { if ($.trim($(this).val()) <= 0) $(this).attr("class", "addpanel_red").val("请填写商品价格"); disSubBtn(); }).keydown(function() { disSubBtn(); }).val("请填写商品价格");
        $("#proAlert").attr("class", "addpanel_alert").text("系统未能抓取商品相关信息，您可以在输入框中填写相关信息");
    }
}

//
function disSubBtn() {
    if ($("#productName").attr("class") != "addpanel_red addpanel_k" && $("#productPrice").attr("class") != "addpanel_red") {
        $("#successBtn").removeAttr("disabled").attr("class", "addpanel_next");
    }
    else {
        $("#successBtn").attr("disabled", "disabled").attr("class", "addpanel_next_no");
    }
}


//产品抓取成功后数据绑定方法
function buildP2(data) {
    var item = eval("(" + data.d + ")");
    if (data._statusCode == 500) {
        buildP2_fail();
    }
    else {
        if (data.error == null) {
            if (item._szProductName != "")
                $("#productName").val(item._szProductName).attr("disabled", "disabled").attr("class", "addpanel_hui addpanel_k");
            else {
                $("#productName").attr("class", "addpanel_red addpanel_k").focus(function() { if ($(this).attr("class") == "addpanel_red addpanel_k") $(this).val(""); $(this).attr("class", "addpanel_k"); }).blur(function() { if ($.trim($(this).val()) <= 0) $(this).attr("class", "addpanel_red addpanel_k").val("请填写商品名称"); disSubBtn(); }).keydown(function() { disSubBtn(); }).val("请填写商品名称");
                $("#proAlert").attr("class", "addpanel_alert").text("系统未能抓取商品相关信息，您可以在输入框中填写相关信息");
            }
            if (!item._isAuction) {
                switch (item.vipLevel) {
                    case 0:
                        if (item._mPrice != -1) {
                            $("#productPrice").val(item._mPrice).attr("disabled", "disabled").attr("class", "addpanel_hui");
                            if ($("#productName").attr("class") != "addpanel_red") $("#successBtn").removeAttr("disabled").attr("class", "addpanel_next");
                        }
                        else
                            noPrice(-1);
                        break;
                    case 1:
                        if (item._mVipPrice1 != -1) {
                            $("#productPrice").val(item._mVipPrice1).attr("disabled", "disabled").attr("class", "addpanel_hui");
                            if ($("#productName").attr("class") != "addpanel_red") $("#successBtn").removeAttr("disabled").attr("class", "addpanel_next");
                        }
                        else
                            noPrice(item._mPrice);
                        break;
                    case 2:
                        if (item._mVipPrice2 != -1) {
                            $("#productPrice").val(item._mVipPrice2).attr("disabled", "disabled").attr("class", "addpanel_hui");
                            if ($("#productName").attr("class") != "addpanel_red") $("#successBtn").removeAttr("disabled").attr("class", "addpanel_next");
                        }
                        else
                            noPrice(item._mPrice);
                        break;
                    case 3:
                        if (item._mVipPrice3 != -1) {
                            $("#productPrice").val(item._mVipPrice3).attr("disabled", "disabled").attr("class", "addpanel_hui");
                            if ($("#productName").attr("class") != "addpanel_red") $("#successBtn").removeAttr("disabled").attr("class", "addpanel_next");
                        }
                        else
                            noPrice(item._mPrice);
                        break;
                    default:
                        if (item._mPrice != -1) {
                            $("#productPrice").val(item._mPrice).attr("disabled", "disabled").attr("class", "addpanel_hui");
                            if ($("#productName").attr("class") != "addpanel_red") $("#successBtn").removeAttr("disabled").attr("class", "addpanel_next");
                        }
                        else
                            noPrice(-1);
                        break;
                }
            }
            else {
                $("#isAuction").show();
                if (item._mPrice != -1)
                    $("#productPrice").val(item._mPrice).attr("class", "").blur(function() { if ($.trim($(this).val()) < item._mPrice) $(this).val(item._mPrice.toString()); });
                else
                    $("#productPrice").attr("class", "addpanel_red").focus(function() { if ($(this).attr("class") == "addpanel_red") $(this).val(""); $(this).attr("class", ""); }).blur(function() { if ($.trim($(this).val()) <= 0) $(this).attr("class", "addpanel_red").val("请填写商品价格") }).val("请填写商品价格");
            }

            if (item._mSendPrice != -1)
                $("#productSendPrice").val(item._mSendPrice).attr("class", "addpanel_hui");
            else
                $("#productSendPrice").val("10").attr("class", "addpanel_red addpanel_wen").focus(function() { $("#question").css("display", "inline"); });

            if (item._szProductURL != "")
                $("#productUrl").val(item._szProductURL);
            else
                $("#productUrl").attr("class", "addpanel_red");

            if (item._szProPIC != "")
                $("#productImg").css("display", "inline").children("img").attr("src", item._szProPIC);

        } else {
            //错误信息输出
        }
        disSubBtn();
        //将商品信息存放到全局变量addItem_productInfo
        addItem_productInfo.szProductName = $("#productName").val();
        addItem_productInfo.szProductURL = $("#productUrl").val();
        addItem_productInfo.szProPIC = item._szProductURL;
        addItem_productInfo.szCategory = item.szCategory;
        addItem_productInfo.szShopName = item._szShopName;
        addItem_productInfo.szProShopURL = item._szProShopURL;
        addItem_productInfo.mPrice = item._mPrice;
        addItem_productInfo.mVipPrice1 = item._mVipPrice1;
        addItem_productInfo.mVipPrice2 = item._mVipPrice2;
        addItem_productInfo.mVipPrice3 = item._mVipPrice3;
        addItem_productInfo.mSendPrice = $("#productSendPrice").val();
        addItem_productInfo.isAuction = item._isAuction;
    }
}

function buildP2_fail() {
    $("#proAlert").attr("class", "addpanel_alert").text("系统未能抓取商品相关信息，您可以在输入框中填写相关信息");
    $("#productUrl").val($("#itemUrl").val());
    $("#productSendPrice").val("10").attr("class", "addpanel_red addpanel_wen").focus(function() { $("#question").css("display", "inline"); });
    $("#productName").attr("class", "addpanel_red addpanel_k").focus(function() { if ($(this).attr("class") == "addpanel_red addpanel_k") $(this).val(""); $(this).attr("class", "addpanel_k"); }).blur(function() { if ($.trim($(this).val()) <= 0) $(this).attr("class", "addpanel_red addpanel_k").val("请填写商品名称"); disSubBtn(); }).keydown(function() { disSubBtn(); }).val("请填写商品名称");
    $("#productPrice").attr("class", "addpanel_red").focus(function() { if ($(this).attr("class") == "addpanel_red") $(this).val(""); $(this).attr("class", ""); }).blur(function() { if ($.trim($(this).val()) <= 0) $(this).attr("class", "addpanel_red").val("请填写商品价格"); disSubBtn(); }).keydown(function() { disSubBtn(); }).val("请填写商品价格");
    disSubBtn();
    
//    addItem_productInfo.szProductName = $("#productName").val();
//    addItem_productInfo.szProductURL = $("#productUrl").val();
//    addItem_productInfo.szProPIC = item._szProductURL;
//    addItem_productInfo.szShopName = item._szShopName;
//    addItem_productInfo.szProShopURL = item._szProShopURL;
//    addItem_productInfo.mPrice = item._mPrice;
//    addItem_productInfo.mVipPrice1 = item._mVipPrice1;
//    addItem_productInfo.mVipPrice2 = item._mVipPrice2;
//    addItem_productInfo.mVipPrice3 = item._mVipPrice3;
//    addItem_productInfo.mSendPrice = $("#productSendPrice").val();
//    addItem_productInfo.isAuction = item._isAuction;
}

var addItem_productInfo = {
    "szProductName": "",
    "szProductURL": "",
    "szProPIC": "",
    "szCategory": "",
    "szShopName": "",
    "szProShopURL": "",
    "mPrice": -1,
    "mVipPrice1": -1,
    "mVipPrice2": -1,
    "mVipPrice3": -1,
    "nBuyNum": -1, //此属性暂时无用
    "mSendPrice": -1,
    "isAuction": false
};

$(document).ready(function() {

    var ShowError = function(XMLHttpRequest, textStatus, errorThrown) {
        p1UnLock();
        $("#p1").hide();
        if ($("#p2 div") <= 0) {
            $("#p2").load("/AddItemPanel/AddItemPanel2.html", function() { buildP2_fail(); $("#p2").show(); $("#productRemark").focus(); });
        } else {
            buildP2_fail();
            $("#p2").show();
            $("#productRemark").focus();
        }
        //alert(textStatus);
    }
    var ShowItemSnapshot = function(data) {
        p1UnLock();
        $("#p1").hide();
        if ($("#p2 div").length <= 0) {
            $("#p2").load("../html/AddItemPanel2.html", function() { buildP2(data); $("#productRemark").focus(); });
        } else {
            buildP2(data);
            $("#p2").show();
            $("#productRemark").focus();
        }
    }

    //输入商品网址后提交方法
    $("#addpanel_submit").click(function() {
        var url = $("#itemUrl").val();
        var reg = new RegExp("http(s)?://([\\w-]+\\.)+[\\w-]+(/[\\w- ./?%&=]*)?");
        if (url.length <= 0) {
            $("#promptInfo").attr("class", "addpanel_wrong");
            $("#promptInfo p").text("请输入您想代购商品的详细页网址！");
        }
        else {
            if (url.indexOf("http://") == -1 && url.indexOf("https://") == -1)
                url = "http://" + url;
            if (reg.test(url)) {
                p1Lock();
                $(this).attr("disabled", "disabled");
                $("#promptInfo").attr("class", "addpanel_loading").prepend("<img src=\"/images20090801/AddItemPanel/loading.gif\" alt=\"请稍候\" />");
                $("#promptInfo p").text("正在抓取商品信息...");

                $.ajax({
                    type: "POST",
                    url: "/App_Services/wsAddItem.asmx/GetItemSnapshot",
                    dataType: "json",
                    contentType: "application/json;utf-8",
                    data: "{aimUrl:'" + url + "'}",
                    timeout: 25000,
                    error: ShowError,
                    success: ShowItemSnapshot
                });
            }
            else {
                $("#promptInfo").attr("class", "addpanel_wrong");
                $("#promptInfo p").text("输入的网址不正确，请核实后再填写！");
            }
        }
    });

    $("#itemUrl").keydown(function(e) { if (e.keyCode == 13) $("#addpanel_submit").click(); });
});