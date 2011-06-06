var Browser = new Object();

Browser.isMozilla = (typeof document.implementation != 'undefined') && (typeof document.implementation.createDocument != 'undefined') && (typeof HTMLDocument != 'undefined');
Browser.isIE = window.ActiveXObject ? true : false;
Browser.isFirefox = (navigator.userAgent.toLowerCase().indexOf("firefox") != - 1);
Browser.isSafari = (navigator.userAgent.toLowerCase().indexOf("safari") != - 1);
Browser.isOpera = (navigator.userAgent.toLowerCase().indexOf("opera") != - 1);

var Utils = new Object();

Utils.htmlEncode = function(text)
{
  return text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

Utils.trim = function( text )
{
  if (typeof(text) == "string")
  {
    return text.replace(/^\s*|\s*$/g, "");
  }
  else
  {
    return text;
  }
}

Utils.isEmpty = function( val )
{
  switch (typeof(val))
  {
    case 'string':
      return Utils.trim(val).length == 0 ? true : false;
      break;
    case 'number':
      return val == 0;
      break;
    case 'object':
      return val == null;
      break;
    case 'array':
      return val.length == 0;
      break;
    default:
      return true;
  }
}

Utils.isNumber = function(val)
{
  var reg = /^[\d|\.|,]+$/;
  return reg.test(val);
}

Utils.isInt = function(val)
{
  if (val == "")
  {
    return false;
  }
  var reg = /\D+/;
  return !reg.test(val);
}

Utils.isEmail = function( email )
{
  var reg1 = /([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/;

  return reg1.test( email );
}

Utils.isTel = function ( tel )
{
  var reg = /^[\d|\-|\s|\_]+$/; //只允许使用数字-空格等

  return reg.test( tel );
}

Utils.fixEvent = function(e)
{
  var evt = (typeof e == "undefined") ? window.event : e;
  return evt;
}

Utils.srcElement = function(e)
{
  if (typeof e == "undefined") e = window.event;
  var src = document.all ? e.srcElement : e.target;

  return src;
}

Utils.isTime = function(val)
{
  var reg = /^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}$/;

  return reg.test(val);
}

Utils.x = function(e)
{ //当前鼠标X坐标
    return Browser.isIE?event.x + document.documentElement.scrollLeft - 2:e.pageX;
}

Utils.y = function(e)
{ //当前鼠标Y坐标
    return Browser.isIE?event.y + document.documentElement.scrollTop - 2:e.pageY;
}

Utils.request = function(url, item)
{
	var sValue=url.match(new RegExp("[\?\&]"+item+"=([^\&]*)(\&?)","i"));
	return sValue?sValue[1]:sValue;
}

Utils.$ = function(name)
{
    return document.getElementById(name);
}

function rowindex(tr)
{
  if (Browser.isIE)
  {
    return tr.rowIndex;
  }
  else
  {
    table = tr.parentNode.parentNode;
    for (i = 0; i < table.rows.length; i ++ )
    {
      if (table.rows[i] == tr)
      {
        return i;
      }
    }
  }
}

document.getCookie = function(sName)
{
  // cookies are separated by semicolons
  var aCookie = document.cookie.split("; ");
  for (var i=0; i < aCookie.length; i++)
  {
    // a name/value pair (a crumb) is separated by an equal sign
    var aCrumb = aCookie[i].split("=");
    if (sName == aCrumb[0])
      return decodeURIComponent(aCrumb[1]);
  }

  // a cookie with the requested name does not exist
  return null;
}

document.setCookie = function(sName, sValue, sExpires)
{
  var sCookie = sName + "=" + encodeURIComponent(sValue);
  if (sExpires != null)
  {
    sCookie += "; expires=" + sExpires;
  }

  document.cookie = sCookie;
}

document.removeCookie = function(sName,sValue)
{
  document.cookie = sName + "=; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
}

function getPosition(o)
{
    var t = o.offsetTop;
    var l = o.offsetLeft;
    while(o = o.offsetParent)
    {
        t += o.offsetTop;
        l += o.offsetLeft;
    }
    var pos = {top:t,left:l};
    return pos;
}

function cleanWhitespace(element)
{
  var element = element;
  for (var i = 0; i < element.childNodes.length; i++) {
   var node = element.childNodes[i];
   if (node.nodeType == 3 && !/\S/.test(node.nodeValue))
     element.removeChild(node);
   }
}

//对字符串对象增加trim去掉前后空格的方法
String.prototype.trim = function(){
    return this.replace(/(?:^\s*)|(?:\s*$)/g, "");
}

//全选，取消全选
function selectAll(obj){
	jQuery(":checkbox[id!='"+obj+"']").attr("checked",jQuery("#"+obj).attr("checked"));
}

//用户名验证(由0-9,a-z,A-Z组成的6~16个字符组成)
function validateUserName( str ){
	var patrn = /^[0-9a-zA-Z]{6,16}$/;
	if( patrn.exec(str) ){
		return true;	
	}else{
		return false;
	}
}

//密码验证(6－16位数字和字母，不能只是数字，或者只是字母，不能连续三位相同)
function validateUserPss( str ){
	var patrn = /^[0-9a-zA-Z]{6,16}$/;
	if( !patrn.exec(str) ){
		return false;
	}
	patrn = /^\d+$/;
	if( patrn.exec(str) ){
		return false;
	}
	patrn = /^[a-zA-Z]+$/;
	if( patrn.exec(str) ){
		return false;
	}
	patrn = /(.)\1{2,}/;
	if( patrn.exec(str) ){
		return false;
	}
	return true;
}

//呢称验证
function validateNickName( str ){
	var patrn = /^(.){2,6}$/;	
	if( patrn.exec(str) ){
		return true;	
	}else{
		return false;
	}
}

//日期输入验证
// str : 要验证的日期字符串[格式包括 Y-M-D|Y/M/D|YMD [H[:I][:S]]]
function validateInputDate( str ){
	str = str.trim();
	if( str == "" || str == null ){
		return true;
	}
	var tempArr = str.split(" ");
	var dateArr = new Array();
	var timeArr = new Array();
	if( tempArr[0].indexOf("-") != -1 ){//2009-06-12
		dateArr = tempArr[0].split("-");
	}else if( tempArr[0].indexOf("/") != -1 ){//2009/06/12
		dateArr = tempArr[0].split("/");
	}else{// 20090612
		if( tempArr[0].toString().length < 8 ){
			return false;
		}
		dateArr[0] = tempArr[0].substring(0,4);
		dateArr[1] = tempArr[0].substring(4,6);
		dateArr[2] = tempArr[0].substring(6,8);
	}
	if( tempArr[1] == undefined || tempArr[1] == null ){
		tempArr[1] = "00:00:00";
	}
	if( tempArr[1].indexOf(":") != -1 ){
		timeArr = tempArr[1].split(":");
	}
	if( dateArr[2] != undefined && ( dateArr[0] == "" || dateArr[1] == "" ) ){
		return false;
	}
	if( dateArr[1] != undefined && dateArr[0] == "" ){
		return false;
	}
	if( timeArr[2] != undefined && ( timeArr[0] == "" || timeArr[1] == "" ) ){
		return false;
	}
	if( timeArr[1] != undefined && timeArr[0] == "" ){
		return false;
	}
	dateArr[0]  = (dateArr[0]==undefined || dateArr[0] == "") ? 1970 : dateArr[0];
	dateArr[1]  = (dateArr[1]==undefined || dateArr[1] == "") ? 0 : (dateArr[1]-1);
	dateArr[2]  = (dateArr[2]==undefined || dateArr[2] == "") ? 0 : dateArr[2];
	timeArr[0]  = (timeArr[0]==undefined || timeArr[0] == "") ? 0 : timeArr[0];
	timeArr[1]  = (timeArr[1]==undefined || timeArr[1] == "") ? 0 : timeArr[1];
	timeArr[2]  = (timeArr[2]==undefined || timeArr[2] == "") ? 0 : timeArr[2];
	var newDate = new Date(dateArr[0],dateArr[1],dateArr[2],timeArr[0],timeArr[1],timeArr[2]); 
	if( newDate.getFullYear()==dateArr[0] && newDate.getMonth()==dateArr[1] && newDate.getDate()==dateArr[2] && newDate.getHours()==timeArr[0] && newDate.getMinutes()==timeArr[1] && newDate.getSeconds()==timeArr[2] ){
		return true;
	}else{
		return false;
	}
	return true;
}

//onkeyup:限制用户资金输入只能输入浮点数，并且小数点后只能跟四位
function checkMoney( obj ){
	obj.value = formatFloat(obj.value);
}

//onkeyup:根据用户输入的资金做检测并自动转换中文大写金额(用于充值和提现)
//obj:检测对象元素，chineseid:要显示中文大小写金额的ID，maxnum：最大能输入金额
function checkWithdraw( obj,chineseid,maxnum ){
	obj.value = formatFloat(obj.value);
	if( parseFloat(obj.value) > parseFloat(maxnum) ){
		alert("输入金额超出了可用余额");	
		obj.value = maxnum;
	}
	jQuery("#"+chineseid).html( changeMoneyToChinese(obj.value) );
}

//格式化浮点数形式(只能输入正浮点数，且小数点后只能跟四位,总体数值不能大于999999999999999共15位:数值999兆)
function formatFloat( num ){
	num = num.replace(/^[^\d]/g,'');
	num = num.replace(/[^\d.]/g,'');
	num = num.replace(/\.{2,}/g,'.');
	num = num.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
	if( num.indexOf(".") != -1 ){
		var data = num.split('.');
		num = (data[0].substr(0,15))+'.'+(data[1].substr(0,2));
	}else{
		num = num.substr(0,15);
	}
	return num;
}

function moneyFormat(num){
	sign = Number(num) < 0 ? "-" : "";
    num = num.toString();
	if( num.indexOf(".") == -1 )
	{
		num = "" + num + ".00";
	}
	var data = num.split('.');
	data[0] = data[0].toString().replace(/[^\d]/g,"").substr(0,15);
	data[0] = Number(data[0]).toString();
	var newnum = [];
	for( i=data[0].length; i>0; i-=3 ){
		newnum.unshift(data[0].substring(i,i-3));
	}
	data[0] = newnum.join(",");
	data[1] = data[1].toString().substr(0,2);
	return sign+""+data[0] + "." + data[1];
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
	
	if( money == "" ){
		return "";	
	}
	
	money = parseFloat(money);
	//alert(money);
	if( money >= maxNum ){
		alert('超出最大处理数字');
		return "";
	}
	if( money == 0 ){
		ChineseStr = cnNums[0]+cnIntLast+cnInteger;
		//document.getElementById("show").value=ChineseStr;
		return ChineseStr;
	}
	money = money.toString(); //转换为字符串
	if( money.indexOf(".") == -1 ){
		IntegerNum = money;
		DecimalNum = '';
	}else{
		parts = money.split(".");
		IntegerNum = parts[0];
		DecimalNum = parts[1].substr(0,4);
	}
	if( parseInt(IntegerNum,10) > 0 ){//获取整型部分转换
		zeroCount = 0;
		IntLen = IntegerNum.length;
		for( i=0;i<IntLen;i++ ){
			n = IntegerNum.substr(i,1);
			p = IntLen - i - 1;
			q = p / 4;
            m = p % 4;
			if( n == "0" ){
				zeroCount++;
			}else{
				if( zeroCount > 0 ){
					ChineseStr += cnNums[0];
				}
				zeroCount = 0;	//归零
				ChineseStr += cnNums[parseInt(n)]+cnIntRadice[m];
			}
			if( m==0 && zeroCount<4 ){
				ChineseStr += cnIntUnits[q];
			}
		}
		ChineseStr += cnIntLast;
	//整型部分处理完毕
	}
	if( DecimalNum!= '' ){//小数部分
		decLen = DecimalNum.length;
		for( i=0; i<decLen; i++ ){
			n = DecimalNum.substr(i,1);
			if( n != '0' ){
				ChineseStr += cnNums[Number(n)]+cnDecUnits[i];
			}
		}
	}
	if( ChineseStr == '' ){
		ChineseStr += cnNums[0]+cnIntLast+cnInteger;
	}
	else if( DecimalNum == '' ){
		ChineseStr += cnInteger;
	}
	return ChineseStr;
	
}

//转换HTML标签为标准代码(类似PHP的htmlspecialchars函数)
function replaceHTML( str ){
	str = str.replace(/[&]/g,'&amp;');
	str = str.replace(/[\"]/g,'&quot;');
	str = str.replace(/[\']/g,'&#039;');
	str = str.replace(/[<]/g,'&lt;');
	str = str.replace(/[>]/g,'&gt;');
	str = str.replace(/[ ]/g,'&nbsp;');
	return str;
}

//转换HTML标准代码为显示代码（类似PHP的htmlspecialchars_decode函数）
function replaceHTML_DECODE( str ){
	str = str.replace(/&amp;/g,'&');
	str = str.replace(/&quot;/g,'"');
	str = str.replace(/&#039;/g,'\'');
	str = str.replace(/&lt;/g,'<');
	str = str.replace(/&gt;/g,'>');
	str = str.replace(/&nbsp;/g,' ');
	return str;
}

//复制内容到剪贴板
function copyToClipboard(obj)
{
	txt = jQuery("#"+obj).html();
	if(window.clipboardData) {
		window.clipboardData.clearData();
		window.clipboardData.setData("Text", txt);
	}else if(navigator.userAgent.indexOf("Opera") != -1) {
		window.location = txt;
	}else if (window.netscape) {
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch (e) {
			alert("您的firefox安全限制限制您进行剪贴板操作，请在地址栏中输入“about:config”将“signed.applets.codebase_principal_support”设置为“true”之后重试");
			return false;
		}
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip)
		return;
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans)
		return;
		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext = txt;
		str.data = copytext;
		trans.setTransferData("text/unicode",str,copytext.length*2);
		var clipid = Components.interfaces.nsIClipboard;
		if (!clip)
		return false;
		clip.setData(trans,null,clipid.kGlobalClipboard);
	}
}