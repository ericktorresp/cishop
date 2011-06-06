/**
*   彩票购买界面自动生成器
*   依赖JQuery开发，作为其插件形式应用
*		购彩界面主要包括：
*			--信息提示区：截止时间，剩余时间，（管理平台）上次开奖号码
*			--选号区：跟据所选的玩法，列出选择号码的按钮工具。
*			--筛选区：从所选的号码中，选择筛选项，进行筛选。
*			--编辑区：对单注号码可以进行添加、修改、删除、复制、清空、导入文件（随机为从冷号随机选择）
*			--确认区：确认区的注数按从小到大自动排序
*			--自定义号码选项区：
*			--追号区：同倍追号 、翻倍追号 、利润金额追号 、利润率追号
*		
*   现主要考虑低频购买界面。其主要彩种包括3D，P5(P5)，双色球，22选5，七乐彩
*
*		玩法包括:	
*			--3D（直选 、通选 、组选 、不定位 、二码 、定位胆 ）
*			--P3（P3直选、P3通选、P3组选、P3不定位、P5二码、P5定位胆）
*			--P5（P5二码、P5定位胆）
*			--双色球（红球、蓝球、经济型、全保型、定胆型、全蓝型）
*			--22选5（基本号、经济型、全保型、定胆型）
*			--七乐彩（基本号、特别号、经济型、全保型、定胆型）
*
*   @version:   1.0.0
*   @author:    james
*   @Update:    2009/07/3
*   @package:   jquery plugin
*/

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
        $.alert("初始化出错，请刷新页面重试");
    }
    $(opts.submitbutton).click(function(){
        $.lottery_submit(this);
    });
};

