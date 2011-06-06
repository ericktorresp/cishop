﻿;(function($){
//start closure
/********************************************************全局函数********************************************************/

/********************************************************全局函数完******************************************************/
/*****************************筛选区********************************/
$.fn.loterryFilterArea = function(opts){
    opts      = $.extend( {}, $.lotteryUI.defaults.filterDefault, opts || {} ); //根据参数初始化默认配置
    opts.show = opts.show.toLowerCase();
    var filterHtml = "<ul class='"+opts.css+"'><li>";
    if( opts.show == 'all' || opts.show == 'span' ){//跨度
        filterHtml += "<input type='checkbox' name='filter_span' id='filter_span' />"+lottery_lang.tip_f_span+"<select disabled='disabled' name='filter_span_select' id='filter_span_select'>";
        $.each(opts.span,function(i,n){
            filterHtml += "<option value='"+Number(n)+"'>"+n+"</option>";
        });
        filterHtml += "</select>";
    }
    if( opts.show == 'all' || opts.show == 'fixed' ){//定胆
        filterHtml += "&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='filter_fixed' id='filter_fixed' />"+lottery_lang.tip_f_fixed+"<select disabled='disabled' name='filter_fixed_select' id='filter_fixed_select'>";
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
//end closure
})(jQuery);