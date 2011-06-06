;(function($){
//start closure

if (/1\.(0|1|2)\.(0|1|2)/.test($.fn.jquery) || /^1.1/.test($.fn.jquery)) {
    alert('requires jQuery v1.2.3 or later!  You are using v' + $.fn.jquery);
    return;
}

var lottery_config = {//彩种玩法对应配置表[根据数据库字段来][全大写]
    lottery: {1:'3D',2:'P5'},
    method : {9:'ZX',10:'ZXHZ',11:'TX',12:'ZS',13:'ZL',14:'HHZX',15:'ZUXHZ',16:'YMBDW',17:'EMBDW',18:'QEZX',19:'HEZX',20:'QEZUX',21:'HEZUX',22:'DWD',23:'DWD',24:'DWD',26:'QEDXDS',27:'HEDXDS',
    37:'ZX',38:'ZXHZ',39:'TX',40:'ZS',41:'ZL',42:'HHZX',43:'ZUXHZ',44:'YMBDW',45:'EMBDW',46:'QEZX',47:'HEZX',48:'QEZUX',49:'HEZUX',50:'DWD',51:'DWD',52:'DWD',53:'DWD',54:'DWD',55:'QEDXDS',56:'HEDXDS',57:'HEDXDS',58:'HEZX',59:'HEZUX'
    }
};

$.lotteryUI = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults, opts || {} ); //根据参数初始化默认配置
    /*************************全局参数设置***********************************/
    $.fn.lottery_option = {
                           lotteryid: parseInt(opts.lottery,10),    //彩种ID
                           lottery  : lottery_config.lottery[parseInt(opts.lottery,10)],     //彩种
                           method   : lottery_config.method[parseInt(opts.methodtype,10)],  //玩法
                           period   : opts.period,      //当前奖期
                           price    : Number(opts.singleprice),  //单注单倍金额
                           prize    : Number(opts.singleprize),  //单倍中奖金额
                           money    : Number(opts.usermoney),    //用户当前可用资金
                           limitbons: parseInt(opts.limitbons,10),    //单笔最高奖金限制额
                           bigcancel: parseInt(opts.bigcancel,10),    //大额扯单起始金额
                           canclepre: Number(opts.canclepre),
                           ajaxurl  : opts.ajaxurl,      //AJAX提交地址
                           confirmsplit : opts.confirmsplit //确认区号码分割符
                          };
    $.fn.lottery_no         = [];       //选择号码列表[选号区选择的号码]
    $.fn.lottery_no_edit    = [];       //编辑区号码列表
    $.fn.lottery_no_confirm = [];       //确认区号码列表
    $.fn.lottery_periodArr  = [];       //追号数据
    $.fn.lottery_no_count   = 0;        //选择注数统计
    $.fn.lottery_no_moneycount = 0;     //总金额
    $.fn.lottery_istrace    = false;    //是否追号
    $.fn.lottery_submiting  = false;    //是否正在提交数据
    $.fn.lottery_blocks     = {
                                timer    : opts.timer,
                                nowIssue : opts.nowIssue,
                                endTime  : opts.endTimearea,
                                isSelect : opts.selectArea,     //是否要选号区
                                isfilter : opts.filterArea,     //是否要筛选区
                                iseditor : opts.editArea,     //是否要编辑区
                                isconfirm: opts.confirmArea,     //是否要确认区
                                istrace  : opts.traceArea      //是否要追号区
                            };
    /*********************************生成购彩界面****************************/
    if( opts.timer != null ){
        $.lotteryTimer(opts.timer,opts.servertime,opts.endtime);
    }
    if( opts.nowIssue != null ){
        $(opts.nowIssue).html(opts.period);
    }
    if( opts.endTimearea != null ){
        $(opts.endTimearea).html(opts.endtime);
    }
    if( opts.selectArea != null ){
        $(opts.selectArea).loterrySelectArea(opts.selectDefault);
    }
    if( opts.filterArea != null ){
        $(opts.filterArea).loterryFilterArea(opts.filterDefault);
    }
    if( opts.editArea != null ){
        $(opts.editArea).loterryEditArea(opts.editDefault);
    }
    if( opts.confirmArea != null ){
        $(opts.confirmArea).loterryConfirmArea(opts.confirmDefault);
    }
    if( opts.traceArea != null ){
        $.fn.lottery_traceOptions = opts.traceDefault;
        $(opts.traceArea).loterryTraceArea(opts.traceDefault);
    }
    if( opts.submitbutton == null ){
        $.alert(lottery_lang.data_error);
    }
    $(opts.submitbutton).click(function(){
        $.lottery_submit(this);
    });
};