$.lotteryUI.version = '1.0.0';

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
                                   {title:'百位', no:'0|1|2|3|4|5|6|7|8|9', place:0, cols:1},
                                   {title:'十位', no:'0|1|2|3|4|5|6|7|8|9', place:1, cols:1},
                                   {title:'个位', no:'0|1|2|3|4|5|6|7|8|9', place:2, cols:1}
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
                button_random_text  : '机选',                   //机选则按钮的文字[如果有图片则以图片为准]
                button_random_CSS   : 'lottery_button_random',  //机选按钮[图片]的CSS样式
                input_random_CSS    : '',          //机选输入框样式
                
                button_import_Image : '',                       //导入文件按钮的图片URL 
                button_import_text  : '导入文件',               //导入文件按钮的文字[如果有图片则以图片为准]
                button_import_CSS   : 'lottery_button_import',  //导入文件按钮[图片]的CSS样式
                
                button_clear_Image : '',                        //清空按钮的图片URL 
                button_clear_text  : '清空',                    //清空按钮的文字[如果有图片则以图片为准]
                button_clear_CSS   : 'lottery_button_clear',   //清空按钮[图片]的CSS样式
                
                button_insert_Image : '',                        //添加按钮的图片URL 
                button_insert_text  : '添加',                    //添加按钮的文字[如果有图片则以图片为准]
                button_insert_CSS   : 'lottery_button_insert'   //添加按钮[图片]的CSS样式
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
            periods           : ['2009001','2009002','2009003','2009004','2009005','2009006','2009007','2009008','2009009','2009010','2009011','2009012','2009013','2009014','2009015','2009016','2009017','2009018','2009019','2009020'],//可以追号的最近20期期号
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
            button_text       : '立即生成',                        //立即生成按钮文字
            button_css        : ''                                //立即生成按钮样式
         },
    submitbutton : null //提交按钮
};

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
        $.alert("参数错误");
        return false;
    }
}
$.lottery_randomNum = function(num){//机选
    lottery_type   = $.fn.lottery_option.lottery;//彩种
    lottery_method = $.fn.lottery_option.method;//方法
    num = Number(num) > 0 ? Number(num) : 0;
    if( num == 0 ){
        return ([]);
    }
    function getRandom(n,m){    //产生n到m的一个随机数
        return Math.floor(Math.random()*m+n);
    }
    function random3DZX(m){ //3D直选机选
        var numbers = [];                   //机选的号码集合
        m = Number(m)>1000 ? 1000 : parseInt(m,10);
        for( i=0; i<m; i++ ){//循环产生随机号码
            temp = getRandom(0,999).toString();
            if( temp.length == 1 ){
            	temp = "00"+temp;
            }else if( temp.length == 2 ){
            	temp = "0"+temp;
            }
            if( $.inArray(temp,numbers) == -1 ){
            	numbers.push(temp);
            }else{
            	i--;
            }
        }
        numbers.sort();
        return numbers;
    }
    function random3DHHZX(m){
        var num = [0,1,2,3,4,5,6,7,8,9];    //可以选的号码
        var numbers = [];                   //机选的号码集合
        var temp_num= [];   //单注号码集合
        m = Number(m)>990 ? 990 : Number(m);
        if( m > 0 ){
            for( i=0; i<m; i++ ){//循环产生随机号码
                temp_num= [];
                for(j=0; j<3; j++){
                    temp_num.push(num[getRandom(0,9)]);
                }
                if( (temp_num[0] == temp_num[1] && temp_num[1]==temp_num[2]) || $.inArray(temp_num.join(""),numbers) != -1 ){//不能为豹子
                    i--;
                }else{
                    numbers.push(temp_num.sort().join(""));
                }
            }
        }
        numbers.sort();
        return numbers;
    }
    if( lottery_type == '3D' || lottery_type == 'P5' ){//3D
        switch( lottery_method.toUpperCase() ){//根据玩法不同机选号码形式不同
            case 'ZX'   : return random3DZX(num); break;
            case 'TX'   : return random3DZX(num); break;
            case 'HHZX' : return random3DHHZX(num); break;
        }
    }else if( lottery_type == 'XSQ' ){//双色球
        
    }else if( lottery_type == '225' ){//22选5
        
    }else if( lottery_type == 'QLC' ){//七乐彩
        
    }else{
        $.alert("参数错误");
        return false;
    }
}
$.lottery_expandNum = function(){//根据已选择号码展开显示在编辑区或者确认区[根据彩种和玩法不同做不同处理]
    lottery_type   = $.fn.lottery_option.lottery;//彩种
    lottery_method = $.fn.lottery_option.method;//方法
    //3D直选,P3直选展开
    function _expandZX(){
        if( $.fn.lottery_no.length < 3 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        first  = new Array();
        second = new Array();
        third  = new Array();
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){first.push(Number(i));}
        });
        $.each($.fn.lottery_no[1], function(i,n){
            if( n == 1 ){second.push(Number(i));}
        });
        $.each($.fn.lottery_no[2], function(i,n){
            if( n == 1 ){third.push(Number(i));}
        });
        if( $.fn.lottery_blocks.isfilter != null ){//如果有筛选区
            isfilter_span  = $("#filter_span").attr("checked");
            span_value     = Number($("#filter_span_select").val());
            isfilter_fixed = $("#filter_fixed").attr("checked");
            fixed_value    = Number($("#filter_fixed_select").val());
        }
        if( first.length > 0 && second.length >0 && third.length > 0 ){//必须三个位上都有选择才生成
            for( i=0; i<first.length; i++ ){
                for( j=0; j<second.length; j++ ){
                    for( k=0; k<third.length; k++ ){
                        temp_num = ""+first[i]+second[j]+third[k];
                        if( $.fn.lottery_blocks.isfilter != null ){//如果有筛选区
                            if( isfilter_span == true ){//跨度
                                if( (Math.max(first[i],second[j],third[k]) - Math.min(first[i],second[j],third[k])) != span_value ){
                                    temp_num = "";
                                }
                            }
                            if( isfilter_fixed == true ){//定胆
                                if( first[i]!=fixed_value && second[j]!=fixed_value && third[k]!=fixed_value  ){
                                    temp_num = "";
                                }
                            }
                        }
                        if( temp_num != "" ){
                            $.fn.lottery_no_edit.push(""+first[i]+second[j]+third[k]);
                        }
                    }
                }
            }
        }
    }
    function _expandZXHZ(){//直选和值展开
        if( $.fn.lottery_no.length != 1 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        first  = new Array();
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){first.push(Number(i));}
        });
        if( first.length > 0 ){
            if( $.fn.lottery_blocks.isfilter != null ){//如果有筛选区
                isfilter_span  = $("#filter_span").attr("checked");
                span_value     = Number($("#filter_span_select").val());
                isfilter_fixed = $("#filter_fixed").attr("checked");
                fixed_value    = Number($("#filter_fixed_select").val());
            }
            for( i=0; i<10; i++ ){
                for( j=0; j<10; j++ ){
                    for( k=0; k<10; k++ ){
                        temp_num = ""+i+j+k;
                        if( $.fn.lottery_blocks.isfilter != null ){
                            if( isfilter_span == true ){//跨度
                                if( (Math.max(i,j,k) - Math.min(i,j,k)) != span_value ){
                                    temp_num = "";
                                }
                            }
                            if( isfilter_fixed == true ){//定胆
                                if( i!=fixed_value && j!=fixed_value && k!=fixed_value  ){
                                    temp_num = "";
                                }
                            }
                        }
                        if( temp_num != "" ){
                            if( $.inArray((i+j+k),first) != -1 ){
                            	  $.fn.lottery_no_edit.push(temp_num);
                            }
                        }
                    }
                }
            }
        }
    }
    function _expandZS(){//组三展开[两位必须,不拆分]
        if( $.fn.lottery_no.length != 1 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        first  = new Array();
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){first.push(Number(i));}
        });
        if( first.length > 1 ){
            $.fn.lottery_no_edit.push(first.join(""));
        }
    }
    function _expandZL(){//组六展开[三位必须,不拆分]
        if( $.fn.lottery_no.length != 1 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        first  = new Array();
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){first.push(Number(i));}
        });
        if( first.length > 2 ){
            $.fn.lottery_no_edit.push(first.join(""));
        }
    }
    function _expandZUXHZ(){//组选和值展开/一码不定位
        if( $.fn.lottery_no.length != 1 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){$.fn.lottery_no_edit.push(Number(i));}
        });
    }
    function _expandQEZX(){//前二直选展开
        if( $.fn.lottery_no.length != 2 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        first  = new Array();
        second = new Array();
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){first.push(Number(i));}
        });
        $.each($.fn.lottery_no[1], function(i,n){
            if( n == 1 ){second.push(Number(i));}
        });
        if( first.length > 0 && second.length >0 ){//必须两个位上都有选择才生成
            $.fn.lottery_no_edit.push(first.join(""));
            $.fn.lottery_no_edit.push(second.join(""));
        }
    }
    function _expandDXDS(){//大小单双,特殊形式展开
        if( $.fn.lottery_no.length != 2 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        first  = new Array();
        second = new Array();
        $.each($.fn.lottery_no[0], function(i,n){
            if( n.ischeck == 1 ){first.push(n.num);}
        });
        $.each($.fn.lottery_no[1], function(i,n){
            if( n.ischeck == 1 ){second.push(n.num);}
        });
        if( first.length > 0 && second.length >0 ){//必须两个位上都有选择才生成
            $.fn.lottery_no_edit.push(first.join(""));
            $.fn.lottery_no_edit.push(second.join(""));
        }
    }
    function _expandYQRX(){//一球任选[超过9后的彩种]
        if( $.fn.lottery_no.length != 1 ){
            $.alert("初始号码错误");
            return false;
        }
        $.fn.lottery_no_edit = [];
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){
                if( i< 10 ){
                    $.fn.lottery_no_edit.push(""+0+Number(i));
                }else{
                    $.fn.lottery_no_edit.push(Number(i));
                }
            }
        });
    }
    //
    if( lottery_type == '3D' || lottery_type == 'P5' ){//3D/P3/P5
        switch( lottery_method.toUpperCase() ){//根据玩法不同展开方式不同
            case 'ZX'   : _expandZX();   break;
            case 'ZXHZ' : _expandZXHZ(); break;
            case 'TX'   : _expandZX();   break;
            case 'ZS'   : _expandZS();   break;
            case 'ZL'   : _expandZL();   break;
            case 'HHZX' : break;
            case 'ZUXHZ': _expandZUXHZ();break;
            case 'YMBDW': _expandZUXHZ();break;
            case 'EMBDW': _expandZS();   break;
            case 'QEZX' : _expandQEZX(); break;
            case 'QEZUX': _expandZS();   break;
            case 'HEZX' : _expandQEZX(); break;
            case 'HEZUX': _expandZS();   break;
            case 'QEDXDS' : _expandDXDS(); break;
            case 'HEDXDS' : _expandDXDS(); break;
            case 'DWD'  : _expandZUXHZ(); break;
            default     : break;
        }
    }else if( lottery_type == 'XSQ' ){//双色球
        switch( lottery_method.toUpperCase() ){//根据玩法不同展开方式不同
            case 'YQRX'   : _expandYQRX();   break;
            default     : break;
        }
    }else if( lottery_type == '225' ){//22选5
        
    }else if( lottery_type == 'QLC' ){//七乐彩
        
    }else{
        $.alert("参数错误");
        return false;
    }
    if( $.fn.lottery_blocks.iseditor != null ){//如果有编辑区
        $("#lottery_editor").val($.fn.lottery_no_edit.join(" "));//写入编辑区
    }else{//没有就直接写进确认区
        $.fn.lottery_no_confirm = $.fn.lottery_no_edit;
        $("#confirm_editor").val($.fn.lottery_no_confirm.join($.fn.lottery_option.confirmsplit));
        $("#confirm_editor").change();
    }
}

