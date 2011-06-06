// JavaScript Document
/**
*	JS通用函数，依赖Jquery
*	
*	@author:	james
*	@version:	1.0.5
*	@date:		2009/05/06
*
*/

//全选，取消全选
function selectAll(obj)
{
	jQuery(":checkbox[id!='"+obj+"']").attr("checked",jQuery("#"+obj).attr("checked"));
	//jQuery("#"+obj).parents("form").find(":checkbox[id!='"+obj+"']").attr("checked",jQuery("#"+obj).attr("checked"));
	//jQuery("#"+obj).parents("table").find("input[type=checkbox][id!='"+obj+"']").attr("checked",jQuery("#"+obj).attr("checked"));
}

//用户名验证(由0-9,a-z,A-Z组成的6~16个字符组成)
function validateUserName( str )
{
	var patrn = /^[0-9a-zA-Z]{6,16}$/g;
	if( patrn.exec(str) )
	{
		return true;	
	}
	else
	{
		return false;
	}
}

//密码验证(6－16位数字和字母，不能只是数字，或者只是字母，不能连续三位相同)
function validateUserPss( str )
{
	var patrn = /^[0-9a-zA-Z]{6,16}$/g;
	if( !patrn.exec(str) )
	{
		return false;
	}
	patrn = /^\d+$/g;
	if( patrn.exec(str) )
	{
		return false;
	}
	patrn = /^[a-zA-Z]+$/g;
	if( patrn.exec(str) )
	{
		return false;
	}
	patrn = /(.)\1{2,}/g;
	if( patrn.exec(str) )
	{
		return false;
	}
	return true;
}

//呢称验证
function validateNickName( str )
{
	var patrn = /^.{4,10}$/g;	
	if( patrn.exec(str) )
	{
		return true;	
	}
	else
	{
		return false;
	}
}

//onkeyup:限制用户资金输入只能输入浮点数，并且小数点后只能跟四位
function checkMoney( obj )
{
	obj.value = formatFloat(obj.value);
}

//onkeyup:根据用户输入的资金做检测并自动转换中文大写金额(用于充值和提现)
//obj:检测对象元素，chineseid:要显示中文大小写金额的ID，maxnum：最大能输入金额
function checkWithdraw( obj,chineseid,maxnum )
{
	obj.value = formatFloat(obj.value);
	if( parseFloat(obj.value) > parseFloat(maxnum) )
	{
		alert("输入金额超出了可用余额");	
		obj.value = maxnum;
	}
	jQuery("#"+chineseid).html( changeMoneyToChinese(obj.value) );
}

//格式化浮点数形式(只能输入正浮点数，且小数点后只能跟四位,总体数值不能大于999999999999999共15位:数值999兆)
function formatFloat( num )
{
	num = num.replace(/^[^\d]/g,'');
	num = num.replace(/[^\d.]/g,'');
	num = num.replace(/\.{2,}/g,'.');
	num = num.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
	if( num.indexOf(".") != -1 )
	{
		var data = num.split('.');
		num = (data[0].substr(0,15))+'.'+(data[1].substr(0,2));
	}
	else
	{
		num = num.substr(0,15);
	}
	return num;
}

//自动转换数字金额为大小写中文字符,返回大小写中文字符串，最大处理到999兆
function changeMoneyToChinese( money )
{
	var cnNums	= new Array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖");	//汉字的数字
	var cnIntRadice = new Array("","拾","佰","仟");	//基本单位
	var cnIntUnits = new Array("","万","亿","兆");	//对应整数部分扩展单位
	var cnDecUnits = new Array("角","分","毫","厘");	//对应小数部分单位
	var cnInteger = "整";	//整数金额时后面跟的字符
	var cnIntLast = "元";	//整型完以后的单位
	var maxNum = 999999999999999.9999;	//最大处理的数字
	
	var IntegerNum;		//金额整数部分
	var DecimalNum;		//金额小数部分
	var ChineseStr="";	//输出的中文金额字符串
	var parts;		//分离金额后用的数组，预定义
	
	if( money == "" )
	{
		return "";	
	}
	
	money = parseFloat(money);
	//alert(money);
	if( money >= maxNum )
	{
		alert('超出最大处理数字');
		return "";
	}
	if( money == 0 )
	{
		ChineseStr = cnNums[0]+cnIntLast+cnInteger;
		//document.getElementById("show").value=ChineseStr;
		return ChineseStr;
	}
	money = money.toString(); //转换为字符串
	if( money.indexOf(".") == -1 )
	{
		IntegerNum = money;
		DecimalNum = '';
	}
	else
	{
		parts = money.split(".");
		IntegerNum = parts[0];
		DecimalNum = parts[1].substr(0,4);
	}
	if( parseInt(IntegerNum,10) > 0 )
	{//获取整型部分转换
		zeroCount = 0;
		IntLen = IntegerNum.length;
		for( i=0;i<IntLen;i++ )
		{
			n = IntegerNum.substr(i,1);
			p = IntLen - i - 1;
			q = p / 4;
            m = p % 4;
			if( n == "0" )
			{
				zeroCount++;
			}
			else
			{
				if( zeroCount > 0 )
				{
					ChineseStr += cnNums[0];
				}
				zeroCount = 0;	//归零
				ChineseStr += cnNums[parseInt(n)]+cnIntRadice[m];
			}
			if( m==0 && zeroCount<4 )
			{
				ChineseStr += cnIntUnits[q];
			}
		}
		ChineseStr += cnIntLast;
	//整型部分处理完毕
	}
	if( DecimalNum!= '' )
	{//小数部分
		decLen = DecimalNum.length;
		for( i=0; i<decLen; i++ )
		{
			n = DecimalNum.substr(i,1);
			if( n != '0' )
			{
				ChineseStr += cnNums[Number(n)]+cnDecUnits[i];
			}
		}
	}
	if( ChineseStr == '' )
	{
		ChineseStr += cnNums[0]+cnIntLast+cnInteger;
	}
	if( DecimalNum == '' )
	{
		ChineseStr += cnInteger;
	}
	return ChineseStr;
	
}

//jQuery("document").ready( function(){
	//do after document load
//});
