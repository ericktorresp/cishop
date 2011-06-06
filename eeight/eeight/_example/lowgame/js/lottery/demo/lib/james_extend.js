/**
*   对javascript方法的扩展
*
*   version 1.0.0.0
*   author: james
*   update: 2009/07/16
*/

/**************************[ 对String对象的扩展 ]*************************************/
String.prototype.trim = function(){//除去字符串两边的空格
    return this.replace(/(?:^\s*)|(?:\s*$)/g, "");
}

/**************************[ 对Math对象的扩展 ]*************************************/

/**************************[ 对Number对象的扩展 ]*************************************/
Number.prototype.add  = function( arg1,arg2 ){//精确计算JS的加法运算
    var r1,r2,m; 
    try{r1=arg1.toString().split(".")[1].length;}catch(e){r1=0;} 
    try{r2=arg2.toString().split(".")[1].length;}catch(e){r2=0;} 
    m=Math.pow(10,Math.max(r1,r2)) 
    return (arg1*m+arg2*m)/m 
}
Number.prototype.subt = function( arg1,arg2 ){//精确计算JS的减法运算
    var r1,r2,m,n;
    try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
    try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
    m=Math.pow(10,Math.max(r1,r2));
    n=(r1>=r2)?r1:r2;
    return Number(((arg1*m-arg2*m)/m).toFixed(n));
}
Number.prototype.mult = function( arg1,arg2 ){//精确计算JS的乘法运算
    var m=0,s1=arg1.toString(),s2=arg2.toString(); 
    try{m+=s1.split(".")[1].length;}catch(e){} 
    try{m+=s2.split(".")[1].length;}catch(e){} 
    return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);
}
Number.prototype.div = function(arg1,arg2){//精确计算JS的除法运算
    var t1=0,t2=0,r1,r2; 
    try{t1=arg1.toString().split(".")[1].length;}catch(e){} 
    try{t2=arg2.toString().split(".")[1].length;}catch(e){}
    return Number(arg1.toString().replace(".",""))*Number(arg2.toString().replace(".",""))*pow(10,t2-t1);
}

/**************************[ 对Array对象的扩展 ]*************************************/

/**************************[ 对Date对象的扩展 ]*************************************/