$.lottery_checkNum =  function(){   //验证号码合法性[验证编辑区],成功返回true,失败错误号码数组
    lottery_type   = $.fn.lottery_option.lottery; //彩种
    lottery_method = $.fn.lottery_option.method;  //方法
    //3D,P3,直选号码检测
    function _checkZX(){
        if( $.fn.lottery_no_edit.length == 0 ){//如果编辑区为空,则直接返回true
            return true;
        }
        errorNums = [];
        partn     = /^[0-9]{3}$/;
        $.each($.fn.lottery_no_edit, function(i,n){
            n = $.trim(n);
            if( partn.test(n) ){
                $.fn.lottery_no_edit[i] = n;
            }else{
                errorNums.push(n);
            }
        });
        if( errorNums.length > 0 ){ //存在不合法号码
            return errorNums;
        }else{  return true; }
    }
    function _checkHHZX(){  //混合组选
        if( $.fn.lottery_no_edit.length == 0 ){//如果编辑区为空,则直接返回true
            return true;
        }
        errorNums = [];
        partn     = /^[0-9]{3}$/;
        partnb    = /(\d)\1{2,}/; //豹子号
        $.each($.fn.lottery_no_edit, function(i,n){
            n = $.trim(n);
            if( partn.test(n) && !partnb.test(n) ){
                tempnnnnn = n.split("");
                tempnnnnn.sort();
                $.fn.lottery_no_edit[i] = tempnnnnn.join("");
            }else{
                errorNums.push(n);
            }
        });
        if( errorNums.length > 0 ){ //存在不合法号码
            return errorNums;
        }else{  return true; }
    }
    
    function _checkDuplicate(){ //检查号码重复状态
        var len = $.fn.lottery_no_edit.length;
        if( len == 0 ){
            return true
        }
        duplicateNum = [];
        num     = "";
        count   = 0;
        for( i=0; i<len; i++ ){
            num = $.fn.lottery_no_edit[i];
            count = 1;
            ischeck = false;
            for( k=0; k<duplicateNum.length; k++ ){
                if( duplicateNum[k].num == num ){
                    ischeck = true; break;
                }
            }
            if( ischeck == false ){
                for( j=i+1; j<len; j++ ){
                    if( $.fn.lottery_no_edit[j] == num ){
                        count += 1;
                    }
                }
            }
            if( count > 1 ){
                duplicateNum.push({num:num,count:count});
            }
        }
        if( duplicateNum.length > 0 ){ //存在重复号码
            return duplicateNum;
        }else{  return true; }
    }
    
    //根据参数来检测
    if( lottery_type == '3D' || lottery_type == 'P5' ){//3D
        switch( lottery_method.toUpperCase() ){//根据玩法检测方式不同
            case 'ZX'   : 
            case 'ZXHZ' : 
            case 'TX'   :
                        error = _checkZX();
                        if( error !== true ){
                            return ({err:'error',nums:error});
                        }else{
                            error = _checkDuplicate();
                            if( error !== true ){
                                return ({err:'dup',nums:error});
                            }else{
                                return true;
                            }
                        }
                        break;
            case 'HHZX':
                        error = _checkHHZX();
                        if( error !== true ){
                            return ({err:'error',nums:error});
                        }else{
                            error = _checkDuplicate();
                            if( error !== true ){
                                return ({err:'dup',nums:error});
                            }else{
                                return true;
                            }
                        }
                        break;
            default : return true; break;
        }
    }else if( lottery_type == 'XSQ' ){//双色球
        
    }else if( lottery_type == '225' ){//22选5
        
    }else if( lottery_type == 'QLC' ){//七乐彩
        
    }else{
        $.alert("参数错误");
        return false;
    }
}
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
                    msg = "当前期结束，是否刷新页面?<br /><br />要刷新页面请点击\"确定\"，不刷新页面请点击\"取消\"";
                    $.confirm({
                               message: msg,
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
            $(timer).html(""+(oDate.day>0 ? oDate.day+"天 " : "")+fftime(oDate.hour)+":"+fftime(oDate.minute)+":"+fftime(oDate.second));
        },1000);
}
/******************************选号区******************************/
$.fn.loterrySelectArea = function(opts){
    opts      = $.extend( {}, $.lotteryUI.defaults.selectDefault, opts || {} ); //根据参数初始化默认配置
    opts.type = opts.type.toLowerCase();
    $.fn.lottery_noCss = {select:opts.noCss,unselect:opts.noCss};
    var selectHtml = "<table border='0' cellpadding='0' cellspacing='0' class='"+opts.tableCss+"'>";
    if( opts.type == 'normal' ){    //普通选号模式
        if( opts.layout != "" && opts.layout.length > 0 ){//选号
            $.each(opts.layout, function(i,n){
            if(typeof(n)=='object'){
                n.place  = Number(n.place);
                selectHtml += "<tr>";
                if( n.cols > 0 ){//有标题
                    selectHtml += "<td style='padding-right:20px;' rowspan='"+n.cols+"'><span class='"+opts.titleCss+"'>"+n.title+"</span></td>";
                }
                selectHtml += "<td>";
                numbers = n.no.split("|");
                $.fn.lottery_no[n.place] = [];
                for( i=0; i<numbers.length; i++ ){
                    selectHtml += "<div class='"+opts.noCss+"' name='lottery_no_"+n.place+"'>"+numbers[i]+"</div> ";
                    $.fn.lottery_no[n.place][numbers[i]] = 0;
                }
                if( opts.isButton == true ){
                    selectHtml += "&nbsp;&nbsp;<button type='button' class='"+opts.buttonCss+"' name='all'>全</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='big'>大</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='small'>小</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='odd'>奇</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='even'>偶</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='clean'>清</button>";
                }
                selectHtml += "</td></tr>";
            }
            });
        }
    }else if( opts.type == 'dxds' ){    //特殊选号模式
        if( opts.layout != "" && opts.layout.length > 0 ){//选号
            $.each(opts.layout, function(i,n){
                n.place  = Number(n.place);
                selectHtml += "<tr>";
                if( n.cols > 0 ){//有标题
                    selectHtml += "<td style='padding-right:20px;' rowspan='"+n.cols+"'><span class='"+opts.titleCss+"'>"+n.title+"</span></td>";
                }
                selectHtml += "<td>";
                numbers = n.no.split("|");
                $.fn.lottery_no[n.place] = [];
                for( i=0; i<numbers.length; i++ ){
                    selectHtml += "<div class='"+opts.noCss+"' name='lottery_no_"+n.place+"'>"+numbers[i]+"</div> ";
                    $.fn.lottery_no[n.place].push({num:numbers[i],ischeck:0});
                }
                selectHtml += "</td></tr>";
            });
        }
    }
    selectHtml += "</table>";
    $(selectHtml).appendTo(this);
    

    //选择号码
    function selectNum(obj,isbutton){
        if( $(obj).attr("class") == opts.noCss ){//本身未选择时才做取消处理
            $(obj).attr("class",opts.noSelectCss);
            place = Number($(obj).attr("name").replace("lottery_no_",""));
            if( opts.type == 'normal' ){
                number= Number($(obj).html());
                $.fn.lottery_no[place][number] = 1;
            }else if( opts.type == 'dxds' ){
                number = $.trim($(obj).html());
                for( i=0; i<$.fn.lottery_no[place].length; i++ ){
                    if( $.fn.lottery_no[place][i].num == number ){
                        $.fn.lottery_no[place][i] = {num:number,ischeck:1};
                    }
                }
            }
            if( isbutton != true ){
            	$.lottery_expandNum();
            }
        }
    }
    //取消号码选择
    function unselectNum(obj,isbutton){
        if( $(obj).attr("class") == opts.noSelectCss ){//本身选择时才做取消处理
            $(obj).attr("class",opts.noCss);
            place = Number($(obj).attr("name").replace("lottery_no_",""));
            if( opts.type == 'normal' ){
                number= Number($(obj).html());
                $.fn.lottery_no[place][number] = 0;
            }else if( opts.type == 'dxds' ){
                number = $.trim($(obj).html());
                for( i=0; i<$.fn.lottery_no[place].length; i++ ){
                    if( $.fn.lottery_no[place][i].num == number ){
                        $.fn.lottery_no[place][i] = {num:number,ischeck:0};
                    }
                }
            }
            if( isbutton != true ){
            	$.lottery_expandNum();
          	}
        }
    }
    //选择与取消号码选择交替变化
    function changeNoCss(obj){
        if( $(obj).attr("class") == opts.noSelectCss ){
            unselectNum(obj,false);
        }else{
            selectNum(obj,false);
        }
    }
    //选择奇数号码
    function selectOdd(obj){
        if( Number($(obj).html()) % 2 == 1 ){
             selectNum(obj,true);
        }else{
             unselectNum(obj,true);
        }
    }
    //选择偶数号码
    function selectEven(obj){
        if( Number($(obj).html()) % 2 == 0 ){
             selectNum(obj,true);
        }else{
             unselectNum(obj,true);
        }
    }
    //选则大号
    function selectBig(i,obj){
        if( i >= opts.noBigIndex ){
            selectNum(obj,true);
        }else{
            unselectNum(obj,true);
        }
    }
    //选择小号
    function selectSmall(i,obj){
        if( i < opts.noBigIndex ){
            selectNum(obj,true);
        }else{
            unselectNum(obj,true);
        }
    }
    //设置号码事件
    $(this).find("div[name^='lottery_no_']").click(function(){
        changeNoCss(this);
    });
    if( opts.isButton == true ){
        //全大小单双清按钮事件
        $("button[class='"+opts.buttonCss+"']",$(this)).click(function(){
            switch( $(this).attr('name') ){
                case 'all'   :
                             isAll = true; 
                             $.each($(this).prevAll("div"),function(i,n){
                                if( $(n).attr("class") == opts.noCss ){//有未选择的号码
                                    isAll = false;
                                }
                             });
                             $.each($(this).prevAll("div"),function(i,n){
                                if( isAll == false ){//有未选择的号码
                                    selectNum(n,true);
                                }else{
                                    unselectNum(n,true);
                                }
                             });
                             break;
                case 'big'   :
                            $.each($(this).parent().children("div"),function(i,n){
                                selectBig(i,n);
                             });break;
                case 'small' :
                             $.each($(this).parent().children("div"),function(i,n){
                                selectSmall(i,n);
                             });break;
                case 'odd'   :
                             $.each($(this).prevAll("div"),function(i,n){
                                selectOdd(n);
                             });break;
                case 'even'  :
                             $.each($(this).prevAll("div"),function(i,n){
                                selectEven(n);
                             });break;
                case 'clean' :
                             $.each($(this).prevAll("div"),function(i,n){
                                unselectNum(n,true);
                             });break;
            }
            $.lottery_expandNum();
        });
    }
};


