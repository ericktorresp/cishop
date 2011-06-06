/*
*	language package
* utf-8
*/
var lottery_lang = {
	  //数据错误信息
	  data_error	  :	"初始化出错，请刷新页面重试",
	  code_error    : "初始号码错误",
	  param_error   : "参数错误",
	  action_error  : "操作错误,请重试",
	  
	  //文字按钮内容
	  button_random : "机选",
	  button_import : "导入文件",
	  button_clear	: "清空",
	  button_insert : "添加",
	  button_trace	: "立即生成",
	  button_delete : "删除重复号",
	  button_all		: "全",
	  button_big		: "大",
	  button_small  : "小",
	  button_odd    : "奇",
	  button_even   : "偶",
	  button_clean  : "清",
	  
	  //文字说明
	  tip_f_span			: "跨度",
	  tip_f_fixed     : "定胆",
	  tip_e_split     : "每个号码之间请用一个 空格[ ]、逗号[,] 或者 分号[;] 隔开",
	  tip_e_count     : "注",
	  tip_c_count     : "总注数",
	  tip_c_money     : "总金额",
	  tip_c_yxb				: "游戏币",
	  tip_c_time1			: "请填写倍数",
	  tip_c_time2     : "倍",
	  tip_t_t1        : "我要追号",
	  tip_t_t2        : "中奖后停止追号",
	  tip_t_t3        : "同倍追号",
	  tip_t_t4        : "翻倍追号",
	  tip_t_t5        : "利润金额追号",
	  tip_t_t6        : "利润率追号",
	  tip_t_t7        : "倍数",
	  tip_t_t8        : "追号期数",
	  tip_t_t9        : "追号总金额",
	  tip_t_t10       : "隔",
	  tip_t_t11       : "期",
	  tip_t_t12       : "追号总期数",
	  tip_t_t13       : "起始倍数",
	  tip_t_t14       : "最低利润",
	  tip_t_t15       : "最低利润率",
	  tip_t_t16       : "可选期号",
	  tip_t_t17       : "期号",
	  tip_t_t18       : "投注金额",
	  tip_t_t19       : "中奖金额",
	  tip_t_t20       : "利润(游戏币)",
	  tip_t_t21       : "利润率(%)",
	  
	  //提示信息[提交表单]
	  msg_timeout		: "当前期结束，是否刷新页面?<br /><br />要刷新页面请点击\"确定\"，不刷新页面请点击\"取消\"",
	  msg_login			: "登陆超时，或者帐户在其他地方登陆，请重新登陆",
	  msg_saletime	: "未到销售时间",
	  msg_ajax			: "<h3>正在提交数据,请稍等....</h3>",
	  msg_ajax_t    : "请求超时,请重试",
	  msg_ajaxerror : "获取数据失败,请刷新页面",
	  msg_sub_t1    : "请选择投注号码",
	  msg_sub_t2    : "超过购买限制,最多购买1000注",
	  msg_sub_t3    : "请输入投注倍数",
	  msg_sub_t4    : "您的余额不足,当前余额:",
	  msg_sub_t5    : "超过奖金限额",
	  msg_sub_t6		: "您确认加入第 <font color='red'>[issue]</font> 期, 加入号码:<br><div style='height:120px;overflow:hidden;overflow-y:scroll;margin-bottom:5px;'><font color='blue' size='2'>[codes]</font></div>&nbsp;&nbsp;总金额:<font color='red'>[money]</font> 游戏币",
	  msg_sub_t7    : "<br /><span id='notice_color'><br /><font>敬请注意：如撤单将收取手续费: <font>[money]</font> 元</font></span>",
	  msg_sub_t13   : "<br><span>[charge]</span>",
	  msg_sub_t8    : "请选择要追号的奖期",
	  msg_sub_t9    : "追号奖期中,第 <font color='red'>[errorIssue]</font> 期倍数错误",
	  msg_sub_t10   : "追号奖期中,第 <font color='red'>[outIssue]</font> 期超过奖金限额",
	  msg_sub_t11   : "追号总金额出错，请刷新页面再试",
	  msg_sub_t12   : "您确认要追号 <font color='red'>[tracecount]</font> 期,加入号码:<br><div style='height:120px;overflow:hidden;overflow-y:scroll;margin-bottom:5px;'><font color='blue' size='2'>[codes]</font></div>&nbsp;&nbsp;总金额:<font color='red'>[money]</font> 游戏币",
	  msg_success   : "购买成功<br />请注意查看参与游戏记录",
	  msg_adjust_t  : "信息提示",
	  msg_adjust_t1 : "您投注的号码中, 有部分号码的奖金有所变动",
	  msg_adjust_t2 : "此单包含的奖金变动号码",
	  msg_adjust_t3 : "目前中奖金额",
	  msg_adjust_t4 : "我接受以上号码的奖金变动, 并且本次投注不要再次提示我",
	  msg_adjust_t5 : "我接受以上号码的奖金变动, (本次投注若有其他号码奖金被变化,请再次让我确认)",
	  msg_adjust_t6 : "我拒绝接受",
	  msg_adjust_t7 : " 确 认 ",
	  //[编辑区]
	  msg_edit_t1   : "请选择要导入的文件",
	  msg_edit_t2   : "导入文件",
	  msg_edit_t3   : "正在载入...",
	  msg_edit_t4   : "以下号码错误,请重新编辑后再重试",
	  msg_edit_t5   : "以下号码重复:",
	  msg_edit_t6   : "<br>&nbsp;&nbsp;&nbsp;&nbsp;号码 <font color='red'>[code]</font> 在订单中出现了 <font color='red'>[count]</font> 次 ",
	  msg_edit_t7   : "您确认要添加吗?",
	  msg_edit_t8   : "没有重复号码",
	  msg_edit_t9   : "已删除以下重复号码:<br><br>[codes]",
	  //[追号区]
	  msg_trace_t1  : "请先选择投注号码",
	  msg_trace_t2  : "购买金额过多超过奖金值无意义",
	  msg_trace_t3  : "期数不正确,最大期数为",
	  msg_trace_t4  : "请填写追号期数",
	  msg_trace_t5  : "请填写倍数",
	  msg_trace_t6  : "<strong>同倍: </strong>\r\n\r\n    [times] 倍",//同倍提示
	  msg_trace_t7  : "请填写相隔期数",
	  msg_trace_t8  : "<strong>翻倍: </strong><br />&nbsp;&nbsp;  相隔 [trace_step] 期  x [times_step] 倍",//翻倍提示
	  msg_trace_t9  : "最低利润额有误,请重新填写",
	  msg_trace_t10 : "<strong>利润追号：</strong><br />&nbsp;&nbsp;   最低利润：[minprofit]\r\n    起始倍数：[startTime]",//利润追号提示
	  msg_trace_t11 : "最低利润率有误,请重新填写",
	  msg_trace_t12 : "<strong>利润率追号：</strong><br />&nbsp;&nbsp;    最低利润率：[minmargin]\r\n    起始倍数：[startTime]",//利润率追号提示
	  msg_trace_t13 : "追号总金额超出了可用余额",
	  msg_trace_t14 : "确定要追号 [issuecount] 期？",
	  msg_trace_t15 : "请填写正确的倍数",
	  
	  //时间
	  time_day			: "天",
	  time_hour			: "小时",
	  time_minute		: "分",
	  
	  //数字[全角]
	  numbers			  : ["０","１","２","３","４","５","６","７","８","９"],
	  //编辑区号码分隔符号
	  splitsign     : /[lottery_lang.splitsign]/g
	  
};