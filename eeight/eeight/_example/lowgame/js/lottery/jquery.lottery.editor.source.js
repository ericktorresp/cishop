;(function($){
//start closure
/********************************************************全局函数********************************************************/
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
        $.alert(lottery_lang.param_error);
        return false;
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
        $.alert(lottery_lang.param_error);
        return false;
    }
}
$.lottery_delDuplicateNum = function(){ //删除编辑区重复号码
	  duplicateNum = [];	//重复的号码
    newnums      = [];  //新的号码
	  var len = $.fn.lottery_no_edit.length;
    if( len == 0 ){
        return duplicateNum;
    }
    num     = "";
    for( i=0; i<len; i++ ){
        num = $.fn.lottery_no_edit[i];
        if( $.inArray(num,duplicateNum) != -1 ){
        	  continue;
        }
        for( j=i+1; j<len; j++ ){
            if( $.fn.lottery_no_edit[j] == num ){
                duplicateNum.push(num);
                break;
            }
        }
        newnums.push(num);
    }
    newnums.sort();
    $.fn.lottery_no_edit = newnums;
    return duplicateNum;
}
/********************************************************全局函数完******************************************************/
/*****************************编辑区********************************/
$.fn.loterryEditArea = function(opts){
    opts = $.extend( {}, $.lotteryUI.defaults.editDefault, opts || {} ); //根据参数初始化默认配置
    var editAreaHtml = "<table class=\""+opts.tableCss+"\"><tr><td width='450'><textarea name=\"editor\" id=\"lottery_editor\" class=\""+opts.textareaCss+"\"></textarea><br /><span class='lottery_editor_word'>"+lottery_lang.tip_e_split+"</span></td><td>&nbsp;</td><td id=\"lottery_editor_buttons\"><ul>";
    if( opts.button_random_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button "+opts.button_random_CSS+"\" id=\"random_button\" alt=\""+opts.button_random_text+"\">"+opts.button_random_text+"</button>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_random_Image+"\" id=\"random_button\" class=\""+opts.button_random_CSS+"\" alt=\""+opts.button_random_text+"\" />";
    }
    editAreaHtml += "  <select name=\"random_input\" id=\"random_input\" class=\""+opts.input_random_CSS+"\"><option value='1'>1</option><option value='2'>2</option><option value='5'>5</option><option value='10'>10</option><option value='15'>15</option><option value='20'>20</option></select>"+lottery_lang.tip_e_count+"</li>";
    if( opts.button_import_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_import_CSS+"\" id=\"import_button\" alt=\""+opts.button_import_text+"\">"+opts.button_import_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_import_Image+"\" id=\"import_button\" class=\""+opts.button_import_CSS+"\" alt=\""+opts.button_import_text+"\" /></li>";
    }
    if( opts.button_clear_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_clear_CSS+"\" id=\"clear_button\" alt=\""+opts.button_clear_text+"\">"+opts.button_clear_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_clear_Image+"\" id=\"clear_button\" class=\""+opts.button_clear_CSS+"\" alt=\""+opts.button_clear_text+"\" /></li>";
    }
    if( opts.button_delete_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_delete_CSS+"\" id=\"delete_button\" alt=\""+opts.button_delete_text+"\">"+opts.button_delete_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_delete_Image+"\" id=\"delete_button\" class=\""+opts.button_delete_CSS+"\" alt=\""+opts.button_delete_text+"\" /></li>";
    }
    if( opts.button_insert_Image == '' ){
        editAreaHtml += "<li><button type=\"button\" class=\"lottery_button  "+opts.button_insert_CSS+"\" id=\"insert_button\" alt=\""+opts.button_insert_text+"\">"+opts.button_insert_text+"</button></li>";
    }else{
        editAreaHtml += "<li><img src=\""+opts.button_insert_Image+"\" id=\"insert_button\" class=\""+opts.button_insert_CSS+"\" alt=\""+opts.button_insert_text+"\" /></li>";
    }
    editAreaHtml += "</ul></td></tr></table>";
    $(editAreaHtml).appendTo(this);
    
    //绑定事件
    $("#lottery_editor").change(function(){//
        temp_str = $.trim($(this).val());
        temp_str = temp_str.replace(/[\s\r,;，；　]/g,"|").replace(/(\|)+/g,"|");
        temp_str = temp_str.replace(/０/g,"0").replace(/１/g,"1").replace(/２/g,"2").replace(/３/g,"3").replace(/４/g,"4").replace(/５/g,"5").replace(/６/g,"6").replace(/７/g,"7").replace(/８/g,"8").replace(/９/g,"9");
        if( temp_str == "" ){
        	  $.fn.lottery_no_edit = [];
        }else{
        	  $.fn.lottery_no_edit = temp_str.split("|");
        }
    }).blur(function(){
    		$(this).val( $(this).val().replace(/[^\s\r,;，；　０１２３４５６７８９0-9]/g,"") );
    		$("#lottery_editor").change();
    });
    $("#random_button").click(function(){   //机选号码
        num = Number($("#random_input").val());
        numbers = $.lottery_randomNum(num);
        $("#lottery_editor").val($("#lottery_editor").val()== "" ? numbers.join(" ") : $("#lottery_editor").val()+" "+numbers.join(" ")).change();
    });
    $("#import_button").click(function(){   //从文件中导入号码
        $.ajaxUploadUI({
                        message :lottery_lang.msg_edit_t1,
												url	    : './js/dialogUI/fileupload.php',
												dataType: 'html',
												filetype: ['txt','csv'],
												title	: lottery_lang.msg_edit_t2,
												success : function(data){
															$("#lottery_editor").val(data).change();
														},
												loadtext: lottery_lang.msg_edit_t3
            });
    });
    $("#clear_button").click(function(){//清空
        $.fn.lottery_no_edit = [];
        $("#lottery_editor").val($.fn.lottery_no_edit);
    });
    $("#delete_button").click(function(){//删除重复号码
    			result = $.lottery_checkNum();
    			if( result.err == 'error' ){
    				  $.alert(lottery_lang.msg_edit_t4+"<br><br>  "+result.nums.join(" "));
    				  return false;
    			}
					result = $.lottery_delDuplicateNum();
					if( result.length == 0 ){
						  $.alert(lottery_lang.msg_edit_t8);
					}else{
						  $.alert(lottery_lang.msg_edit_t9.replace("[codes]",result.join(" ")));
					}
        	$("#lottery_editor").val($.fn.lottery_no_edit.join(" "));
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
                $.alert(lottery_lang.msg_edit_t4+"<br><br>  "+result.nums.join(" "));
            }else if( result.err == 'dup' ){
                msg = lottery_lang.msg_edit_t5+"<br>";
                $.each(result.nums,function(i,n){
                    msg += lottery_lang.msg_edit_t6.replace( "[code]",n.num ).replace( "[count]",n.count );
                });
                $.confirm({
                           message: msg+"<br><br>"+lottery_lang.msg_edit_t7,
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
//end closure
})(jQuery);