/*****************************筛选区********************************/
$.fn.loterryFilterArea = function(opts){
    opts      = $.extend( {}, $.lotteryUI.defaults.filterDefault, opts || {} ); //根据参数初始化默认配置
    opts.show = opts.show.toLowerCase();
    var filterHtml = "<ul class='"+opts.css+"'><li>";
    if( opts.show == 'all' || opts.show == 'span' ){//跨度
        filterHtml += "<input type='checkbox' name='filter_span' id='filter_span' />跨度<select disabled='disabled' name='filter_span_select' id='filter_span_select'>";
        $.each(opts.span,function(i,n){
            filterHtml += "<option value='"+Number(n)+"'>"+n+"</option>";
        });
        filterHtml += "</select>";
    }
    if( opts.show == 'all' || opts.show == 'fixed' ){//定胆
        filterHtml += "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='filter_fixed' id='filter_fixed' />定胆<select disabled='disabled' name='filter_fixed_select' id='filter_fixed_select'>";
        $.each(opts.fixed,function(i,n){
            filterHtml += "<option value='"+Number(n)+"'>"+n+"</option>";
        });
        filterHtml += "</select>";
    }
    filterHtml += "</li></ul>";
    $(filterHtml).appendTo(this);
    //绑定事件
    if( opts.show == 'all' || opts.show == 'span' ){//跨度
        $("#filter_span").click(function(){
            if( $(this).attr("checked") == true ){
                $("#filter_span_select").attr("disabled",false);
                $.lottery_expandNum();
            }else{
                $("#filter_span_select").attr("disabled",true);
                $.lottery_expandNum();
            }
        });
        $("#filter_span_select").change(function(){
            $.lottery_expandNum();
        });
    }
    if( opts.show == 'all' || opts.show == 'fixed' ){//定胆
        $("#filter_fixed").click(function(){
            if( $(this).attr("checked") == true ){
                $("#filter_fixed_select").attr("disabled",false);
                $.lottery_expandNum();
            }else{
                $("#filter_fixed_select").attr("disabled",true);
                $.lottery_expandNum();
            }
        });
        
        $("#filter_fixed_select").change(function(){
            $.lottery_expandNum();
        });
    }
};