$.lotteryUI.version = '1.1.0';

//默认配置
$.lotteryUI.defaults = {
    lottery    : 1,  //彩种对应表关系参照上面
    methodtype : 9,  //玩法对应表关系参照上面
    period     : '2009001', //期号
    singleprice: 2,     //单注单倍价格
    singleprize: 1700,  //单倍中奖金额[不变价]
    limitbons  : 0,     //单笔奖金最高限制
    bigcancel  : 0,     //大额撤单起始金额
    canclepre  : 0,     //大额撤单手续费比列
    servertime : '',    //服务器时间,用于时间同步
    endtime    : '',    //结束时间,与服务器时间同步
    timer      : null,  //显示倒计时的位置
    nowIssue   : null,  //显示当前期的位置
    endTimearea: null,  //显示当前期结束时间的位置
    usermoney  : 0,     //会员可用资金
    ajaxurl    : '',    //提交的URL地址,获取下一期的地址是后面加上flag=read,提交是后面加上flag=save
    
    selectArea : null,  //选号区嵌入位置,如果为NULL则不要选号区
    selectDefault:  {//选号区默认设置
                    type        : 'normal',  //选号区类别'normal':普通[数字型],'dxds':大小单双
                    tableCss    : 'lottery_selectarea_table',   //外表格样式
                    layout      : [
                                   {title:'BW', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                   {title:'SW', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1},
                                   {title:'GW', no:'0|1|2|3|4|5|6|7|8|9', place:2, cols:1}
                                  ],   //号码排列
                    titleCss    : 'lottery_selectarea_title',   //标题样式
                    noCss       : 'lottery_selectarea_num',   //号码样式
                    noSelectCss : 'lottery_selectarea_num_check',   //号码选中样式
                    noBigIndex  : 5,    //前面多少个号码是小号,即大号是从多少个以后开始的
                    isButton    : true,                         //是否要按钮[大小单双]
                    buttonCss   : 'lottery_selectarea_button',   //按钮[大小单双清]的样式
                    test   : ''
    },
    
    filterArea : null,  //筛选区嵌入位置,如果为NULL则不要筛选区
    filterDefault: {//筛选区默认设置
                css    : '',
                show   : 'all', //all(所有) | span(跨度) | fixed(定胆)
                span   : ['0','1','2','3','4','5','6','7','8','9'], //跨度选项
                fixed  : ['0','1','2','3','4','5','6','7','8','9']  //定胆选项
    },
    
    editArea   : null,  //编辑区嵌入位置,如果为NULL则不要编辑区
    editDefault: {//编辑区默认设置
                tableCss    : 'lottery_editarea_table',   //表格样式
                textareaCss : 'lottery_editor_textarea',  //文本框样式
                
                button_random_Image : '',                       //机选则按钮的图片URL 
                button_random_text  : lottery_lang.button_random,                   //机选则按钮的文字[如果有图片则以图片为准]
                button_random_CSS   : 'lottery_button_random',  //机选按钮[图片]的CSS样式
                input_random_CSS    : '',          //机选输入框样式
                
                button_import_Image : '',                       //导入文件按钮的图片URL 
                button_import_text  : lottery_lang.button_import, //导入文件按钮的文字[如果有图片则以图片为准]
                button_import_CSS   : 'lottery_button_import',  //导入文件按钮[图片]的CSS样式
                
                button_clear_Image : '',                        //清空按钮的图片URL 
                button_clear_text  : lottery_lang.button_clear, //清空按钮的文字[如果有图片则以图片为准]
                button_clear_CSS   : 'lottery_button_clear',   //清空按钮[图片]的CSS样式
                
                button_insert_Image : '',                        //添加按钮的图片URL 
                button_insert_text  : lottery_lang.button_insert, //添加按钮的文字[如果有图片则以图片为准]
                button_insert_CSS   : 'lottery_button_insert',   //添加按钮[图片]的CSS样式
                
                button_delete_Image : '',												//删除重复号按钮的图片URL
                button_delete_text  : lottery_lang.button_delete,//删除重复号按钮的文字[如果有图片则以图片为准]
                button_delete_CSS   : 'lottery_button_delete'   //删除重复号按钮的CSS
              },    
              
    confirmArea: null,  //确认区嵌入位置,如果为NULL则不要确认区
    confirmsplit : " ", //确认区号码展开后的分隔符
    confirmDefault  : {//确认区默认设置
                        textareaCss : 'lottery_confirm_textarea',//确认框样式
                        jswordCss   : 'lottery_js_word',         //JS显示文字样式
                        inputCss    : 'lottery_input lottery_input_underline'   //倍数输入框样式
                    },   
                    
    traceArea  : null,  //追号区嵌入位置,如果为NULL则不要追号区
    traceDefault: {//追号区默认设置
            periods           : ['2009001','2009002'],//可以追号的最近20期期号
            selectype         : ['sametimes','difftimes','profit','margin'],
            label_css         : 'lottery_tracearea_table_label',  //标签区table样式
            label_front_css   : 'lottery_td_front',               //标签区当前标签的样式
            label_back_css    : 'lottery_td_back',                //标签区其他标签样式
            label_space_css   : 'lottery_td_space',               //标签之间间隔样式
            label_line_css    : 'lottery_td_line',                //标签空白区线条样式
            label_content_css : 'lottery_label_div',              //标签内容区样式
            
            table_css         : 'lottery_tracearea_table',        //追号区带框表格样式
            input_css         : 'lottery_input lottery_input_underline', //追号区输入框样式
            money_css         : 'lottery_tracearea_money',         //追号区游戏币金额样式
            button_image      : '',                                //立即生成按钮图片
            button_text       : lottery_lang.button_trace,         //立即生成按钮文字
            fun_select				: function(){},											//某期选中后的外接函数,参数obj[tr]
            fun_unselect			: function(){},											//取消某期选择后的外接函数 参数obj[tr]				
            button_css        : ''                                //立即生成按钮样式
         },
    submitbutton : null //提交按钮
};
/********************************************************全局函数********************************************************/

/********************************************************全局函数完******************************************************/
/*-===============倒计时=====================--*/
$.lotteryTimer = function( timer, starttime, endtime ){//服务器现在时间,服务器结束时间
    if( starttime == "" || endtime == "" ){
        $.fn.lottery_TimeCount  = 0;
    }else{
        $.fn.lottery_TimeCount  = (format(endtime).getTime()-format(starttime).getTime())/1000;//总秒数
    }
    function fftime(n){
        return Number(n)<10 ? ""+0+Number(n) : Number(n); 
    }
    function format(dateStr){//格式化时间
        return new Date(dateStr.replace(/[\-\u4e00-\u9fa5]/g, "/"));
    }
    function diff(t){//根据时间差返回相隔时间
        return t>0 ? {
			day : Math.floor(t/86400),
			hour : Math.floor(t%86400/3600),
			minute : Math.floor(t%3600/60),
			second : Math.floor(t%60)
		} : {day:0,hour:0,minute:0,second:0};
    }
    var timerno = window.setInterval(function(){
            if( $.fn.lottery_TimeCount <= 0 ){//结束
                clearInterval(timerno);
                if( $.fn.lottery_submiting == false ){//如果没有正在提交数据则弹出对话框,否则主动权交给提交表单
                    $.confirm({
                               message: lottery_lang.msg_timeout,
                               funyes : function(){
                                                $.lotteryRest(false);
                                        },
                               funno  : function(){
                                                $.lotteryRest(true);
                                        } 
                        });
                }
            }
            var oDate = diff($.fn.lottery_TimeCount--);
            $(timer).html(""+(oDate.day>0 ? oDate.day+(lottery_lang.time_day)+" " : "")+fftime(oDate.hour)+":"+fftime(oDate.minute)+":"+fftime(oDate.second));
        },1000);
}

$.lotteryRest = function(keep){//重设
    if( keep && keep === true ){
        keep = true;
    }else{
        keep = false;
    }
    if( $.fn.lottery_TimeCount <= 0 ){ //如果到期则自动转换为读取下期数据[读取下一期信息]
        //读取下一期数据
        $.ajax({
            type: 'POST',
            URL : $.fn.lottery_option.ajaxurl,
            data: "lotteryid="+$.fn.lottery_option.lotteryid+"&flag=read",
            success : function(data){//成功
                        var partn = /<script.*>.*<\/script>/;
                        if( partn.test(data) ){
                            alert(lottery_lang.msg_login);
														top.location.href="../?controller=default";
														return false;
                        }
                        if( data == "empty" ){
                            alert(lottery_lang.msg_saletime);
                            window.location.href="./?controller=default&action=start";
                            return false;
                        }
                        data = eval(data);
                        $.fn.lottery_option.money = Math.floor(Number(data[0].userinfo.availablebalance)*100)/100;
                        if( $.fn.lottery_blocks.nowIssue != null ){//更改当前期
                            $.fn.lottery_option.period = data[0].issue;
                            $($.fn.lottery_blocks.nowIssue).html(data[0].issue);
                            $("#lottery_currentissue").val(data[0].issue);
                        }
                        if( $.fn.lottery_blocks.endTime != null ){//更改当期结束时间
                            $($.fn.lottery_blocks.endTime).html(data[0].saleend);
                        }
                        if( $.fn.lottery_blocks.timer != null ){//重设倒记时
                            $.lotteryTimer($.fn.lottery_blocks.timer,data[0].nowtime,data[0].saleend);
                        }
                        if( $.fn.lottery_blocks.isSelect != null ){//重设选号区
                            if( keep ==  false ){//如果不保留选号记录则清空所有选号记录
                                $("div[name^='lottery_no_']",$($.fn.lottery_blocks.isSelect)).attr("class",$.fn.lottery_noCss.unselect);
                                $.each($.fn.lottery_no, function(i,n){
                                    $.each(n,function(j,m){
                                        if( typeof(m) == 'number' ){
                                            $.fn.lottery_no[i][j] = 0;
                                        }else{
                                            $.fn.lottery_no[i][j] = {num:m.num,ischeck:0};
                                        }
                                    });
                                });
                            }
                        }
                        if( $.fn.lottery_blocks.isfilter != null ){//重设筛选区
                            if( keep == false ){
                                $("#filter_span").attr("checked",false);
                                $("#filter_span_select").attr("disabled",true);
                                $("#filter_fixed").attr("checked",false);
                                $("#filter_fixed_select").attr("disabled",true);
                            }
                        }
                        if( $.fn.lottery_blocks.iseditor != null ){//重设编辑区
                            if( keep ==  false ){
                                $.fn.lottery_no_edit = [];
                                $("#lottery_editor").val("");
                            }
                        }
                        if( $.fn.lottery_blocks.isconfirm != null ){//重设确认区
                            if( keep ==  false ){
                                $("#confirm_editor").val("");
                                $("#lottery_total_order").html(0);  //写入注数
                                $("#lottery_totalnum").val(0);
                                $("#lottery_total_money").html(0);//写入总投注金额
                                $("#lottery_totalamount").val(0);
                                $("#lottery_times").val(1);
                                $.fn.lottery_no_confirm = [];       //确认区号码列表
                                $("#lottery_confirmnums").val("");//写入提交的号码
                                $.fn.lottery_no_count   = 0;        //选择注数统计
                                $.fn.lottery_no_moneycount = 0;     //总金额
                            }
                        }
                        if( $.fn.lottery_blocks.istrace != null ){   //重设追号区
                            var temparr = [];
                            $.each(data[0].taskinfo,function(i,n){
                                temparr.push("'"+n.issue+"'");
                            });
                            $.fn.lottery_traceOptions.periods = eval("["+temparr.join(",")+"]");
                            $($.fn.lottery_blocks.istrace).empty().loterryTraceArea($.fn.lottery_traceOptions);
                        }
                        
            },
            error : function(){//失败
                $.alert(lottery_lang.msg_ajaxerror);
                return false;
            }
        });
    }else{ 
        if( $.fn.lottery_blocks.isSelect != null ){//重设选号区
            if( keep ==  false ){//如果不保留选号记录则清空所有选号记录
                $("div[name^='lottery_no_']",$($.fn.lottery_blocks.isSelect)).attr("class",$.fn.lottery_noCss.unselect);
                $.each($.fn.lottery_no, function(i,n){
                    $.each(n,function(j,m){
                        if( typeof(m) != 'object' ){
                            $.fn.lottery_no[i][j] = 0;
                        }else{
                            $.fn.lottery_no[i][j] = {num:m.num,ischeck:0};
                        }
                    });
                });
            }
        }
        if( $.fn.lottery_blocks.isfilter != null ){//重设筛选区
            if( keep == false ){
                $("#filter_span").attr("checked",false);
                $("#filter_span_select").attr("disabled",true);
                $("#filter_fixed").attr("checked",false);
                $("#filter_fixed_select").attr("disabled",true);
            }
        }
        if( $.fn.lottery_blocks.iseditor != null ){//重设编辑区
            if( keep ==  false ){
                $.fn.lottery_no_edit = [];
                $("#lottery_editor").val("");
            }
        }
        if( $.fn.lottery_blocks.isconfirm != null ){//重设确认区
            if( keep ==  false ){
                $("#confirm_editor").val("");
                $("#lottery_total_order").html(0);  //写入注数
                $("#lottery_totalnum").val(0);
                $("#lottery_total_money").html(0);//写入总投注金额
                $("#lottery_totalamount").val(0);
                $("#lottery_times").val(1);
                $.fn.lottery_no_confirm = [];       //确认区号码列表
                $("#lottery_confirmnums").val("");//写入提交的号码
                $.fn.lottery_no_count   = 0;        //选择注数统计
                $.fn.lottery_no_moneycount = 0;     //总金额
                $("#lottery_adjustcodes").val("");  //变价号码
                $("#lottery_adjustchoice").val(2);  //变价选择
            }
        }
        if( $.fn.lottery_blocks.istrace != null ){   //重设追号区
                $("#trace_check").attr("checked",false);
                $("#trace_stop").attr("disabled",true).attr("checked",false);
                $("#trace_content_area").hide();
                $("#lottery_istrace").val(0);
                $.cleanTrace();
        }
    }
}
$.lottery_submit = function(obj){//提交表单,下单
    function checkTimeOut(){//检查时间是否结束
        if( $.fn.lottery_TimeCount <= 0 ){//当期结束
            $.confirm({
                       message: lottery_lang.msg_timeout,
                       funyes : function(){
                                    $.lotteryRest(false);
                            },
                       funno  : function(){
                                    $.lotteryRest(true);
                            } 
                });
            return false;
        }else{
            return true;   
        }
    }
    if( checkTimeOut() == false ){//检测时间是否结束
        return false;
    }
    $.fn.lottery_submiting = true;  //状态改为正在提交表单
    var confirmmsg = "";    //确认信息
    // louis
    var sign = 0;			// 是否需要倒计时
    // louis
    var tmptotalmoney = 0;	//最后购买的金额
    //提交数据完整性检查
    if( $("#lottery_currentissue").val() != $.fn.lottery_option.period ){//检查当前期完整性
        $.fn.lottery_submiting = false;
        $.alert(lottery_lang.data_error);
        return false;
    }
    if( $("#confirm_editor").val() == "" //确认区检查
        || $("#lottery_confirmnums").val() == ""    //确认号码检查
        || Number($("#lottery_totalnum").val()) <= 0 //总投注注数检查
      ){
        $.fn.lottery_submiting = false;
        $.alert(lottery_lang.msg_sub_t1);
        return false;
    }
    if( Number($("#lottery_totalnum").val()) > 1000 ){
    		$.alert(lottery_lang.msg_sub_t2);
    }
    if( Number($("#lottery_istrace").val()) == 0 ){//非追号
        if( Number($("#lottery_totalamount").val()) <= 0 || Number($("#lottery_times").val()) <= 0 ){//非追号投注总金额检查和倍数检查
            $.fn.lottery_submiting = false;
            $.alert(lottery_lang.msg_sub_t3);
            return false;
        }
        if( Number($("#lottery_totalamount").val()) > $.fn.lottery_option.money ){//超过可用资金
            $.alert(lottery_lang.msg_sub_t4+$.fn.lottery_option.money);
            return false;
        }
        if( ($.fn.lottery_option.prize * Number($("#lottery_times").val())) > $.fn.lottery_option.limitbons ){//奖金超出最大限制额
            $.alert(lottery_lang.msg_sub_t5);
            return false;
        }
        tmptotalmoney = Number($("#lottery_totalamount").val());
        confirmmsg += lottery_lang.msg_sub_t6.replace( "[issue]",$("#lottery_currentissue").val() ).replace( "[codes]",$("#confirm_editor").val() ).replace( "[money]",$("#lottery_totalamount").val() );
        if( Number($("#lottery_totalamount").val()) >= $.fn.lottery_option.bigcancel ){
            confirmmsg += lottery_lang.msg_sub_t7.replace( "[money]",Math.floor((Number($("#lottery_totalamount").val())*$.fn.lottery_option.canclepre)*100)/100 );
            // louis
            sign = 1;
            confirmmsg += "#" + sign;
            // louis
        }
    }else{//追号
        tracecount = 0;
        errorIssue = "";
        outIssue   = "";
        var totalMoney = 0;
        var charge = new Array();
        var tempCharge = "";
        var m = 0;
        $("input[name^='trace_issue']",$("#trace_detail")).each(function(i,n){//检查选择的期号和对应的倍数是否正确
            if( $(n).attr("checked") == true ){
            	totalMoney = Number($("input[name='trace_times_"+$(n).val()+"']").val()) * Number($("#lottery_totalnum").val()) * 2;
            	if ( totalMoney >= $.fn.lottery_option.bigcancel){
            		charge[m] = new Array();
            		charge[m]['issue'] = $(n).val();
            		charge[m]['charge'] = Math.floor((totalMoney * $.fn.lottery_option.canclepre)*100)/100;
            		m++;
            	}
                tracecount++;
                if( Number($("input[name='trace_times_"+$(n).val()+"']").val()) <= 0 ){
                    errorIssue += $(n).val()+" ";
                }
                if( ($.fn.lottery_option.prize * Number($("input[name='trace_times_"+$(n).val()+"']").val())) > $.fn.lottery_option.limitbons ){
                    outIssue += $(n).val()+" ";
                }
            }
        });
        if( tracecount == 0 ){//如果没有选择任何追号期
            $.fn.lottery_submiting = false;
            $.alert(lottery_lang.msg_sub_t8);
            return false;
        }
        if( errorIssue != "" ){//倍数错误
            $.fn.lottery_submiting = false;
            $.alert( lottery_lang.msg_sub_t9.replace( "[errorIssue]",errorIssue ) );
            return false;
        }
        if( outIssue != "" ){//超过奖金限额
            $.fn.lottery_submiting = false;
            $.alert( lottery_lang.msg_sub_t10.replace( "[outIssue]",outIssue ) );
            return false;
        }
        if( Number($("#trace_totalamount").val()) <= 0 ){//追号总金额检查
            $.fn.lottery_submiting = false;
            $.alert(lottery_lang.msg_sub_t11);
            return false;
        }
        if( Number($("#trace_totalamount").val()) > $.fn.lottery_option.money ){//超过可用资金
            $.alert(lottery_lang.msg_sub_t4+$.fn.lottery_option.money);
            return false;
        }
        tmptotalmoney = Number($("#trace_totalamount").val());
        confirmmsg += lottery_lang.msg_sub_t12.replace( "[tracecount]",tracecount ).replace( "[codes]",$("#confirm_editor").val() ).replace( "[money]",$("#trace_totalamount").val() );
        // louis
        if (charge.length > 0){
        	tempCharge = "<br /><font color='red'>敬请注意：如撤单将收取手续费:</font><div style='height:60px;margin:5px;padding:5px;overflow:hidden;overflow-y:auto;'>";
        	tempCharge += "<table width='100%'><tr><td width='50%' align='center'><font color='red'>奖期</font></td><td width='50%' align='center'><font color='red'>撤单手续费</font></td></tr>";
        	for(var j=0; j < charge.length;j++){
        		tempCharge += "<tr><td width='50%' align='center'><font color='blue'>" + charge[j]['issue'] + "</font></td><td width='50%' align='center'><font color='blue'>" + charge[j]['charge'] + "元</font></td></tr>";	
        	}
        	tempCharge += "</table></div>";
        	confirmmsg += lottery_lang.msg_sub_t13.replace( "[charge]", tempCharge );
        }
        if (charge.length > 0){
        	sign = 1;
        }
        confirmmsg += "#" + sign;
        // louis
    }
    $.confirm({
        message : confirmmsg,
        funyes  : function(){
                    ajaxSubmit(obj);
            },
        funno   : function(){
                $.fn.lottery_submiting = false;
                return checkTimeOut();
            }
    });
    //AJAX提交数据
    function ajaxSubmit(obj){
        $.blockUI({
        message: lottery_lang.msg_ajax,
        overlayCSS: {
                    backgroundColor: '#000000',
                    opacity:          0.5,
                    cursor:          'wait'
                }
        });
        var form = $(obj).closest("form");
        $.ajax({
            type: 'POST',
            url : $.fn.lottery_option.ajaxurl,
            timeout : 30000,
            data: $(form).serialize(),
            success: function(data){
//                        alert(data);
//                        $.alert("调试");
                        //return false;
                        $.fn.lottery_submiting = false;
                        var partn = /<script.*>.*<\/script>/;
                        if( partn.test(data) ){
                            alert(lottery_lang.msg_login);
				            				top.location.href="../?controller=default";
				            				return false;
                        }else if( data == "success"){//购买成功
                            $.alert({
                            				title:lottery_lang.msg_adjust_t,
                                    message:lottery_lang.msg_success,
                                    onclose: function(){
                                                if( checkTimeOut() == true ){//没有结束
                                                	  $.fn.lottery_option.money -= tmptotalmoney;
                                                	  //2009-11-13 by Tom Disabled $("#leftusermoney",window.top.frames['leftframe'].document).html(moneyFormat($.fn.lottery_option.money));                                                	  	  
                                                    $.lotteryRest();
                                                    return false;
                                                }
                                        }
                              });
                            return false;
                        }else{//购买失败提示
                            eval("data = "+ data +";");
                            if( data.stats == 'error' ){
                                $.alert({
                                        message:data.data,
                                        onclose:function(){
                                                if( checkTimeOut() == true ){//没有结束
                                                    $("#lottery_adjustcodes").val("");  //变价号码
                                                    $("#lottery_adjustchoice").val(2);  //变价选择
                                                    return false;
                                                }
                                            }
                                });
                            }else if( data.stats == 'adjust' ){//变价提示
                                $("#lottery_adjustcodes").val(data.data.serialdata);
                                adjustConfirm(obj,data.data.codedata);
                            }
                            return false;
                        }
                     },
            error: function(){
                        $.fn.lottery_submiting = false;
                        $.alert({message:lottery_lang.msg_ajax_t,onclose:checkTimeOut});
                     }
        });
    }
    //变价提示
    function adjustConfirm(obj,codedata){
        html  = '<div id="adjust_content" class="lottery_adjust_confirm"><div class="adjusttitlebox" id="adjust_title"><span class="adjusttitle">'+lottery_lang.msg_adjust_t+'</span></div>';
        html += '<div class="adjustmsg">'+lottery_lang.msg_adjust_t1+'</div>';
        html += '<div class="adjustcodebox"><div><table width="'+($.browser.msie ? "93%" : "100%")+'" border="1" cellpadding="3" cellspacing="0"><tr><th>'+lottery_lang.msg_adjust_t2+'</th><th>'+lottery_lang.msg_adjust_t3+'</th></tr>';
        $.each(codedata,function(i,n){
            html += '<tr><td align="center">'+i+'</td><td align="center">'+n+'</td></tr>';
            });
        html += '</table></div></div><div style="width:100%;margin-top:10px;"><ul><li><label for="adjust_choice_1"><input type="radio" name="adjust_choice" id="adjust_choice_1" value="1" checked> '+lottery_lang.msg_adjust_t4+'</label></li><li><label for="adjust_choice_2"><input type="radio" name="adjust_choice" id="adjust_choice_2" value="2"> '+lottery_lang.msg_adjust_t5+'</label></li><li><label for="adjust_choice_0"><input type="radio" name="adjust_choice" id="adjust_choice_0" value="0"> '+lottery_lang.msg_adjust_t6+'</label></li></ul></div><div class="confirmbuttonbox"><input type="button" name="adjust_submit" id="adjust_submit" value="'+lottery_lang.msg_adjust_t7+'"></div></div>';
        $html = $(html);
        $.blockUI({
            message : $html,
            css: {width : '600px',height:'350px', border:"1px #f9d3cb solid",backgroundColor:'#fdeedb'},
            overlayCSS:{backgroundColor: '#FFFFFF',opacity:0.6,cursor:'default'},
            centerX   : true,
            centerY   : true,
            onUnblock : function(){
                        $("#lottery_adjustcodes").val("");  //变价号码
                        $("#lottery_adjustchoice").val(2);  //变价选择
                }
        });
        //$(".blockMs").Drags({handler: '#adjust_title'});
        fullCenter( $(".blockMsg") );
        $("#adjust_submit",$html).click(function(){
            if( $("#adjust_choice_0",$html).attr("checked") == true ){//拒绝
                if( checkTimeOut() == true ){//没有结束                    
                    $.unblockUI({fadeOut:0}); 
                    $.lotteryRest();
                    return false;
                }
            }else if( $("#adjust_choice_1",$html).attr("checked") == true ){//强行购买,不再提示
                $("#lottery_adjustchoice").val(1);
            }else{
                $("#lottery_adjustchoice").val(2);
            }
            ajaxSubmit(obj);
            return false;
        });
    }
    
    function fullCenter(el){//使元素在屏幕中央
        var ie6     = $.browser.msie && /MSIE 6.0/.test(navigator.userAgent);
        var topset  = $(window).height()/2-$(el).height()/2+ (ie6 ? document.documentElement.scrollTop : 0);
        var leftset = $(window).width()/2 - $(el).width()/2+ (ie6 ? document.documentElement.scrollLeft : 0);
        $(el).css({left:leftset+"px",top:topset+"px"});
    }
}
//end closure
})(jQuery);