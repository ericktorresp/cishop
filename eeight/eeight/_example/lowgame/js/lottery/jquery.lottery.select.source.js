;(function($){
//start closure
/********************************************************全局函数********************************************************/
$.lottery_expandNum = function(){//根据已选择号码展开显示在编辑区或者确认区[根据彩种和玩法不同做不同处理]
    lottery_type   = $.fn.lottery_option.lottery;//彩种
    lottery_method = $.fn.lottery_option.method;//方法
    //3D直选,P3直选展开
    function _expandZX(){
        if( $.fn.lottery_no.length < 3 ){
            $.alert(lottery_lang.code_error);
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
                            $.fn.lottery_no_edit.push(temp_num);
                        }
                    }
                }
            }
        }
    }
    function _expandZXHZ(){//直选和值展开
        if( $.fn.lottery_no.length != 1 ){
            $.alert(lottery_lang.code_error);
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
            $.alert(lottery_lang.code_error);
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
            $.alert(lottery_lang.code_error);
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
            $.alert(lottery_lang.code_error);
            return false;
        }
        $.fn.lottery_no_edit = [];
        $.each($.fn.lottery_no[0], function(i,n){
            if( n == 1 ){$.fn.lottery_no_edit.push(Number(i));}
        });
    }
    function _expandQEZX(){//前二直选展开
        if( $.fn.lottery_no.length != 2 ){
            $.alert(lottery_lang.code_error);
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
            $.alert(lottery_lang.code_error);
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
            $.alert(lottery_lang.code_error);
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
        $.alert(lottery_lang.param_error);
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
/********************************************************全局函数完******************************************************/

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
                    selectHtml += "<td style='padding-right:20px;width:100px;text-align:center;' rowspan='"+n.cols+"'><span class='"+opts.titleCss+"'>"+n.title+"</span></td>";
                }
                selectHtml += "<td>";
                numbers = n.no.split("|");
                $.fn.lottery_no[n.place] = [];
                for( i=0; i<numbers.length; i++ ){
                    selectHtml += "<div class='"+opts.noCss+"' name='lottery_no_"+n.place+"'>"+numbers[i]+"</div> ";
                    $.fn.lottery_no[n.place][numbers[i]] = 0;
                }
                if( opts.isButton == true ){
                    selectHtml += "<span class='space_class'></span><button type='button' class='"+opts.buttonCss+"' name='all'>"+lottery_lang.button_all+"</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='big'>"+lottery_lang.button_big+"</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='small'>"+lottery_lang.button_small+"</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='odd'>"+lottery_lang.button_odd+"</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='even'>"+lottery_lang.button_even+"</button>&nbsp;<button type='button' class='"+opts.buttonCss+"' name='clean'>"+lottery_lang.button_clean+"</button>";
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
                    selectHtml += "<td style='padding-right:20px;width:100px;text-align:center;' rowspan='"+n.cols+"'><span class='"+opts.titleCss+"'>"+n.title+"</span></td>";
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

//end closure
})(jQuery);