/*****************************编辑区********************************/
$.fn.loterryEditArea = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults.editDefault, opts || {} ); //根据参数初始化默认配置
    var editAreaHtml = "<table class=\""+opts.tableCss+"\"><tr><td width='450'><textarea name=\"editor\" id=\"lottery_editor\" class=\""+opts.textareaCss+"\"></textarea><br /><span style='color:#666666'>每个号码之间请用一个 空格[ ]、逗号[,] 或者 分号[;] 隔开</span></td><td>&nbsp;</td><td id=\"lottery_editor_buttons\"><ul>";
    if( opts.button_random_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button "+opts.button_random_CSS+"\" id=\"random_button\" title=\""+opts.button_random_text+"\">"+opts.button_random_text+"</button>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_random_Image+"\" id=\"random_button\" class=\""+opts.button_random_CSS+"\" title=\""+opts.button_random_text+"\" />";
    }
    editAreaHtml += "  <select name=\"random_input\" id=\"random_input\" class=\""+opts.input_random_CSS+"\"><option value='1'>1</option><option value='2'>2</option><option value='5'>5</option><option value='10'>10</option><option value='15'>15</option><option value='20'>20</option></select>注</li>";
    if( opts.button_import_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_import_CSS+"\" id=\"import_button\" title=\""+opts.button_import_text+"\">"+opts.button_import_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_import_Image+"\" id=\"import_button\" class=\""+opts.button_import_CSS+"\" title=\""+opts.button_import_text+"\" /></li>";
    }
    if( opts.button_clear_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_clear_CSS+"\" id=\"clear_button\" title=\""+opts.button_clear_text+"\">"+opts.button_clear_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_clear_Image+"\" id=\"clear_button\" class=\""+opts.button_clear_CSS+"\" title=\""+opts.button_clear_text+"\" /></li>";
    }
    if( opts.button_insert_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_insert_CSS+"\" id=\"insert_button\" title=\""+opts.button_insert_text+"\">"+opts.button_insert_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_insert_Image+"\" id=\"insert_button\" class=\""+opts.button_insert_CSS+"\" title=\""+opts.button_insert_text+"\" /></li>";
    }
    editAreaHtml += "</ul></td></tr></table>";
    $(editAreaHtml).appendTo(this);
    
    //绑定事件
    $("#lottery_editor").change(function(){//
        temp_str = $.trim($(this).val());
        temp_str = temp_str.replace(/[\s\r,;，；　]/g,"|").replace(/(\|)+/g,"|");
        temp_str = temp_str.replace(/０/g,"0").replace(/１/g,"1").replace(/２/g,"2").replace(/３/g,"3").replace(/４/g,"4").replace(/５/g,"5").replace(/６/g,"6").replace(/７/g,"7").replace(/８/g,"8").replace(/９/g,"9");
        $.fn.lottery_no_edit = temp_str.split("|");
    });
    $("#random_button").click(function(){   //机选号码
        num = Number($("#random_input").val());
        numbers = $.lottery_randomNum(num);
        $("#lottery_editor").val($("#lottery_editor").val()== "" ? numbers.join(" ") : $("#lottery_editor").val()+" "+numbers.join(" ")).change();
    });
    $("#import_button").click(function(){   //从文件中导入号码
        $.ajaxUploadUI({
                        message :"请选择要导入的文件",
						url	    : './js/dialogUI/fileupload.php',
						dataType: 'html',
						filetype: ['txt','csv'],
						title	: '导入文件',
						success : function(data){
									$("#lottery_editor").val(data).change();
								},
						loadtext: "正在载入..."
            });
    });
    $("#clear_button").click(function(){//清空
        $.fn.lottery_no_edit = [];
        $("#lottery_editor").val($.fn.lottery_no_edit);
    });
    $("#insert_button").click(function(){//添加
        //检验号码合法性
        result = $.lottery_checkNum();
        if( result === true ){  //合法
            $.fn.lottery_no_confirm = $.fn.lottery_no_edit;
            $("#confirm_editor").val($.fn.lottery_no_confirm.join($.fn.lottery_option.confirmsplit));
            $("#confirm_editor").change();
        }else{
            if( result.err == 'error' ){
                $.alert("以下号码错误,请重新编辑后再添加<br><br>  "+result.nums.join(" "));
            }else if( result.err == 'dup' ){
                msg ="以下号码重复:<br>";
                $.each(result.nums,function(i,n){
                    msg += "<br>&nbsp;&nbsp;&nbsp;&nbsp;号码 <font color='red'>"+n.num+"</font> 在订单中出现了 <font color='red'>"+n.count+"</font> 次 ";
                });
                $.confirm({
                           message: msg+"<br><br>您确认要添加吗?",
                           funyes : function(){
                                        $.fn.lottery_no_confirm = $.fn.lottery_no_edit;
                                        $("#confirm_editor").val($.fn.lottery_no_confirm.join($.fn.lottery_option.confirmsplit));
                                        $("#confirm_editor").change();
                                },
                           funno  : function(){} 
                 });
            }
            return false;
        }
    });
    /*函数调用*/
    
};


