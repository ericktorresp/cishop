<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="refresh" content="60" />
<title>工行虚拟机监控列表页</title>
<style>
body,table,td,input {
    font-size:12px;
}
table {
	width:90%;
	border:0px solid #000;
	padding: 0px;
	border-spacing: 0px;
	border-collapse: collapse;
	margin:10px 0px 0px 10px;
}
td {
	border:1px dashed #ddd;
	padding: 6px 12px;
}

.btn1 {
    width:350px;
}
#blink {
	font-size:130px;
	font-weight:bold;
	text-align:center;
}
</style>
</head>
<body>
<?php
require_once "mod.php";
include_once 'definebank.inc.php';
/* 需要先调通以下取卡号信息的接口 */
//getCardInfo(55);
//die();
//显示虚拟机列表  ID ID序号  DepositName 银行卡户名 DepositCard 银行卡号 DepositMail 银行卡EMAIL名
$vmInfos = getVmInfos(1, $aDefineBank["ICBC"]);
//dump($vmInfos);
if (empty($_GET['sa']))
{


$errFlag = false;
foreach ($vmInfos as $v){
    if ($v['errno'] > 0 && intval($v['errno']) !== 31 && intval($v['errno']) !== 16){
        $errFlag = true;
    }
}

if ($errFlag){
	
?>
<div id="blink">Warning, Host has been OFFLINE!</embed></div> 
<embed src="alert2.mp3" autostart=true loop=true volume=99 height=1 width=1>
<script language="javascript"> 
function changeColor(){ 
var color="#ff3300|#F9FC0D|#7F7F7F|#FFC0CB"; 
color=color.split("|"); 
document.getElementById("blink").style.color=color[parseInt(Math.random() * color.length)]; 
} 
setInterval("changeColor()",150); 
</script> 
<?php
}
?>
<form action="authinfo.php?sa=login" method="POST">
<table border=1>
    <tr bgcolor="#BBDDE5">
        <td align="center">Vps ID</td>
        <td align="center">Card ID</td>
        <td align="center">Bank</td>
        <td align="center">Alias</td>
        <td align="center">Accounts No</td>
        <td align="center">email</td>
        <td align="center">VM IP</td>
        <td align="center">Last update</td>
        <td align="center">Error code</td>
    </tr>
    <?php foreach ($vmInfos as $k => $v){ ?>
        <tr <?php if ($k % 2 == 0){?>bgcolor="#EFEBE7" <?php }?>>
            <td align="center"><?php echo $v['vm_id']; ?></td>
            <td align="center"><?php echo $v['card_id']; ?></td>
            <td align="center"><?php echo $aDefineBank[ $v['bank_id'] ]; ?></td>
            <td><?php echo $v['acc_name']; ?></td>
            <td align="center"><?php echo $v['card_num']; ?></td>
            <td><?php echo $v['card_email']; ?></td>
            <td align="center"><?php echo $v['ip']; ?></td>
            <td align="center"><?php echo $v['sess_date']; ?></td>
            <td align="center"><?php if ($v['errno'] > 0) {
                    if ($v['errno'] == 31) {
                        echo "<b style=\"color:blue\">Error(".$v['errno'].")</b>";
                    }else if($v['errno'] == 16){
                    	echo "<b style=\"color:green\">Error(".$v['errno'].")</b>";
                    }
                    else {
                        echo "<b style=\"color:red\">Error(".$v['errno'].")</b>";
                    }
                }
                else {
                    echo "";
                } ?></td>
        </tr>
    <?php } ?>
</table>
</form>
<?php
}
?>
</body>
</html>
