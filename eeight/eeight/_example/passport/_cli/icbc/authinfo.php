<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>工行虚拟机登录页</title>
<?php 
if (empty($_GET['sa'])){
?>
<meta http-equiv="refresh" content="60" />
<?php 
}
?>
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
	text-align: center;
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

function updateHost($ip) {
    $file = "c:/windows/system32/drivers/etc/hosts";
    if (!is_writable($file)) {
        die('File host is not writable');
    }
    file_put_contents($file, "$ip\tmybank.icbc.com.cn");
}

//显示虚拟机列表  ID ID序号  DepositName 银行卡户名 DepositCard 银行卡号 DepositMail 银行卡EMAIL名
$vmInfos = getVmInfos(1, $aDefineBank["ICBC"]);
if (empty($vmInfos)){
	die("have no vminfos");
}
//dump($vmInfos);
if (empty($_GET['sa'])){
?>
<?php
$errFlag = false;
foreach ($vmInfos as $v){
    if ($v['errno'] > 0 && intval($v['errno']) !== 31 && intval($v['errno']) !== 16){
        $errFlag = true;
    }
}
if ($errFlag){
?>
<embed src="alert2.mp3" autostart=true loop=true volume=99 height=1 width=1></embed>
<?php
}
?>
<form action="./authinfo.php?sa=login" method="POST">
<table border=1>
    <tr bgcolor="#BBDDE5">
        <td align="center">Vps ID</td>
        <td align="center">Card ID</td>
        <td align="center">Alias</td>
        <td align="center">Accounts No</td>
        <td align="center">email</td>
        <td align="center">Last update</td>
        <td align="center">Error code</td>
        <td align="center">Operation</td>
    </tr>
    <?php foreach ($vmInfos as $k => $v){ ?>
        <tr <?php if ($k % 2 == 0){?>bgcolor="#EFEBE7" <?php }?>>
            <td align="center"><?php echo $v['vm_id']; ?></td>
            <td align="center"><?php echo $v['card_id']; ?></td>
            <td align="center"><?php echo $v['acc_name']; ?></td>
            <td align="center"><?php echo $v['card_num']; ?></td>
            <td><?php echo $v['card_email']; ?></td>
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
            <td align="center"><input type="button" onclick="this.form.vm_id.value=<?php echo $v['vm_id']; ?>;this.form.submit();" value="Login" style="height:18px;top:0px;" /></td>
        </tr>
    <?php } ?>
</table>
    <input type="hidden" name="vm_id" value=""/>
</form>

<?php
}elseif (isset($_GET['sa']) && $_GET['sa'] == 'login'){
    $vm_id = $_POST['vm_id'];
    // 得到虚拟机信息
    if (!$vmInfo = getVmInfo($vm_id)) {
        die('<h3>Sorry can not find virtual machine records !</h3>');
    }
//dump($vmInfo);
    // 修改host文件
    updateHost($vmInfo['ip']);
    sleep(1);
    
    // 显示提交表单
?>
<h4>Login info：</h4>
<form action="./authinfo.php?sa=inputAuth&vm_id=<?=$vmInfo['vm_id'];?>" method="POST">
    <table>
        <tr><td>Accounts No：</td><td><input type="text" class="btn1" name="cardNum" value="<?=$vmInfo['card_num'];?>" /></td></tr>
        <tr><td>Last update：</td><td><?=$vmInfo['sess_date'];?></td></tr>
        <tr><td>Session ID：</td><td><input type="text" class="btn1" name="dse_sessionId" value="" /></td></tr>
        <tr><td>cookie：</td><td><input type="text" class="btn1" name="cookie" value="" /></td></tr>
        <tr><td colspan=2 align=center><input type="submit" name="submit" value="submit"/></td></tr>
    </table>
</form>
<?php
} elseif (isset($_GET['sa']) && $_GET['sa'] == 'inputAuth'){
    $vm_id = $_GET['vm_id'];
    $cardNum = $_POST['cardNum'];
    $acctNum = $_POST['acctNum'];
    $dse_sessionId = $_POST['dse_sessionId'];
    $cookie = $_POST['cookie'];
    if (!$cardNum || !$dse_sessionId || !$cookie || intval($vm_id) <= 0) {
        echo "<script> alert('The parameters is Incomplete, input again please');</script>";
        die();
    }

    //记录登录日志
    ICBC_log($cardNum, 1);
    $sql = "UPDATE vmtables SET dse_session_id='$dse_sessionId', cookie='$cookie' , errno = 0 WHERE vm_id='$vm_id' LIMIT 1";
    $db->query($sql);
    if ($db->affected_rows <= 0) {
        echo '<h1 style="color:red">sorry, update error!</h1>';
    }
    else {
        echo '<h1>update successful !</h1>';
    }
    $url = "http://".$_SERVER['HTTP_HOST']."/".$_SERVER['PHP_SELF'];
    echo "<br/>Program will automatically <button onclick='location.href=\"$url\";'>return</button> in 300 seconds<script>setTimeout('location.href=\"$url\";', 300000);</script>";
}
?>
</body>
</html>
