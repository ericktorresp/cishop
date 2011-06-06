//活动插件
	(function($){
		//公用函数
		function binddefault(obj,value){//绑定默认值事件
			$(obj).mouseover(function(){
				if( $(obj).val() == value ){
					$(obj).select();
				}
			}).click(function(){
				if( $(obj).val() == value ){
					$(obj).val("");
				}
			}).blur(function(){
				if( $(obj).val() == "" ){
					$(obj).val(value);
				}
			});
		}
		
		$.fn.getSingleOptions = function(opts){//获取单选
		    opts = $.extend( {}, $.fn.getSingleOptions.defaults, opts || {} ); //根据参数初始化默认配置
			j = 1;
			var htmlstr = "<input type='hidden' name='question_answers_"+opts.qno+"_options_count' id='question_answers_"+opts.qno+"_options_count' value='"+j+"'><ul id='question_answers_"+opts.qno+"'>";
			if( opts.options == "" ){
			   htmlstr += "<li><input type='radio' name='question_answers_"+opts.qno+"_check' id='question_answers_"+opts.qno+"_check_"+j+"' value='"+j+"'> <input type='text' name='question_answers_"+opts.qno+"_option_"+j+"' id='question_answers_"+opts.qno+"_option_"+j+"' class='optioninput' value='选项'></li>";
			}else{
			    k = 0;
			    $.each(opts.options,function(i,n){
			       k = k+1;
			       if( i !='other' ){
    			       j = parseInt(i);
    			       htmlstr += "<li><input type='radio' name='question_answers_"+opts.qno+"_check' id='question_answers_"+opts.qno+"_check_"+j+"' value='"+j+"'> <input type='text' name='question_answers_"+opts.qno+"_option_"+j+"' id='question_answers_"+opts.qno+"_option_"+j+"' class='optioninput' value='"+n+"'>";
    			       if( k != 1 ){
    			            htmlstr += " <img src='./images/act_del.gif' title='删除'>";
    			       }
    			       htmlstr += "</li>";
			       }
			    });
			}
			htmlstr += "<li><input type='radio' id='options_radio_add_"+opts.qno+"' disabled> <input type='text' id='options_add_"+opts.qno+"' class='optionaddinput' readonly>";
			if( opts.acttype == 1 ){
			     htmlstr += "<span";
			    if( opts.options.other == 1 ){
				   htmlstr += " style='display:none;' ";
			    }
			    htmlstr += ">或<a href='javascript:' id='addother_"+opts.qno+"' style='color:blue;'>添加“其他”</a></span>";
			}
			htmlstr += "</li>";
			if( opts.options.other == 1 ){
			    htmlstr += "<li><input type='radio' name='question_answers_other_"+opts.qno+"' value='1' disabled><input type='hidden' name='question_answers_other_"+opts.qno+"' value='1'> 其他<input type='text' id='question_answers_other_"+opts.qno+"'  style='width:150px;height:15px;' class='showonly_input' disabled value='他们的回答'> <img src='./images/act_del.gif' title='删除'></li>";
		    }
			htmlstr += "</ul>";
			$(htmlstr).appendTo(this);
			$("#question_answers_"+opts.qno+"_options_count").val(j);
			//答案
			if( opts.options != "" && opts.answer != "" ){
			    $("#question_answers_"+opts.qno+"_check_"+parseInt(opts.answer)).attr("checked",true);
			}
			$("input[id^='question_answers_"+opts.qno+"_option_']").each(function(){
			    binddefault(this,"选项");
			    $(this).next().click(function(){
					$(this).parent().remove();
					$("#savequestion").html("保存").attr("disabled",false);
				});
			});
			if( opts.options.other == 1 ){
			    $("#question_answers_other_"+opts.qno).next().click(function(){
					$(this).parent().remove();
					$("#addother_"+opts.qno).parent().show();
					$("#savequestion").html("保存").attr("disabled",false);
				});
			}
			$("#options_add_"+opts.qno).click(function(){
			    j = parseInt($("#question_answers_"+opts.qno+"_options_count").val());
				j = j+1;
				$("<li><input type='radio' name='question_answers_"+opts.qno+"_check' id='question_answers_"+opts.qno+"_check_"+j+"' value='"+j+"'> <input type='text' name='question_answers_"+opts.qno+"_option_"+j+"' id='question_answers_"+opts.qno+"_option_"+j+"' class='optioninput' value='选项'> <img src='./images/act_del.gif' title='删除'></li>").insertBefore($(this).parent());
				binddefault("#question_answers_"+opts.qno+"_option_"+j,"选项");
				$("#question_answers_"+opts.qno+"_options_count").val(j);
				$("#savequestion").html("保存").attr("disabled",false);
				$("#question_answers_"+opts.qno+"_option_"+j).next().click(function(){
					$(this).parent().remove();
					$("#savequestion").html("保存").attr("disabled",false);
				});
			});
			if( opts.acttype == 1 ){
				$("#addother_"+opts.qno).click(function(){
					$(this).parent().hide();
					$("<li><input type='radio' name='question_answers_other_"+opts.qno+"' value='1' disabled><input type='hidden' name='question_answers_other_"+opts.qno+"' value='1'> 其他<input type='text' id='question_answers_other_"+opts.qno+"'  style='width:150px;height:15px;' class='showonly_input' disabled value='他们的回答'> <img src='./images/act_del.gif' title='删除'></li>").insertAfter($(this).parent().parent());
					$("#savequestion").html("保存").attr("disabled",false);
					$("#question_answers_other_"+opts.qno).next().click(function(){
						$(this).parent().remove();
						$("#addother_"+opts.qno).parent().show();
						$("#savequestion").html("保存").attr("disabled",false);
					});
				});
			}
		};
		$.fn.getSingleOptions.defaults = {
		    qno     : 1,
		    acttype : 0,
		    options : [],
		    answer  : []
		};
		
		$.fn.getmultipleOptions = function(opts){//获取多选
		    opts = $.extend( {}, $.fn.getmultipleOptions.defaults, opts || {} ); //根据参数初始化默认配置
		    j = 1;
			var htmlstr = "<input type='hidden' name='question_answers_"+opts.qno+"_options_count' id='question_answers_"+opts.qno+"_options_count' value='"+j+"'><ul id='question_answers_"+opts.qno+"'>";
			if( opts.options == "" ){
			   htmlstr += "<li><input type='checkbox' name='question_answers_"+opts.qno+"_check[]' id='question_answers_"+opts.qno+"_check_"+j+"' value='"+j+"'> <input type='text' name='question_answers_"+opts.qno+"_option_"+j+"' id='question_answers_"+opts.qno+"_option_"+j+"' class='optioninput' value='选项'></li>";
			}else{
			    k = 0;
			    $.each(opts.options,function(i,n){
			       k = k+1;
			       if( i !='other' ){
    			       j = parseInt(i);
    			       htmlstr += "<li><input type='checkbox' name='question_answers_"+opts.qno+"_check[]' id='question_answers_"+opts.qno+"_check_"+j+"' value='"+j+"'> <input type='text' name='question_answers_"+opts.qno+"_option_"+j+"' id='question_answers_"+opts.qno+"_option_"+j+"' class='optioninput' value='"+n+"'>";
    			       if( k != 1 ){
    			            htmlstr += " <img src='./images/act_del.gif' title='删除'>";
    			       }
    			       htmlstr += "</li>";
			       }
			    });
			    $("#question_answers_"+opts.qno+"_options_count").val(j);
			}
			htmlstr += "<li><input type='checkbox' id='options_checkbox_add_"+opts.qno+"' disabled> <input type='text' id='options_add_"+opts.qno+"' class='optionaddinput' readonly>";
			if( opts.acttype == 1 ){
			     htmlstr += "<span";
			    if( opts.options.other == 1 ){
				   htmlstr += " style='display:none;' ";
			    }
			    htmlstr += ">或<a href='javascript:' id='addother_"+opts.qno+"' style='color:blue;'>添加“其他”</a></span>";
			}
			htmlstr += "</li>";
			if( opts.options.other == 1 ){
			    htmlstr += "<li><input type='checkbox' name='question_answers_other_"+opts.qno+"' value='1' disabled><input type='hidden' name='question_answers_other_"+opts.qno+"' value='1'> 其他<input type='text' id='question_answers_other_"+opts.qno+"' style='width:150px;height:15px;' class='showonly_input' disabled value='他们的回答'> <img src='./images/act_del.gif' title='删除'></li>";
		    }
		    htmlstr += "<li>最少选择<input type='text' style='width:20px; text-align:center;' value='"+opts.qminright+"' name='question_answers_minselect_"+opts.qno+"' id='question_answers_minselect_"+opts.qno+"'>个选项</li>";
			htmlstr += "</ul>";
			$(htmlstr).appendTo(this);
			$("#question_answers_"+opts.qno+"_options_count").val(j);
			//答案
			if( opts.options != "" && opts.answer != "" ){
			    $.each(opts.answer,function(i,n){
			        $("#question_answers_"+opts.qno+"_check_"+parseInt(n)).attr("checked",true);
			    });
			}
			$("input[id^='question_answers_"+opts.qno+"_option_']").each(function(){
			    binddefault(this,"选项");
			    $(this).next().click(function(){
					$(this).parent().remove();
					$("#savequestion").html("保存").attr("disabled",false);
				});
			});
			if( opts.options.other == 1 ){
			    $("#question_answers_other_"+opts.qno).next().click(function(){
					$(this).parent().remove();
					$("#addother_"+opts.qno).parent().show();
					$("#savequestion").html("保存").attr("disabled",false);
				});
			}
		    ////////////////////////////////////////
			$("#question_answers_minselect_"+opts.qno).keyup(function(){
				$(this).val( $(this).val().replace(/^[^0-9]*$/,"").substr(0,2) );
			});
			$("#options_add_"+opts.qno).click(function(){
			    j = parseInt($("#question_answers_"+opts.qno+"_options_count").val());
				j = j+1;
				$("<li><input type='checkbox' name='question_answers_"+opts.qno+"_check[]' value='"+j+"'> <input type='text' id='question_answers_"+opts.qno+"_option_"+j+"' name='question_answers_"+opts.qno+"_option_"+j+"' class='optioninput' value='选项'> <img src='./images/act_del.gif' title='删除'></li>").insertBefore($(this).parent());
				binddefault("#question_answers_"+opts.qno+"_option_"+j,"选项");
				$("#question_answers_"+opts.qno+"_options_count").val(j);
				$("#savequestion").html("保存").attr("disabled",false);
				$("#question_answers_"+opts.qno+"_option_"+j).next().click(function(){
					$(this).parent().remove();
					$("#savequestion").html("保存").attr("disabled",false);
				});
			});
			if( opts.acttype == 1 ){
				$("#addother_"+opts.qno).click(function(){
					$(this).parent().hide();
					$("<li><input type='checkbox' name='question_answers_other_"+opts.qno+"' value='1' disabled><input type='hidden' name='question_answers_other_"+opts.qno+"' value='1'> 其他<input type='text' id='question_answers_other_"+opts.qno+"' style='width:150px;height:15px;' class='showonly_input' disabled value='他们的回答'> <img src='./images/act_del.gif' title='删除'></li>").insertAfter($(this).parent().parent());
					$("#savequestion").html("保存").attr("disabled",false);
					$("#question_answers_other_"+opts.qno).next().click(function(){
						$(this).parent().remove();
						$("#addother_"+opts.qno).parent().show();
						$("#savequestion").html("保存").attr("disabled",false);
					});
				});
			}
		};
		$.fn.getmultipleOptions.defaults = {
		    qno     : 1,
		    acttype : 0,
		    options : [],
		    answer  : [],
		    qminright : 0
		};
		
		$.fn.getUserInput = function(n){//用户输入框
			var $htmlstr = $("<input type='text' id='question_answer_"+n+"' style='width:200px;height:22px;' class='showonly_input' disabled value='他们的回答'>");
			$htmlstr.appendTo(this);
		};
		
		$.fn.getUserText = function(n){//用户文本框
			var $htmlstr = $("<textarea id='question_answer_"+n+"' class='showonly_input' style='width:400px;height:50px;' disabled>他们较详细的回答</textarea>");
			$htmlstr.appendTo(this);
		};
		
		$.fn.insertQuestion = function(opts){//增加问题
		    opts = $.extend( {}, $.fn.insertQuestion.defaults, opts || {} ); //根据参数初始化默认配置
			var htmlstr = "<div class='questiondiv' id='questionsbox_"+opts.no+"'>";
			htmlstr += "<input type='hidden' name='question_id_"+opts.no+"' id='question_id_"+opts.no+"' value='"+opts.qid+"'>";
			htmlstr += "<table cellspacing='1' cellpadding='2' id='detail-table'>";
			htmlstr += "<tr><td colspan='3' style='color:red; display:none;' id='question_each_error_"+opts.no+"'></td></tr>";
			htmlstr += "<tr><td width='70'><strong>问题标题</strong></td>";
			htmlstr += " <td><input type='text' name='question_title_"+opts.no+"' id='question_title_"+opts.no+"' size='50' value='"+opts.qtitle+"'></td>";
			htmlstr += " <td width='100' align='right'><img src='./images/no.gif' title='删除' id='question_button_delete_"+opts.no+"' style='cursor:pointer;' /></td></tr>";
			htmlstr += "<tr><td><strong>帮助文本</strong></td>";
			htmlstr += " <td colspan='2'><input type='text' name='question_help_"+opts.no+"' id='question_help_"+opts.no+"' size='50' value='"+opts.qhelp+"'></td></tr>";
			htmlstr += "<tr><td valign='top'><strong>问题类型</strong></td>";
			htmlstr += " <td colspan='2'><select name='question_type_"+opts.no+"' id='question_type_"+opts.no+"'>";
			typesel = new Array('','','','','');
			switch( opts.qtype ){
			    case 0 : typesel[0]='selected';break;
			    case 1 : typesel[1]='selected';break;
			    case 2 : typesel[2]='selected';break;
			    case 3 : typesel[3]='selected';break;
			    case 4 : typesel[4]='selected';break;
			    default: typesel[0]='selected';break;
			}
			htmlstr += " <option value='0' disabled "+typesel[0]+">从列表中选择</option>";
			
			htmlstr += " <option value='1' "+typesel[1]+">单选</option><option value='2' "+typesel[2]+">多选</option>";
			if( opts.acttype == 1 ){
				htmlstr += " <option value='3' "+typesel[3]+">文本</option><option value='4' "+typesel[4]+">段落文本</option></select>";
			}else{
				htmlstr += "</select> <span style='color:green;'> [必须勾选正确答案]</span>";
			}
			htmlstr += "<div class='question_options_div' style='margin:10px auto;'></div>";      
			htmlstr += "</td></tr><tr><td><strong>";
			if( opts.acttype == 2 ){
				htmlstr += "答对分数";
			}
			htmlstr += "</strong></td><td colspan='2'>";
			if( opts.acttype == 2 ){
				htmlstr += "<input type='text' size='10' name='question_score_"+opts.no+"' id='question_score_"+opts.no+"' value='"+opts.qscore+"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			if( opts.qmust == 1 ){
			    must_check = "checked";   
			}else{
			    must_check = "";
			}
			htmlstr += "<input type='checkbox' name='question_must_"+opts.no+"' id='question_must_"+opts.no+"' value='1' "+must_check+"> 将其设置为必要问题</td>";
			htmlstr += "</tr></table></div>";
			$(htmlstr).appendTo(this);
			switch( opts.qtype ){
			    case 1:    
			            $("#question_type_"+opts.no).nextAll(".question_options_div").getSingleOptions({
    					    qno     : opts.no,
                		    acttype : opts.acttype,
                		    options : opts.qoptions,
                		    answer  : opts.qanswer            		    
			            }); break;
			    case 2:
			            $("#question_type_"+opts.no).nextAll(".question_options_div").getmultipleOptions({
    					    qno      : opts.no,
                		    acttype  : opts.acttype,
                		    options  : opts.qoptions,
                		    answer   : opts.qanswer,
                		    qminright: opts.qminright            		    
			            }); break;
			   case 3 : $("#question_type_"+opts.no).nextAll(".question_options_div").getUserInput(opts.no);break;
			   case 4 : $("#question_type_"+opts.no).nextAll(".question_options_div").getUserText(opts.no);break;
			   default: break;
			}
			
			//绑定事件
			$("#question_type_"+opts.no).change(function(){//类型选择触发
				if( $(this).val() == 1 ){
					$(this).nextAll(".question_options_div").empty().getSingleOptions({
					    qno     : opts.no,
            		    acttype : opts.acttype
					});
				}else if( $(this).val() == 2 ){
					$(this).nextAll(".question_options_div").empty().getmultipleOptions({
					    qno      : opts.no,
            		    acttype  : opts.acttype
					});
				}else if( $(this).val() == 3 ){
					$(this).nextAll(".question_options_div").empty().getUserInput(opts.no);
				}else if( $(this).val() == 4 ){
					$(this).nextAll(".question_options_div").empty().getUserText(opts.no);
				}
			});
			$("#question_button_delete_"+opts.no).click(function(){//删除触发
				if( confirm("确实要放弃此问题吗？") ){
					if( $("#question_id_"+opts.no).val() != 0 ){//删除
						$.ajax({
							type : 'POST',
							url	 : './?controller=marketmgr&action=activityadd',
							data : 'step=delquestion&qid='+$("#question_id_"+opts.no).val(),
							success : function(data){
								if( data == 'fail' ){
									showError('非法提交');
								}
								else if( parseInt(data) == 1 ){//删除成功
									$("#questionsbox_"+opts.no).remove();
								}else{
									showError('删除失败');
								}
							}
						});
					}else{
						$("#questionsbox_"+opts.no).remove();
					}
				}
			});
			$("#questionsbox_"+opts.no).mouseover(function(){//鼠标移动
				$(this).attr("class","questiondivover");
			}).mouseout(function(){
				$(this).attr("class","questiondiv");
			});
		};
		$.fn.insertQuestion.defaults = {
		        no       : 1,   //当前问题数目
		        acttype  : 1,   //活动类型,1:问卷调查,2:有奖竟猜
		        qid      : 0,   //当前问题ID
		        qtitle   : '',  //问题标题
		        qhelp    : '',  //问题帮助
		        qtype    : 0,   //问题类型,0:无,1:单选,2:多选,3:文本,4:段落
		        qoptions : {},  //问题选项,单选和多选
		        qanswer  : {},  //问题答案
		        qscore   : 0,   //问题分数
		        qminright: 0,   //多选时,最少选择个数
		        qmust    : 0    //是否为必答,0:否,1:是
		    };
	})(jQuery);