/*****************************确认区********************************/
$.fn.loterryConfirmArea = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults.confirmDefault, opts || {} ); //根据参数初始化默认配置
    var $editor = $("<textarea name=\"confirm_editor\" id=\"confirm_editor\" class=\""+opts.textareaCss+"\" readonly=\"readonly\"></textarea><input type='hidden' name='lottery_confirmnums' id='lottery_confirmnums' value=''><input type='hidden' name='lottery_adjustcodes' id='lottery_adjustcodes' value=''><input type='hidden' name='lottery_adjustchoice' id='lottery_adjustchoice' value='2'>");
    var $other  = $("<div class='lottery_confirm_list'><input type='hidden' name='lottery_currentissue' id='lottery_currentissue' value='"+$.fn.lottery_option.period+"'>总注数:<input type='hidden' name='lottery_totalnum' id='lottery_totalnum' value='0'><span id=\"lottery_total_order\" class=\""+opts.jswordCss+"\">0</span>,  &nbsp;&nbsp;总金额:<input type='hidden' name='lottery_totalamount' id='lottery_totalamount' value='0'><span id=\"lottery_total_money\" class=\""+opts.jswordCss+"\">0</span> 游戏币, &nbsp;&nbsp;请填写倍数:<input type=\"text\" id=\"lottery_times\" name='lottery_times' class=\""+opts.inputCss+"\" value='1' /> 倍</div>");
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
        cleanTrace();
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
};


