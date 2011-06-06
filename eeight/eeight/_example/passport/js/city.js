// JavaScript Document
function getCity(obj)
{
	var city = new Array();
	city[0] = "北京";
	city[1] = "深圳";
	city[2] = "上海";
	city[3] = "重庆";
	city[4] = "天津";
	city[5] = "广东";
	city[6] = "河北";
	city[7] = "山西";
	city[8] = "内蒙古";
	city[9] = "辽宁";
	city[10] = "吉林";
	city[11] = "黑龙江";
	city[12] = "江苏";
	city[13] = "浙江";
	city[14] = "安徽";
	city[15] = "福建";
	city[16] = "江西";
	city[17] = "山东";
	city[18] = "河南";
	city[19] = "湖北";
	city[20] = "湖南";
	city[21] = "广西";
	city[22] = "海南";
	city[23] = "四川";
	city[24] = "贵州";
	city[25] = "云南";
	city[26] = "西藏";
	city[27] = "陕西";
	city[28] = "甘肃";
	city[29] = "青海";
	city[30] = "宁夏";
	city[31] = "新疆";
	city[32] = "香港";
	city[33] = "澳门";
	city[33] = "台湾";
	for( i=0; i< city.length; i++ )
	{
		obj.options[i+1] = new Option( city[i],city[i] );
	}
}