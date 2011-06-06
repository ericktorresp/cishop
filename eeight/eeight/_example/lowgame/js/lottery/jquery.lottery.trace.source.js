;(function($){
//start closure
/********************************************************全局函数********************************************************/

/********************************************************全局函数完******************************************************/
/*****************************追号区********************************/
$.fn.loterryTraceArea = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults.traceDefault, opts || {} ); //根据参数初始化默认配置
    var traceHtml = "<table border='0' cellpadding='0' cellspacing='0' style='width:100%;'>";
    traceHtml += "<tr><td height='30' align='left'>&nbsp;&nbsp;<input type='checkbox' name='trace_check' id='trace_check' value='1' /><input type='hidden' name='lottery_istrace' id='lottery_istrace' value='0'><input type='hidden' name='trace_totalamount' id='trace_totalamount' value='0'> "+lottery_lang.tip_t_t1+"&nbsp;&nbsp;&nbsp;<input type='checkbox' name='trace_stop' id='trace_stop' value='1' disabled /> "+lottery_lang.tip_t_t2+"</td></tr>";
    traceHtml += "<tr><td align='center' id='trace_content_area' style='display:none;'><input type='hidden' name='trace_type' id='trace_type' value='sametimes'>";
    //标签
    traceHtml += "<table class='"+opts.label_css+"' cellpadding='0' cellspacing='0'><tr><td class='"+opts.label_space_css+"'>&nbsp;</td>";
    $.each(opts.selectype, function(i,n){
        if( i == 0 ){
            labelcss = opts.label_front_css;
        }else{ labelcss = opts.label_back_css; }
        switch(n){
            case 'sametimes' :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_sametimes'>"+lottery_lang.tip_t_t3+"</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            case 'difftimes' :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_difftimes'>"+lottery_lang.tip_t_t4+"</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            case 'profit'    :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_profit'>"+lottery_lang.tip_t_t5+"</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            case 'margin'     :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_margin'>"+lottery_lang.tip_t_t6+"</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            default : break;
        }
    });
    traceHtml += "<td class='"+opts.label_line_css+"'>&nbsp;</td></tr></table>";
    //标签内容
    traceHtml += "<table class='"+opts.table_css+"' cellpadding='0' cellspacing='0' style='border-top:0;'><tr><td align='center' style='border-right:0;' valign='middle'>";
    $.each(opts.selectype, function(i,n){
        if( i == 0 ){
            dispaly = ""
        }else{ dispaly = "style='display:none;'"; }
        switch(n){
            case 'sametimes' :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_sametimes_text' "+dispaly+">"+lottery_lang.tip_t_t7+":<input type='text' class='"+opts.input_css+"' name='trace_sametimes' id='trace_sametimes' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t8+":<input type='text' class='"+opts.input_css+"' name='trace_issues_sametimes' id='trace_issues_sametimes' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t9+": <span class='"+opts.money_css+"'>0</span> "+lottery_lang.tip_c_yxb+"</div>";
                break;
            case 'difftimes' :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_difftimes_text' "+dispaly+">"+lottery_lang.tip_t_t10+" <input type='text' class='"+opts.input_css+"' name='trace_step_difftimes' id='trace_step_difftimes' /> "+lottery_lang.tip_t_t11+"&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t7+" × <input type='text' class='"+opts.input_css+"' name='trace_difftimes' id='trace_difftimes' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t12+":<input type='text' class='"+opts.input_css+"' name='trace_issues_difftimes' id='trace_issues_difftimes' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t9+": <span class='"+opts.money_css+"'>0</span> "+lottery_lang.tip_c_yxb+"</div>";
                break;
            case 'profit'    :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_profit_text' "+dispaly+">"+lottery_lang.tip_t_t13+": <input type='text' class='"+opts.input_css+"' name='trace_profittimes' id='trace_profittimes' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t8+":<input type='text' class='"+opts.input_css+"' name='trace_issues_profit' id='trace_issues_profit' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t14+":<input type='text' class='"+opts.input_css+"' name='trace_profit_min' id='trace_profit_min' /> "+lottery_lang.tip_c_yxb+"&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t9+": <span class='"+opts.money_css+"'>0</span> "+lottery_lang.tip_c_yxb+"</div>";
                break;
            case 'margin'     :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_margin_text' "+dispaly+">"+lottery_lang.tip_t_t13+": <input type='text' class='"+opts.input_css+"' name='trace_margintimes' id='trace_margintimes' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t8+":<input type='text' class='"+opts.input_css+"' name='trace_issues_margin' id='trace_issues_margin' />&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t15+":<input type='text' class='"+opts.input_css+"' name='trace_margin_min' id='trace_margin_min' value='50' /> %&nbsp;&nbsp;&nbsp;&nbsp;"+lottery_lang.tip_t_t9+": <span class='"+opts.money_css+"'>0</span> "+lottery_lang.tip_c_yxb+"</div>";
                break;
            default : break;
        }
    });
    traceHtml += "</td><td align='center' width='100'>";
    if( opts.button_image == '' ){
        traceHtml += "<button type='button' class='"+opts.button_css+"' id='trace_button'>"+opts.button_text+"</button>";
    }else{
        traceHtml += "<img src='"+opts.button_image+"' class='"+opts.button_css+"' alt='"+opts.button_text+"' id='trace_button' style='cursor:pointer;' />";
    }
    traceHtml += "</td></tr></table>";
    //追号详情
    traceHtml += "<div style='height:200px;overflow:auto;overflow-x:hidden;'><table class='"+opts.table_css+"' cellpadding='0' cellspacing='0' style='width:97%;' id='trace_detail'><tr><td colspan='6' align='center' bgcolor='#FF6666' class='lottery_tracearea_wordtitle'>"+lottery_lang.tip_t_t16+"</td></tr><tr><td height='20' class='titletd'>"+lottery_lang.tip_t_t17+"</td><td class='titletd'>"+lottery_lang.tip_t_t7+"</td><td class='titletd'>"+lottery_lang.tip_t_t18+"</td><td class='titletd'>"+lottery_lang.tip_t_t19+"</td><td class='titletd'>"+lottery_lang.tip_t_t20+"</td><td class='titletd'>"+lottery_lang.tip_t_t21+"</td></tr>";
    var len = opts.periods.length;
    $.fn.lottery_periodArr = [];//每期的数据
    for( i=0; i < len; i++ ){
        if( opts.periods[i] != "" ){
            $.fn.lottery_periodArr.push({issue:opts.periods[i],ischecked:false,times:0,money_buy:0,money_prize:0,money_profit:0,money_percent:0});//初始追号任务表
            traceHtml += "<tr><td height='20'><input type='checkbox' name='trace_issue[]' value='"+opts.periods[i]+"'> "+opts.periods[i]+"</td><td><input type='text' class='"+opts.input_css+"' name='trace_times_"+opts.periods[i]+"' value='0' disabled> "+lottery_lang.tip_c_time2+"</td><td><span class='trace_money_buy'>0</span> "+lottery_lang.tip_c_yxb+"</td><td><span class='trace_money_prize'>0</span> "+lottery_lang.tip_c_yxb+"</td><td><span class='trace_money_profit'>0</span> "+lottery_lang.tip_c_yxb+"</td><td><span class='trace_money_precent'>0</span> %</td></tr>";
        }
    }
    traceHtml += "</table></div>";
    traceHtml += "</td></tr>";
    traceHtml += "</table>";
    $(traceHtml).appendTo(this);
    //////////绑定事件
    $("#trace_check").click(function(){//显示追号
        if( $(this).attr("checked") == true ){
            $("#trace_stop").attr("disabled",false).attr("checked",true);
            $("#lottery_istrace").val(1);
            $("#trace_content_area").show();
        }else{
            $("#trace_stop").attr("disabled",true).attr("checked",false);
            $("#lottery_istrace").val(0);
            $("#trace_content_area").hide();
        }
    });
    $("input[type='text'][name^='trace']").keyup(function(){
        $(this).val( Number($(this).val().replace(/[^0-9]/g,"0")) );
    });
    function jamesRound(num){//根据奖金小数位数对金额进行四舍五入
        var t;
        try{t=$.fn.lottery_option.prize.toString().split(".")[1].length;}catch(e){t=0;}
        return Math.round(Number(num)*Math.pow(10,t))/Math.pow(10,t);
    }
    function compute( count, times, oldprice ){//根据注数和倍数以及历史购买金额算购买金额、奖金和利润以及利率
        var price = $.fn.lottery_option.price;    //单注单倍购买金额
        var prize = $.fn.lottery_option.prize;    //单倍中奖金额
        var result= [{buy:0,prize:0,profit:0,percent:0}];
        count = count ? Math.round(Number(count)) : 0;
        times = times ? Math.round(Number(times)) : 0;
        oldprice = oldprice ? Math.round(Number(oldprice)) : 0;
        if( count > 0 && times > 0 ){//有奖金的情况
            result.buy     = price * count * times;     //购买金额
            result.prize   = jamesRound(prize * times);             //奖金
            result.profit  = jamesRound(result.prize - result.buy - oldprice); //利润
            result.percent = Math.round( result.profit*100 / (result.buy+oldprice) );//利润率[四舍五入]
        }else{
            result.buy      = 0;
            result.prize    = 0;
            result.profit   = 0;
            result.percent  = 0;
        }
        return result;
    }
    function computeByProfit( count, starttime, profit, oldprice ){ //根据注数、历史购买金额以及利润金额算最低倍数、购买金额、奖金和利率
        var price = $.fn.lottery_option.price;    //单注单倍购买金额
        var prize = $.fn.lottery_option.prize;    //单倍中奖金额
        var result= [{times:0,buy:0,prize:0,profit:0,percent:0}];
        count = count ? Math.round(Number(count)) : 0;
        profit = profit ? Math.round(Number(profit)) : 0;
        oldprice = oldprice ? Math.round(Number(oldprice)) : 0;
        starttime = starttime ? Math.round(Number(starttime)) : 1;
        if( count > 0  ){
            if( profit >= 0 ){//最低利润有值
                result.times = Math.ceil((profit + oldprice)/(prize - price*count));
            }else{
                result.times    = 1;
            }
            if( result.times < starttime ){
                result.times = starttime;
            }
            result.buy      = price * count * result.times;
            result.prize    = jamesRound(prize * result.times);
            result.profit   = jamesRound(result.prize - result.buy - oldprice); //利润
            result.percent  = Math.round( result.profit*100 / (result.buy+oldprice) );//利润率[四舍五入]
        }else{
            result.times    = 0;
            result.buy      = 0;
            result.prize    = 0;
            result.profit   = 0;
            result.percent  = 0;
        }
        return result;
    }
    function computeByMargin( count, starttime, margin, oldprice ){
        //根据注数、开始倍数、利润率和历史购买金额算最低倍数、购买金额、奖金和利润率
        var price = $.fn.lottery_option.price;    //单注单倍购买金额
        var prize = $.fn.lottery_option.prize;    //单倍中奖金额
        var result= [{times:0,buy:0,prize:0,profit:0,percent:0}];
        count     = count ? Math.round(Number(count)) : 0;
        margin    = margin ? Math.round(Number(margin)) : 0;
        oldprice  = oldprice ? Math.round(Number(oldprice)) : 0;
        starttime = starttime ? Math.round(Number(starttime)) : 1;
        if( count > 0 ){
            if( margin >= 0 ){//最低利润有值
                result.times = Math.ceil( (oldprice*(margin/100+1))/(prize-(margin/100+1)*count*price) );
            }else{
                result.times    = 1;
            }
            if( result.times < starttime ){
                result.times = starttime;
            }
            result.buy      = price * count * result.times;
            result.prize    = jamesRound(prize * result.times);
            result.profit   = jamesRound(result.prize - result.buy - oldprice); //利润
            result.percent  = Math.round( result.profit*100 / (result.buy+oldprice) );//利润率[四舍五入]
        }else{
            result.times    = 0;
            result.buy      = 0;
            result.prize    = 0;
            result.profit   = 0;
            result.percent  = 0;
        }
        return result;
    }
    function updateTotalMoney(){   //更新追号总金额以及期数
        traceType = $("#trace_type").val().toLowerCase(); //追号方式
        var money = 0;
        var issues= 0;
        $.each($.fn.lottery_periodArr,function(i,n){
            if( n.ischecked ==  true ){
                issues += 1;
                money += Number(n.money_buy);
            }
        });
        $("#trace_label_"+traceType+"_text").find("span").html(money);
        $("#trace_issues_"+traceType).val(issues);
        $("#trace_totalamount").val(money);
    }
    $("#trace_button").click(function(){    //立即生成按钮
        traceType = $("#trace_type").val().toLowerCase(); //追号方式
        var count = $.fn.lottery_no_count;                //总注数
        var price = $.fn.lottery_option.price;            //单注单倍购买金额
        var prize = $.fn.lottery_option.prize;            //单倍中奖金额
        if( count <= 0 ){
            $.alert(lottery_lang.msg_trace_t1);
            return false;
        }
        if( traceType != 'sametimes' && traceType != 'difftimes' && price * count > prize ){//同倍追号和翻倍追号不判断
            $.alert(lottery_lang.msg_trace_t2);
            return false;
        }
        if( $.inArray(traceType,['sametimes','difftimes','profit','margin']) != -1 ){//合法范围
            var issuecount = Number($("#trace_issues_"+traceType).val());
            if( issuecount >= opts.periods.length ){
                $.alert(lottery_lang.msg_trace_t3+":"+(opts.periods.length-1));
                return false;
            }
            var periodlen  = $.fn.lottery_periodArr.length;
            var error      = false;
            var errmsg     = "";
            if( issuecount <= 0 ){
                $.alert(lottery_lang.msg_trace_t4);
                return false;
            }
            switch( traceType ){//根据不同追号方式,产生不同的方案
                case 'sametimes' : //同倍追号
                                   var times = Number($("#trace_sametimes").val());
                                   if( times <= 0 ){
                                      error = true;
                                      errmsg= lottery_lang.msg_trace_t5;
                                      break;
                                   }
                                   var oldprice  = 0;
                                   var totalbuy  = 0;
                                   var statistic = [];
                                   for( i=0; i<periodlen; i++  ){
                                      statistic = compute(count,times,oldprice);
                                      oldprice += statistic.buy;
                                      if( i < issuecount ){
                                          totalbuy += statistic.buy;
                                      }
                                      $.fn.lottery_periodArr[i].times         = times;   //同倍
                                      $.fn.lottery_periodArr[i].money_buy     = statistic.buy;       //购买金额
                                      $.fn.lottery_periodArr[i].money_prize   = statistic.prize;     //奖金金额
                                      $.fn.lottery_periodArr[i].money_profit  = statistic.profit;    //利润金额
                                      $.fn.lottery_periodArr[i].money_percent = statistic.percent;   //单期利润率
                                   }
                                   errmsg = lottery_lang.msg_trace_t6.replace( "[times]",times );
                                   break;
                case 'difftimes' :  //翻倍追号
                                   var trace_step = Number($("#trace_step_difftimes").val());
                                   var times_step = Number($("#trace_difftimes").val());
                                   if( trace_step <= 0 ){
                                      error = true;
                                      errmsg= lottery_lang.msg_trace_t7;
                                      break;
                                   }
                                   if( times_step <= 0 ){
                                      error = true;
                                      errmsg= lottery_lang.msg_trace_t5;
                                      break;
                                   }
                                   var times = 1;  //起始倍数
                                   var oldprice  = 0;
                                   var totalbuy  = 0;
                                   var statistic = [];
                                   for( i=0; i<periodlen; i++  ){
                                      if( i!= 0 && (i%(trace_step)) == 0  ){
                                          times *= times_step;
                                      }
                                      statistic = compute(count,times,oldprice);
                                      oldprice += statistic.buy;
                                      if( i < issuecount ){
                                          totalbuy += statistic.buy;
                                      }
                                      $.fn.lottery_periodArr[i].times = times;   //倍数
                                      $.fn.lottery_periodArr[i].money_buy     = statistic.buy;   //购买金额
                                      $.fn.lottery_periodArr[i].money_prize   = statistic.prize;   //奖金金额
                                      $.fn.lottery_periodArr[i].money_profit  = statistic.profit;   //利润金额
                                      $.fn.lottery_periodArr[i].money_percent = statistic.percent;   //单期利润率
                                   }
                                   errmsg = lottery_lang.msg_trace_t8.replace( "[trace_step]",trace_step ).replace( "[times_step]",times_step );
                                   break;
                case 'profit'   : //利润追号
                                   var startTime = Number($("#trace_profittimes").val());//起始倍数
                                   var minprofit = Number($("#trace_profit_min").val());//最低利润额
                                   if( minprofit <= 0 ){
                                      error = true;
                                      errmsg= lottery_lang.msg_trace_t9;
                                      break;
                                   }
                                   var oldprice  = 0;
                                   var totalbuy  = 0;
                                   var statistic = [];
                                   for( i=0; i<periodlen; i++  ){
                                       statistic = computeByProfit( count, startTime, minprofit, oldprice );
                                       oldprice += statistic.buy;
                                       if( i < issuecount ){
                                          totalbuy += statistic.buy;
                                       }
                                       $.fn.lottery_periodArr[i].times         = statistic.times;   //倍数
                                       $.fn.lottery_periodArr[i].money_buy     = statistic.buy;   //购买金额
                                       $.fn.lottery_periodArr[i].money_prize   = statistic.prize;   //奖金金额
                                       $.fn.lottery_periodArr[i].money_profit  = statistic.profit;   //利润金额
                                       $.fn.lottery_periodArr[i].money_percent = statistic.percent;   //单期利润率
                                   }
                                   errmsg = lottery_lang.msg_trace_t10.replace( "[minprofit]",minprofit ).replace( "[startTime]",startTime );
                                   break;
                case 'margin'   : //利润率追号
                                   var startTime = Number($("#trace_margintimes").val());//起始倍数
                                   var minmargin = Number($("#trace_margin_min").val());//最低利润率
                                   if( minmargin >= ((prize*100)/(price*count)-100) ){
                                      error = true;
                                      errmsg= lottery_lang.msg_trace_t11;
                                      break;
                                   }
                                   var oldprice  = 0;
                                   var totalbuy  = 0;
                                   var statistic = [];
                                   for( i=0; i<periodlen; i++  ){
                                       statistic = computeByMargin( count, startTime, minmargin, oldprice );
                                       oldprice += statistic.buy;
                                       if( i < issuecount ){
                                          totalbuy += statistic.buy;
                                       }
                                       $.fn.lottery_periodArr[i].times         = statistic.times;   //倍数
                                       $.fn.lottery_periodArr[i].money_buy     = statistic.buy;   //购买金额
                                       $.fn.lottery_periodArr[i].money_prize   = statistic.prize;   //奖金金额
                                       $.fn.lottery_periodArr[i].money_profit  = statistic.profit;   //利润金额
                                       $.fn.lottery_periodArr[i].money_percent = statistic.percent;   //单期利润率
                                   }
                                   errmsg = lottery_lang.msg_trace_t12.replace( "[minmargin]",minmargin ).replace( "[startTime]",startTime );
                                   break;
                                   
            }
            if( totalbuy > $.fn.lottery_option.money ){
                error  = true;
                errmsg = lottery_lang.msg_trace_t13;
            }
            if( error == true ){//出错显示错误
                $.alert(errmsg);
                return false;
            }
            $.confirm({
                message: errmsg+lottery_lang.msg_trace_t14.replace( "[issuecount]",issuecount ),
                funyes : function(){
                            $("input[name^='trace_issue']",$("#trace_detail")).each(function(i,n){
                                if( i<issuecount ){selectIssue(n);}else{unselectIssue(n);}
                            });
                            updateTotalMoney();
                    },
                funno  : function(){
                            $.cleanTrace();
                    }
            });
        }else{
            $.alert(lottery_lang.action_error);
            return false;
        }
    });
    function selectIssue(obj){//选择某期
        var len = $.fn.lottery_periodArr.length;
        for( i=0; i<len; i++ ){ //查找对应的期数
            if( $(obj).val() == $.fn.lottery_periodArr[i].issue ){
                $.fn.lottery_periodArr[i].ischecked = true;
                $(obj).parent().parent().find("input[name^='trace_times_']").attr("disabled",false).val($.fn.lottery_periodArr[i].times);
                $(obj).parent().parent().find("span[class^='trace_money']").each(function(j,n){
                    switch( $(this).attr("class") ){
                        case 'trace_money_buy'     : $(this).html($.fn.lottery_periodArr[i].money_buy); break;
                        case 'trace_money_prize'   : $(this).html($.fn.lottery_periodArr[i].money_prize); break;
                        case 'trace_money_profit'  : $(this).html($.fn.lottery_periodArr[i].money_profit); break;
                        case 'trace_money_precent' : $(this).html($.fn.lottery_periodArr[i].money_percent); break;
                        default : break;
                    }
                });
                break;
            }
        }
        $(obj).attr("checked",true);
        opts.fun_select($(obj).parent().parent());
    }
    function unselectIssue(obj){//取消选择某期
        $(obj).parent().parent().find("input[name^='trace_times_']").attr("disabled",true).val(0);
        $(obj).parent().parent().find("span[class^='trace_money']").each(function(i,n){
                $(this).html(0);
        });
        var len = $.fn.lottery_periodArr.length;
        for( i=0; i<len; i++ ){ //查找对应的期数
            if( $(obj).val() == $.fn.lottery_periodArr[i].issue ){
                $.fn.lottery_periodArr[i].ischecked = false;
                break;
            }
        }
        $(obj).attr("checked",false);
        opts.fun_unselect($(obj).parent().parent());
    }
    $("input[name^='trace_issue']",$("#trace_detail")).click(function(){
        if( $(this).attr("checked") == true ){//选中
            selectIssue(this);
        }else{  //取消选中
            unselectIssue(this);
        }
        updateTotalMoney();
    });
    $("input[name^='trace_times_']",$("#trace_detail")).keyup(function(){
        var len   = $.fn.lottery_periodArr.length;
        var count = $.fn.lottery_no_count;        //总注数
        var price = $.fn.lottery_option.price;    //单注单倍购买金额
        var prize = $.fn.lottery_option.prize;    //单倍中奖金额
        var oldprice = 0;
        var statistic = [];
        for( i=0; i<len; i++ ){ //查找对应的期数[并更新数据]
            if( $(this).parent().parent().find("input[name^='trace_issue']").val() == $.fn.lottery_periodArr[i].issue ){
                $.fn.lottery_periodArr[i].ischecked = true;
                if( Number($(this).val()) <= 0 ){
                    $(this).val($.fn.lottery_periodArr[i].times);
                    $.alert(lottery_lang.msg_trace_t15);
                    break;
                    return false;
                }
                $.fn.lottery_periodArr[i].times = Number($(this).val());
                statistic = compute(count,$.fn.lottery_periodArr[i].times,oldprice);
                oldprice += statistic.buy;
                $.fn.lottery_periodArr[i].money_buy     = statistic.buy;       //购买金额
                $.fn.lottery_periodArr[i].money_prize   = statistic.prize;     //奖金金额
                $.fn.lottery_periodArr[i].money_profit  = statistic.profit;    //利润金额
                $.fn.lottery_periodArr[i].money_percent = statistic.percent;   //单期利润率
                $(this).parent().parent().find("span[class^='trace_money']").each(function(j,n){
                    switch( $(this).attr("class") ){
                        case 'trace_money_buy'     : $(this).html($.fn.lottery_periodArr[i].money_buy); break;
                        case 'trace_money_prize'   : $(this).html($.fn.lottery_periodArr[i].money_prize); break;
                        case 'trace_money_profit'  : $(this).html($.fn.lottery_periodArr[i].money_profit); break;
                        case 'trace_money_precent' : $(this).html($.fn.lottery_periodArr[i].money_percent); break;
                        default : break;
                    }
                });
                i++;
                break;
            }else{
            		if( $.fn.lottery_periodArr[i].ischecked == true ){
            			oldprice += $.fn.lottery_periodArr[i].money_buy;
            		}
            }
        }
        for( i; i<len; i++ ){//依次更新后面所有期数的值
            statistic = compute(count,$.fn.lottery_periodArr[i].times,oldprice);
            oldprice += statistic.buy;
            $.fn.lottery_periodArr[i].money_buy     = statistic.buy;       //购买金额
            $.fn.lottery_periodArr[i].money_prize   = statistic.prize;     //奖金金额
            $.fn.lottery_periodArr[i].money_profit  = statistic.profit;    //利润金额
            $.fn.lottery_periodArr[i].money_percent = statistic.percent;   //单期利润率
            if( $.fn.lottery_periodArr[i].ischecked == true ){
                selectIssue($("input[name^='trace_issue'][value='"+$.fn.lottery_periodArr[i].issue+"']",$("#trace_detail")));
            }
        }
        updateTotalMoney();
    });
    //适用该插件的函数
    function traceShowLabel(obj){   //显示标签
        $(obj).attr("class",opts.label_front_css);
        $(obj+"_text").show();
    }
    function traceHideLabel(obj){   //隐藏标签
        $(obj).attr("class",opts.label_back_css);
        $(obj+"_text").hide();
    }
    $("#trace_label_sametimes").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_profit");
        traceHideLabel("#trace_label_margin");
        if( $("#trace_type").val() != "sametimes" ){
            $("#trace_type").val("sametimes");
            $.cleanTrace();
        }
    });
    $("#trace_label_difftimes").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_profit");
        traceHideLabel("#trace_label_margin");
        if( $("#trace_type").val() != "difftimes" ){
            $("#trace_type").val("difftimes");
            $.cleanTrace();
        }
    });
    $("#trace_label_profit").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_profit");
        traceHideLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_margin");
        if( $("#trace_type").val() != "profit" ){
            $("#trace_type").val("profit");
            $.cleanTrace();
        }
    });
    $("#trace_label_margin").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_margin");
        traceHideLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_profit");
        if( $("#trace_type").val() != "margin" ){
            $("#trace_type").val("margin");
            $.cleanTrace();
        }
    });
};
$.cleanTrace = function(){//清空所有追号方案
    $.each($.fn.lottery_periodArr,function(i,n){
        n.ischecked=false; n.times=0; n.money_buy=0; n.money_prize=0; n.money_profit=0; n.money_percent=0;
    });
    $("input[name^='trace_issue']",$("#trace_detail")).each(function(i,n){
        $(n).parent().parent().find("input[name^='trace_times_']").attr("disabled",true).val(0); // 倍数清0
        $(n).parent().parent().find("span[class^='trace_money']").each(function(i,n){
                $(this).html(0); // 投注金额，中奖金额，利润，利润率循环清0
        });
        $(n).attr("checked",false); // 取消选择
        if ($(n).attr("checked") === true){
        	opts.fun_unselect($(n).parent().parent());
        }
    });
    $("#trace_label_sametimes_text").find("span").html(0);
    $("#trace_label_difftimes_text").find("span").html(0);
    $("#trace_label_profit_text").find("span").html(0);
    $("#trace_label_margin_text").find("span").html(0);
    $("#trace_totalamount").val(0);
}
//end closure
})(jQuery);