/*****************************追号区********************************/
$.fn.loterryTraceArea = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults.traceDefault, opts || {} ); //根据参数初始化默认配置
    var traceHtml = "<table border='0' cellpadding='0' cellspacing='0' style='width:100%;'>";
    traceHtml += "<tr><td height='30' align='left'>&nbsp;&nbsp;<input type='checkbox' name='trace_check' id='trace_check' value='1' /><input type='hidden' name='lottery_istrace' id='lottery_istrace' value='0'><input type='hidden' name='trace_totalamount' id='trace_totalamount' value='0'> 我要追号&nbsp;&nbsp;&nbsp;<input type='checkbox' name='trace_stop' id='trace_stop' value='1' disabled /> 中奖后停止追号</td></tr>";
    traceHtml += "<tr><td align='center' id='trace_content_area' style='display:none;'><input type='hidden' name='trace_type' id='trace_type' value='sametimes'>";
    //标签
    traceHtml += "<table class='"+opts.label_css+"' cellpadding='0' cellspacing='0'><tr>";
    $.each(opts.selectype, function(i,n){
        if( i == 0 ){
            labelcss = opts.label_front_css;
        }else{ labelcss = opts.label_back_css; }
        switch(n){
            case 'sametimes' :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_sametimes'>同倍追号</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            case 'difftimes' :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_difftimes'>翻倍追号</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            case 'profit'    :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_profit'>利润金额追号</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
                break;
            case 'margin'     :
                traceHtml += "<td class='"+labelcss+"' id='trace_label_margin'>利润率追号</td><td class='"+opts.label_space_css+"'>&nbsp;</td>";
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
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_sametimes_text' "+dispaly+">倍数:<input type='text' class='"+opts.input_css+"' name='trace_sametimes' id='trace_sametimes' />&nbsp;&nbsp;&nbsp;&nbsp;追号期数:<input type='text' class='"+opts.input_css+"' name='trace_issues_sametimes' id='trace_issues_sametimes' />&nbsp;&nbsp;&nbsp;&nbsp;追号总金额: <span class='"+opts.money_css+"'>0</span> 游戏币</div>";
                break;
            case 'difftimes' :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_difftimes_text' "+dispaly+">隔 <input type='text' class='"+opts.input_css+"' name='trace_step_difftimes' id='trace_step_difftimes' /> 期&nbsp;&nbsp;&nbsp;&nbsp;倍数 × <input type='text' class='"+opts.input_css+"' name='trace_difftimes' id='trace_difftimes' />&nbsp;&nbsp;&nbsp;&nbsp;追号总期数:<input type='text' class='"+opts.input_css+"' name='trace_issues_difftimes' id='trace_issues_difftimes' />&nbsp;&nbsp;&nbsp;&nbsp;追号总金额: <span class='"+opts.money_css+"'>0</span> 游戏币</div>";
                break;
            case 'profit'    :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_profit_text' "+dispaly+">起始倍数: <input type='text' class='"+opts.input_css+"' name='trace_profittimes' id='trace_profittimes' />&nbsp;&nbsp;&nbsp;&nbsp;追号期数:<input type='text' class='"+opts.input_css+"' name='trace_issues_profit' id='trace_issues_profit' />&nbsp;&nbsp;&nbsp;&nbsp;最低利润:<input type='text' class='"+opts.input_css+"' name='trace_profit_min' id='trace_profit_min' /> 游戏币&nbsp;&nbsp;&nbsp;&nbsp;追号总金额: <span class='"+opts.money_css+"'>0</span> 游戏币</div>";
                break;
            case 'margin'     :
                traceHtml += "<div class='"+opts.label_content_css+"' id='trace_label_margin_text' "+dispaly+">起始倍数: <input type='text' class='"+opts.input_css+"' name='trace_margintimes' id='trace_margintimes' />&nbsp;&nbsp;&nbsp;&nbsp;追号期数:<input type='text' class='"+opts.input_css+"' name='trace_issues_margin' id='trace_issues_margin' />&nbsp;&nbsp;&nbsp;&nbsp;最低利润率:<input type='text' class='"+opts.input_css+"' name='trace_margin_min' id='trace_margin_min' value='50' /> %&nbsp;&nbsp;&nbsp;&nbsp;追号总金额: <span class='"+opts.money_css+"'>0</span> 游戏币</div>";
                break;
            default : break;
        }
    });
    traceHtml += "</td><td align='center' width='100'>";
    if( opts.button_image == '' ){
        traceHtml += "<button type='button' class='"+opts.button_css+"' id='trace_button'>"+opts.button_text+"</button>";
    }else{
        traceHtml += "<img src='"+opts.button_image+"' class='"+opts.button_css+"' title='"+opts.button_text+"' id='trace_button' style='cursor:pointer;' />";
    }
    traceHtml += "</td></tr></table>";
    //追号详情
    traceHtml += "<div style='height:200px;overflow:auto;overflow-x:hidden;'><table class='"+opts.table_css+"' cellpadding='0' cellspacing='0' style='width:97%;' id='trace_detail'><tr><td colspan='6' align='center' bgcolor='#FF6666' style='color:#000;'>可选期号</td></tr><tr><td height='20' class='titletd'>期号</td><td class='titletd'>倍数</td><td class='titletd'>投注金额</td><td class='titletd'>中奖金额</td><td class='titletd'>利润(游戏币)</td><td class='titletd'>利润率(%)</td></tr>";
    var len = opts.periods.length;
    $.fn.lottery_periodArr = [];//每期的数据
    for( i=0; i < len; i++ ){
        if( opts.periods[i] != "" ){
            $.fn.lottery_periodArr.push({issue:opts.periods[i],ischecked:false,times:0,money_buy:0,money_prize:0,money_profit:0,money_percent:0});//初始追号任务表
            traceHtml += "<tr><td height='20'><input type='checkbox' name='trace_issue[]' value='"+opts.periods[i]+"'> "+opts.periods[i]+"</td><td><input type='text' class='"+opts.input_css+"' name='trace_times_"+opts.periods[i]+"' value='0' disabled> 倍</td><td><span class='trace_money_buy'>0</span> 游戏币</td><td><span class='trace_money_prize'>0</span> 游戏币</td><td><span class='trace_money_profit'>0</span> 游戏币</td><td><span class='trace_money_precent'>0</span> %</td></tr>";
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
        try{t=$.fn.lottery_option.prize.toString().split(".")[1].length;}catch(e){t=0}
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
            $.alert("请先选择投注号码");
            return false;
        }
        if( traceType != 'sametimes' && traceType != 'difftimes' && price * count > prize ){//同倍追号和翻倍追号不判断
            $.alert("购买金额过多超过奖金值无意义");
            return false;
        }
        if( $.inArray(traceType,['sametimes','difftimes','profit','margin']) != -1 ){//合法范围
            var issuecount = Number($("#trace_issues_"+traceType).val());
            if( issuecount >= opts.periods.length ){
                $.alert("期数不正确,最大期数为:"+(opts.periods.length-1));
                return false;
            }
            var periodlen  = $.fn.lottery_periodArr.length;
            var error      = false;
            var errmsg     = "";
            if( issuecount <= 0 ){
                $.alert("请填写追号期数");
                return false;
            }
            switch( traceType ){//根据不同追号方式,产生不同的方案
                case 'sametimes' : //同倍追号
                                   var times = Number($("#trace_sametimes").val());
                                   if( times <= 0 ){
                                      error = true;
                                      errmsg= "请填写倍数";
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
                                   errmsg = " <strong>同倍：</strong>\r\n\r\n    "+times+" 倍";
                                   break;
                case 'difftimes' :  //翻倍追号
                                   var trace_step = Number($("#trace_step_difftimes").val());
                                   var times_step = Number($("#trace_difftimes").val());
                                   if( trace_step <= 0 ){
                                      error = true;
                                      errmsg= "请填写相隔期数";
                                      break;
                                   }
                                   if( times_step <= 0 ){
                                      error = true;
                                      errmsg= "请填写倍数";
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
                                   errmsg = " <strong>翻倍：</strong><br />&nbsp;&nbsp;  相隔 "+trace_step+" 期  x "+times_step+" 倍"
                                   break;
                case 'profit'   : //利润追号
                                   var startTime = Number($("#trace_profittimes").val());//起始倍数
                                   var minprofit = Number($("#trace_profit_min").val());//最低利润额
                                   if( minprofit <= 0 ){
                                      error = true;
                                      errmsg= "最低利润额有误,请重新填写";
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
                                   errmsg = " <strong>利润追号：</strong><br />&nbsp;&nbsp;   最低利润："+minprofit+"\r\n    起始倍数："+startTime;
                                   break;
                case 'margin'   : //利润率追号
                                   var startTime = Number($("#trace_margintimes").val());//起始倍数
                                   var minmargin = Number($("#trace_margin_min").val());//最低利润率
                                   if( minmargin >= ((prize*100)/(price*count)-100) ){
                                      error = true;
                                      errmsg= "最低利润率有误,请重新填写";
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
                                   errmsg = "<strong>利润率追号：</strong><br />&nbsp;&nbsp;    最低利润率："+minmargin+"\r\n    起始倍数："+startTime;
                                   break;
                                   
            }
            if( totalbuy > $.fn.lottery_option.money ){
                error  = true;
                errmsg = "追号总金额超出了可用余额";
            }
            if( error == true ){//出错显示错误
                $.alert(errmsg);
                return false;
            }
            $.confirm({
                message: errmsg+"<br /><br />确定要追号 "+issuecount+" 期？",
                funyes : function(){
                            $("input[name^='trace_issue']",$("#trace_detail")).each(function(i,n){
                                if( i<issuecount ){selectIssue(n);}else{unselectIssue(n);}
                            });
                            updateTotalMoney();
                    },
                funno  : function(){
                            cleanTrace();
                    }
            });
        }else{
            $.alert("操作错误,请重试");
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
                    $.alert("请填写正确的倍数");
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
            cleanTrace();
        }
    });
    $("#trace_label_difftimes").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_profit");
        traceHideLabel("#trace_label_margin");
        if( $("#trace_type").val() != "difftimes" ){
            $("#trace_type").val("difftimes");
            cleanTrace();
        }
    });
    $("#trace_label_profit").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_profit");
        traceHideLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_margin");
        if( $("#trace_type").val() != "profit" ){
            $("#trace_type").val("profit");
            cleanTrace();
        }
    });
    $("#trace_label_margin").css("cursor","pointer").click(function(){
        traceShowLabel("#trace_label_margin");
        traceHideLabel("#trace_label_sametimes");
        traceHideLabel("#trace_label_difftimes");
        traceHideLabel("#trace_label_profit");
        if( $("#trace_type").val() != "margin" ){
            $("#trace_type").val("margin");
            cleanTrace();
        }
    });
};
function cleanTrace(){//清空所有追号方案
    $.each($.fn.lottery_periodArr,function(i,n){
        n.ischecked=false; n.times=0; n.money_buy=0; n.money_prize=0; n.money_profit=0; n.money_percent=0;
    });
    $("input[name^='trace_issue']",$("#trace_detail")).each(function(i,n){
        $(n).parent().parent().find("input[name^='trace_times_']").attr("disabled",true).val(0);
        $(n).parent().parent().find("span[class^='trace_money']").each(function(i,n){
                $(this).html(0);
        });
        $(n).attr("checked",false);
    });
    $("#trace_label_sametimes_text").find("span").html(0);
    $("#trace_label_difftimes_text").find("span").html(0);
    $("#trace_label_profit_text").find("span").html(0);
    $("#trace_label_margin_text").find("span").html(0);
    $("#trace_totalamount").val(0);
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
                            alert("登陆超时，或者帐户在其他地方登陆，请重新登陆");
							top.location.href="../?controller=default";
							return false;
                        }
                        if( data == "empty" ){
                            alert("未到销售时间");
                            window.location.href="./?controller=default&action=start";
                            /*$.alert({
                                    message : "未到销售时间",
                                    onclose : function(){window.location.href="./?controller=default&action=start";}
                                    });*/
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
                $.alert("获取数据失败,请刷新页面");
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
                $("#lottery_istrace").val(0)
                cleanTrace();
        }
    }
}

