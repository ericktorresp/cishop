;(function($){
//start closure
/********************************************************全局函数********************************************************/
$.lottery_countMoney = function(times){//计算总投注金额
    $.fn.lottery_no_moneycount = $.fn.lottery_no_count * $.fn.lottery_option.price * Number(times);
}
$.lottery_countNums  = function(){  //计算总投注注数
    lottery_type   = $.fn.lottery_option.lottery;//彩种
    lottery_method = $.fn.lottery_option.method;//方法
    numlen         = $.fn.lottery_no_confirm.length;
    if( numlen == 0 ){
        $.fn.lottery_no_count = 0;
        return;
    }
    function _getCountZUXHZ(){//组选和值固定计算
        var const_count = {1:1,2:2,3:2,4:4,5:5,6:6,7:8,8:10,9:11,10:13,11:14,12:14,13:15,14:15,15:14,16:14,17:13,18:11,19:10,20:8,21:6,22:5,23:4,24:2,25:2,26:1};
        var temp_count = 0;
        for( i=0; i<numlen; i++ ){
            temp_count += const_count[$.fn.lottery_no_confirm[i]];
        }
        return temp_count;
    }
    function _getPCount(m){//m*m*m...形式计算[m<=$.fn.lottery_no_confirm.length]
        result_len = 1;
        m = Number(m);
        if( m > numlen || m < 1 ){
            return 0;
        }
        for( i=0; i<m; i++ ){
            result_len *= $.fn.lottery_no_confirm[i].toString().length;
        }
        return result_len;
    }
    single_len     = _getPCount(1);//确认区号码长度
    if( lottery_type == '3D' || lottery_type == 'P5' ){//3D
        switch( lottery_method.toUpperCase() ){//根据玩法不同展开方式不同
            case 'ZX'   : $.fn.lottery_no_count = numlen; break;
            case 'ZXHZ' : $.fn.lottery_no_count = numlen; break;
            case 'TX'   : $.fn.lottery_no_count = numlen; break;
            case 'ZS'   : $.fn.lottery_no_count = single_len*(single_len-1); break;
            case 'ZL'   : $.fn.lottery_no_count = single_len*(single_len-1)*(single_len-2)/6; break;
            case 'HHZX' : $.fn.lottery_no_count = numlen; break;
            case 'ZUXHZ': $.fn.lottery_no_count = _getCountZUXHZ(); break;
            case 'YMBDW': $.fn.lottery_no_count = numlen; break;
            case 'EMBDW': $.fn.lottery_no_count = single_len*(single_len-1)/2; break;
            case 'QEZX' : $.fn.lottery_no_count = _getPCount(2); break;
            case 'QEZUX': $.fn.lottery_no_count = single_len*(single_len-1)/2; break;
            case 'HEZX' : $.fn.lottery_no_count = _getPCount(2); break;
            case 'HEZUX': $.fn.lottery_no_count = single_len*(single_len-1)/2; break;
            case 'QEDXDS' : $.fn.lottery_no_count = _getPCount(2); break;
            case 'HEDXDS' : $.fn.lottery_no_count = _getPCount(2); break;
            case 'DWD'  : $.fn.lottery_no_count = numlen; break;
            default : break;
        }
    }else if( lottery_type == 'XSQ' ){//双色球
        switch( lottery_method.toUpperCase() ){//根据玩法不同展开方式不同
            case 'YQRX'   : $.fn.lottery_no_count = numlen; break;
            default     : break;
        }
    }else if( lottery_type == '225' ){//22选5
        
    }else if( lottery_type == 'QLC' ){//七乐彩
        
    }else{
        $.alert(lottery_lang.param_error);
        return false;
    }
}
/********************************************************全局函数完******************************************************/
/*****************************确认区********************************/
$.fn.loterryConfirmArea = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults.confirmDefault, opts || {} ); //根据参数初始化默认配置
    var $editor = $("<textarea name=\"confirm_editor\" id=\"confirm_editor\" class=\""+opts.textareaCss+"\" readonly=\"readonly\"></textarea><input type='hidden' name='lottery_confirmnums' id='lottery_confirmnums' value=''><input type='hidden' name='lottery_adjustcodes' id='lottery_adjustcodes' value=''><input type='hidden' name='lottery_adjustchoice' id='lottery_adjustchoice' value='2'>");
    var $other  = $("<div class='lottery_confirm_list'><input type='hidden' name='lottery_currentissue' id='lottery_currentissue' value='"+$.fn.lottery_option.period+"'>"+lottery_lang.tip_c_count+":<input type='hidden' name='lottery_totalnum' id='lottery_totalnum' value='0'><span id=\"lottery_total_order\" class=\""+opts.jswordCss+"\">0</span>,  &nbsp;&nbsp;"+lottery_lang.tip_c_money+":<input type='hidden' name='lottery_totalamount' id='lottery_totalamount' value='0'><span id=\"lottery_total_money\" class=\""+opts.jswordCss+"\">0</span> "+lottery_lang.tip_c_yxb+", &nbsp;&nbsp;"+lottery_lang.tip_c_time1+":<input type=\"text\" id=\"lottery_times\" name='lottery_times' class=\""+opts.inputCss+"\" value='1' /> "+lottery_lang.tip_c_time2+"<br />&nbsp;&nbsp;<span class=\"quickbutton\">5"+lottery_lang.tip_c_time2+"</span>&nbsp;&nbsp;<span class=\"quickbutton\">10"+lottery_lang.tip_c_time2+"</span>&nbsp;&nbsp;<span class=\"quickbutton\">15"+lottery_lang.tip_c_time2+"</span>&nbsp;&nbsp;<span class=\"quickbutton\">20"+lottery_lang.tip_c_time2+"</span>&nbsp;&nbsp;<span class=\"quickbutton\">25"+lottery_lang.tip_c_time2+"</span></div>");
    $editor.appendTo(this);
    $other.appendTo(this);
    //确认号码变动事件
    $("#confirm_editor").change(function(){
        $.lottery_countNums();
        times = $("#lottery_times").val();
        if( times == "" ){
            times = 1;
        }else{
            times = Number(times);
        }
        $("#lottery_times").val(times);
        $.lottery_countMoney(times);
        $("#lottery_confirmnums").val($.fn.lottery_no_confirm.join("|"));//写入提交的号码
        $("#lottery_total_order").html($.fn.lottery_no_count);  //写入注数
        $("#lottery_totalnum").val($.fn.lottery_no_count);
        $("#lottery_total_money").html($.fn.lottery_no_moneycount);//写入总投注金额
        $("#lottery_totalamount").val($.fn.lottery_no_moneycount);
        $.cleanTrace();
    });
    //绑定输入倍数事件
    $("#lottery_times").keyup(function(){
        times = $(this).val().replace(/[^0-9]/g,"").substring(0,5);
        $(this).val( times );
        if( times == "" ){
            times = 0;
        }else{
            times = Number(times);
        }
        $.lottery_countMoney(times);
        $("#lottery_total_money").html($.fn.lottery_no_moneycount);
        $("#lottery_totalamount").val($.fn.lottery_no_moneycount);
    });
    //快捷方式事件
    $("span[class='quickbutton']").click(function(){
    	  times = parseInt($(this).html(),10);
    	  $("#lottery_times").val(times);
        $.lottery_countMoney(times);
        $("#lottery_total_money").html($.fn.lottery_no_moneycount);
        $("#lottery_totalamount").val($.fn.lottery_no_moneycount);
    });
};

//end closure
})(jQuery);