$.lottery_submit = function(obj){//提交表单,下单
    function checkTimeOut(){//检查时间是否结束
        if( $.fn.lottery_TimeCount <= 0 ){//当期结束
            msg = "当前期结束，是否刷新页面?<br /><br />要刷新页面请点击\"确定\"，不刷新页面请点击\"取消\"";
            $.confirm({
                       message: msg,
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
    //提交数据完整性检查
    if( $("#lottery_currentissue").val() != $.fn.lottery_option.period ){//检查当前期完整性
        $.fn.lottery_submiting = false;
        $.alert("数据初始错误,请刷新页面再试");
        return false;
    }
    if( $("#confirm_editor").val() == "" //确认区检查
        || $("#lottery_confirmnums").val() == ""    //确认号码检查
        || Number($("#lottery_totalnum").val()) <= 0 //总投注注数检查
      ){
        $.fn.lottery_submiting = false;
        $.alert("请选择投注号码");
        return false;
    }
    if( Number($("#lottery_totalnum").val()) > 1000 ){
    		$.alert("超过购买限制,最多购买1000注");
    }
    if( Number($("#lottery_istrace").val()) == 0 ){//非追号
        if( Number($("#lottery_totalamount").val()) <= 0 || Number($("#lottery_times").val()) <= 0 ){//非追号投注总金额检查和倍数检查
            $.fn.lottery_submiting = false;
            $.alert("请输入投注倍数");
            return false;
        }
        if( Number($("#lottery_totalamount").val()) > $.fn.lottery_option.money ){//超过可用资金
            $.alert("您的余额不足,当前余额:"+$.fn.lottery_option.money);
            return false;
        }
        if( ($.fn.lottery_option.prize * Number($("#lottery_times").val())) > $.fn.lottery_option.limitbons ){//奖金超出最大限制额
            $.alert("超过奖金限额");
            return false;
        }
        confirmmsg += "您确认加入第 <font color='red'>"+$("#lottery_currentissue").val()+"</font> 期";
        confirmmsg += "<br><br>加入号码:<br><font color='blue' size='2'>"+$("#confirm_editor").val()+"</font>";
        confirmmsg += "<br><br>总金额:<font color='red'>"+$("#lottery_totalamount").val()+"</font> 游戏币";
        if( Number($("#lottery_totalamount").val()) >= $.fn.lottery_option.bigcancel ){
            confirmmsg += "<br><font color='green'>如果您手动撤消此单,将收取撤单手续费: <font color='red'>"+Math.floor((Number($("#lottery_totalamount").val())*$.fn.lottery_option.canclepre)*100)/100+"</font> 游戏币</font>";
        }
    }else{//追号
        tracecount = 0;
        errorIssue = "";
        outIssue   = "";
        $("input[name^='trace_issue']",$("#trace_detail")).each(function(i,n){//检查选择的期号和对应的倍数是否正确
            if( $(n).attr("checked") == true ){
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
            $.alert("请选择要追号的奖期");
            return false;
        }
        if( errorIssue != "" ){
            $.fn.lottery_submiting = false;
            $.alert("追号奖期中,第 <font color='red'>"+errorIssue+"</font> 期倍数错误");
            return false;
        }
        if( outIssue != "" ){
            $.fn.lottery_submiting = false;
            $.alert("追号奖期中,第 <font color='red'>"+outIssue+"</font> 期超过奖金限额");
            return false;
        }
        if( Number($("#trace_totalamount").val()) <= 0 ){//追号总金额检查
            $.fn.lottery_submiting = false;
            $.alert("追号总金额出错，请刷新页面再试");
            return false;
        }
        if( Number($("#trace_totalamount").val()) > $.fn.lottery_option.money ){//超过可用资金
            $.alert("您的余额不足,当前余额:"+$.fn.lottery_option.money);
            return false;
        }
        confirmmsg += "您确认要追号 <font color='red'>"+tracecount+"</font> 期";
        confirmmsg += "<br><br>加入号码:<br><font color='blue' size='2'>"+$("#confirm_editor").val()+"</font>";
        confirmmsg += "<br><br>总金额:<font color='red'>"+$("#trace_totalamount").val()+"</font> 游戏币";
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
        message:"<h3>正在提交数据,请稍等....</h3>",
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
                        //alert(data);
                        //$.alert("调试");
                        //return false;
                        $.fn.lottery_submiting = false;
                        var partn = /<script.*>.*<\/script>/;
                        if( partn.test(data) ){
                            alert("登陆超时，或者帐户在其他地方登陆，请重新登陆");
				            				top.location.href="../?controller=default";
				            				return false;
                        }else if( data == "success"){//购买成功
                            $.alert({
                                    message:"购买成功",
                                    onclose: function(){
                                                if( checkTimeOut() == true ){//没有结束
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
                        $.alert({message:"请求超时,请重试",onclose:checkTimeOut});
                     }
        });
    }
    //变价提示
    function adjustConfirm(obj,codedata){
        html  = '<div id="adjust_content" class="lottery_adjust_confirm"><div class="adjusttitlebox" id="adjust_title"><span class="adjusttitle">信息提示</span></div>';
        html += '<div class="adjustmsg">您投注的号码中, 有部分号码的奖金有所变动</div>';
        html += '<div class="adjustcodebox"><div><table width="100%" border="1" cellpadding="3" cellspacing="0" bordercolordark="#FFFFFF" bordercolorlight="#666666"><tr bgcolor="#3399CC"><td align="center">此单包含的奖金变动号码</td><td align="center">目前中奖金额</td></tr>';
        $.each(codedata,function(i,n){
            html += '<tr><td align="center">'+i+'</td><td align="center">'+n+'</td></tr>';
            });
        html += '</table></div></div><div style="width:100%;margin-top:10px;"><ul><li><input type="radio" name="adjust_choice" id="adjust_choice_1" value="1" checked> 我接受以上号码的奖金变动, 并且本次投注不要再次提示我</li><li><input type="radio" name="adjust_choice" id="adjust_choice_2" value="2"> 我接受以上号码的奖金变动, (本次投注若有其他号码奖金被变化,请再次让我确认)</li><li><input type="radio" name="adjust_choice" id="adjust_choice_0" value="0"> 我拒绝接受</li></ul></div><div class="confirmbuttonbox"><input type="button" name="adjust_submit" id="adjust_submit" value=" 确 认 "></div></div>';
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
                    $.lotteryRest();
                    $.unblockUI({fadeOut:0});
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