<?php

/**
 * 功能：付款卡管理
 * 
 * +++++actionBanklist 网银列表
 * +++++actionSavebankinfo   增加新的网银
 * +++++EnableCard 设置是否启用银行卡
 * +++++actionDelCard 删除银行卡
 * +++++actionEditAmount 设置余额(只允许修改金额)
 * +++++actionEditbankinfo 修改网银信息
 * +++++actionGetWithdraworders 查看帐变
 *
 * @author jack,jader
 */
class controller_netbankmanage extends basecontroller
{
   /**
    * 网银列表
    */
    function actionBanklist()
    {
        $oNetbank = new model_netbank();
        $oOrders = new model_withdraworders();
        $aNetbank = $oNetbank->getnetbanklist();
        $idlist = array();
        if($aNetbank)
        {
            
            foreach($aNetbank as $k=>$v)
            {
                $idlist[] = $v['paycard_id'];
            }

            $withdrawsNum = $oOrders->getWithdrawsNum($idlist, 'all');
            foreach($aNetbank as $k => &$v)
            {
                $v['sum_transfer_in'] = $withdrawsNum[$v['paycard_id']]['sum_transfer_in'];
                $v['sum_transfer_out'] = $withdrawsNum[$v['paycard_id']]['sum_transfer_out'];
                $v['sum_fee'] = $withdrawsNum[$v['paycard_id']]['sum_fee'];
                $v['diff'] = number_format($v['init_amount'] + $v['sum_transfer_in']-$v['sum_transfer_out']-$v['sum_fee']-$v['amount'],2, '.', '');
                $v['count'] = $withdrawsNum[$v['paycard_id']]['count'];
            }
        }
        $GLOBALS['oView']->assign('banklist', $aNetbank);
        $GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("netbankmanage","savebankinfo"), 'text'=>'增加网银' ) );
        $GLOBALS['oView']->assign( "ur_here",    "网银列表");
        $GLOBALS['oView']->display( 'netbankmanage_banklist.html' );
        EXIT;
    }
    
    /**
     * 增加网银信息
     */
    function actionSavebankinfo ()
    {
        $aLinks = array(
			0 => array(
				'text' => "返回网银列表",
				'href' => "?controller=netbankmanage&action=banklist"
			)
		);
        if( isset($_POST['action']) && $_POST['action'] == 'savebankinfo' )
        {
            $_POST['finance_name'] = trim($_POST['finance_name']);
            // 2/22/2011 added
            if ( empty( $_POST['finance_name']) )   sysMessage ('财务识别名不能为空',   1);
            if ( empty($_POST['card_num']) )        sysMessage ('网银账号不能为空',     1);
            if (!preg_match('/\d/',$_POST['card_num']) && strlen($_POST['card_num']) != 16 && $_POST['card_num'] != 19)
                                                    sysMessage ('网银账号输入有误',     1);
            if ( empty($_POST['passwd'])  )          sysMessage ('登录密码不能为空',     1);
            if ( empty($_POST['repasswd']) )        sysMessage ('确认密码不能为空',     1);
            if ($_POST['passwd'] != $_POST['repasswd'])
                                                    sysMessage ('确认密码与登录密码输入不一致',1);
            if ( intval($_POST['init_amount']) < 0) sysMessage ('起初余额输入有误',     1);
            if ( empty($_POST['name']) )            sysMessage ('账户名不能为空',       1);
            if ( ! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $_POST['name'] ) || strlen($_POST['name']) > 15 )
                                                    sysMessage ('账户名输入有误',       1);
            if ( empty($_POST['area']) )            sysMessage ('所在省份不能为空',     1);
            if ( ! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $_POST['area'] ) )
                                                    sysMessage ('所在省份输入有误',     1);
            $oProvinceName = new model_withdraw_Area();
            $oProvinceName->Name = daddslashes( $_POST['area'] );
            if ( !$oProvinceName->areaIsExistByName() ) sysMessage ('所在省份输入有误', 1);
            
            $oNetbank = new model_netbank();
            /*
            if(!$oNetbank->checkUserPass($_POST['passwd']))
            {
                sysMessage('网银密码不符合规则', 1);
            }
            */
            
            $mNetbank = $oNetbank->addNetbank($_POST['finance_name'], $_POST['bank_id'], $_POST['card_num'], $_POST['passwd'],
                    $_POST['status'], $_POST['init_amount'],$_POST['name'], $_POST['area']);
            if($mNetbank === -1)
            {
                sysMessage('网银数据不完整2',1);
            }
            elseif( $mNetbank === -2 )
            {
                sysMessage('操作失败', 1);
            }
            else
            {
                sysMessage('操作成功', 0, $aLinks);
            }
        }
        
        $model_autopay = new model_autopay();
        $allBanks = $model_autopay->getAllBanks();

        // 2/22/2011 added
        // list province
        $oProvince = new model_withdraw_AreaList();
        $oProvince->ParentId = 0;
        $oProvince->Used = 1;
        $oProvince->init();
        $aProvince = $oProvince->Data;
        $GLOBALS['oView']->assign( 'ur_here', '增加网银' );
        $GLOBALS['oView']->assign( 'allBanks', $allBanks );
        $GLOBALS['oView']->assign( 'arealist',  $aProvince);
        $GLOBALS['oView']->assign( 'actionlink', array( 'href'=>url("netbankmanage","banklist"), 'text'=>'返回网银列表' ) );
        $GLOBALS['oView']->display( 'netbankmanage_add.html' );
        EXIT;
    }
    
    /**
     * 设置是否启用
     */
    function actionEnableCard()
    {
        $aLinks = array(
			0 => array(
				'text' => "返回网银列表",
				'href' => "?controller=netbankmanage&action=banklist"
			)
		);
        $id = intval($_GET['id']);
        $status = !intval($_GET['status']) ? 0 : 1;

        $oBankinfo = new model_netbank();
        // 强制下线，应允许其修改状态
//        if($aBankinfo['status'] ==2)
//        {
//            sysMessage('操作失败,此卡正在付款中，不能修改状态！', 1, $aLinks);
//        }
        if (!$oBankinfo->editNetBank($id, array('status'=>$status)))
        {
            sysMessage('操作失败', 1, $aLinks);
        }

        sysMessage('操作成功', 0, $aLinks);
    }
    
	/**
     * 删除银行卡
     */
    function actionDelCard()
    {
        $aLinks = array(
			0 => array(
				'text' => "返回网银列表",
				'href' => "?controller=netbankmanage&action=banklist"
			)
		);
        $id = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
        if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
        $oBankinfo = new model_netbank();
         //删除
        $mBankinfo = $oBankinfo->delNetBank($id);
        if($mBankinfo === -1)
        {
            sysMessage('参数不正确', 1);
        }
        if($mBankinfo === -2)
        {
            sysMessage('操作失败',1);
        }
        sysMessage('操作成功', 0, $aLinks);
    }
    
    
	/**
     * 追加转帐 可以转入或转出
     */
    function actionEditAmount(){
    	$aLinks = array(
			0 => array(
				'text' => "返回网银列表",
				'href' => "?controller=netbankmanage&action=banklist"
			)
		);
        $id = isset($_GET['id']) ? intval($_GET['id']) : intval($_POST['id']);
        if ($id <= 0)
        {
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
        $oBankinfo = new model_netbank();
        $model_withdraworders = new model_withdraworders();

        if (isset($_POST['submit']))
        {
            $_POST['transferIn'] = floatval($_POST['transferIn']);
            $_POST['transferOut'] = floatval($_POST['transferOut']);
            $_POST['transferOutFee'] = floatval($_POST['transferOutFee']);
            $_POST['remark'] = addslashes($_POST['remark']);
            try
            {
                $mBankinfo = $oBankinfo->updateAmount_Tran($id, $_POST['transferIn'], $_POST['transferOut'], $_POST['transferOutFee'], $_POST['remark'], $_SESSION['adminname']);
            }
            catch (Exception $e)
            {
                sysMessage($e->getMessage(), 1);
            }
            sysMessage('追加转帐成功');
        }

        if (!$paycard = $oBankinfo->getPayCardById($id))
        {
            sysMessage('找不到该付款卡', 1);
        }
        
        $withdrawsNum = $model_withdraworders->getWithdrawsNum(array($paycard['paycard_id']));
        $withdrawsNum = reset($withdrawsNum);

        $GLOBALS['oView']->assign('paycard', $paycard);
        $GLOBALS['oView']->assign('withdrawsNum', $withdrawsNum);
        $GLOBALS['oView']->assign( "ur_here", "修改金额" );
        $oBankinfo->assignSysInfo();
        $GLOBALS['oView']->display('netbankmanage_editamount.html');
    }
    
    /**
     * 修改网银信息
     */
    function actionEditbankinfo()
    {
        $aLinks = array(
			0 => array(
				'text' => "返回网银列表",
				'href' => "?controller=netbankmanage&action=banklist"
			)
		);
        $id = $_GET['id'] > 0 ? $_GET['id'] : $_POST['id'];
        
        //if (!is_numeric($_GET['id']) || intval($_GET['id']) <= 0){
        if (!is_numeric($id) || intval($id) <= 0){
			sysMessage("您提交的数据有误，请核对后重新提交！", 1, $aLinks);
		}
        $oBankinfo = new model_netbank();
        if (!$aBankinfo = $oBankinfo->getPayCardById($id, array(0, 1)))
        {
            sysMessage("找不到付款卡信息或者该卡正在使用", 1);
        }
        //修改
        if(isset($_POST['action']) && $_POST['action'] == 'editbankinfo')
        {
            // 2/22/2011 added
            if ( empty( $_POST['finance_name']) )   sysMessage ('财务识别名不能为空',   1);
            if ( empty($_POST['name']) )            sysMessage ('账户名不能为空',       1);
            if ( ! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $_POST['name'] ) || strlen($_POST['name']) > 15 )
                                                    sysMessage ('账户名输入有误',       1);
            if ( empty($_POST['area']) )            sysMessage ('所在省份不能为空',     1);
            if ( ! preg_match("/^[\x{4e00}-\x{9fa5}]+$/u", $_POST['area'] ) )
                                                    sysMessage ('所在省份输入有误',     1);
            $oProvinceName = new model_withdraw_Area();
            $oProvinceName->Name = daddslashes( $_POST['area'] );
            if ( !$oProvinceName->areaIsExistByName() ) sysMessage ('所在省份输入有误', 1);
            
            $aBankinfo = array(
                    'name'=>$_POST['name'],
                    'area'=>$_POST['area'],
                    'status'=> intval($_POST['status']),
                    'finance_name' => trim($_POST['finance_name']),
            );
            if ($_POST['passwd'])
            {
                if ( empty($_POST['repasswd']) ) sysMessage ('确认密码不能为空',1);
                if($_POST['passwd'] != $_POST['repasswd'])
                {
                    sysMessage('确认密码与登录密码输入不一致',1);
                }
                /*
                if( $oBankinfo->checkUserPass($_POST['passwd']) == false )
                {
                    sysMessage('输入密码不符合规范，请重新输入！',1);
                }
                */
                $aBankinfo['passwd'] = $_POST['passwd'];


            }
            
            $mBankinfo = $oBankinfo->editNetBank($id, $aBankinfo);
            if($mBankinfo === -1)
            {
                sysMessage('参数不正确', 1);
            }
            if($mBankinfo === -2)
            {
                sysMessage('操作失败', 1);
            }
            sysMessage('操作成功', 0, $aLinks);
        }

        // 2/22/2011 added
        // list province
        $oProvince = new model_withdraw_AreaList();
        $oProvince->ParentId = 0;
        $oProvince->Used = 1;
        $oProvince->init();
        $aProvince = $oProvince->Data;
        
        $GLOBALS['oView']->assign('finance_name', $aBankinfo['finance_name']);
        $GLOBALS['oView']->assign('paycard_id', $aBankinfo['paycard_id']);
      	$GLOBALS['oView']->assign('card_num', $aBankinfo['card_num']);
        $GLOBALS['oView']->assign('password', $aBankinfo['passwd']);
        $GLOBALS['oView']->assign('amount', $aBankinfo['amount']);
        $GLOBALS['oView']->assign('name', $aBankinfo['name']);
        $GLOBALS['oView']->assign('area', $aBankinfo['area']);
        $GLOBALS['oView']->assign('status', $aBankinfo['status']);
        $GLOBALS['oView']->assign( 'arealist',  $aProvince);
        $GLOBALS['oView']->assign( "ur_here", "修改网银信息" );
        $oBankinfo->assignSysInfo();
        $GLOBALS['oView']->display('netbankmanage_editbankinfo.html');
    }
    
    /**
     * 查看帐变
     */
    function actionGetWithdrawOrders()
    {
        $aLinks = array(
			0 => array(
				'text' => "返回网银列表",
				'href' => "?controller=netbankmanage&action=banklist"
			)
		);
        $paycard_id = intval($_GET['id']);
        if ($paycard_id <= 0)
        {
            sysMessage('非法的付款卡id', 1);
        }
        
        $model_netbank = new model_netbank();
        $paycard = $model_netbank->getPayCardById($paycard_id);

        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d 00:00:00');
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d 23:59:59');
        
        //查询出符合时间的记录
        if(isset($_POST['dosubmit']) && $_POST['dosubmit'] == 'search')
        {
            $sRedate = isset($_POST["endDate"]) ? $_POST["endDate"] : date('Y-m-d h:i:s');//结束时间
            $sRdate = getFilterDate($_POST["rdate"]);//开始时间
            if( $sRdate<>'' )
            {
                $GLOBALS['oView']->assign("rdate",getFilterDate($sRdate,"Y-m-d H:i:s"));
                $GLOBALS['oView']->assign("redate",getFilterDate($sRedate,"Y-m-d H:i:s"));
            }
        }
        $model_withdraworders = new model_withdraworders();
        $sOrderBy = ' ORDER BY `entry` DESC ';
        $withDraworders = $model_withdraworders->getWithDraworders($paycard_id, $startDate, $endDate, 'all', $sOrderBy, 25);  //加25表示取分页

        $oPage  = new pages($withDraworders['affects'], 25);
        $GLOBALS['oView']->assign('banklist', $withDraworders['results']);
        $GLOBALS['oView']->assign("pageinfo", $oPage->show(1));

        $GLOBALS['oView']->assign("startDate",$startDate);
        $GLOBALS['oView']->assign("endDate",$endDate);
        $GLOBALS['oView']->assign('paycard_id',$paycard_id);
        $GLOBALS['oView']->assign('paycard',$paycard);
        $GLOBALS['oView']->assign( "ur_here", "查看帐变" );
        $GLOBALS['oView']->assign('actionlink',   array( 'href'=>url("netbankmanage","banklist"), 'text'=>'返回网银列表' ) );
        $model_withdraworders->assignSysInfo();
        $GLOBALS['oView']->display('withdraworders_list.html');
    }
    
    /**
     * 导出csv文件
     */
    function actionoutputcsv()
    {
        $oUser = A::singleton('model_user');
        $paycard_id = $_POST['paycard_id'];
        $startDate = $_POST['startDate'];
        $endDate = $_POST['endDate'];
        if (!$startDate || !$endDate)
        {
            sysMessage('必须选择日期', 1);
        }

        $model_withdraworders = new model_withdraworders();
        if (!$withDraworders = $model_withdraworders->getWithDraworders($paycard_id, $startDate, $endDate, 'auto'))
        {
            sysMessage('暂不提供手工转帐记录导出', 1);
        }
        
        $userIds = array();
        foreach ($withDraworders AS $v)
        {
            $userIds[] = $v['userid'];
        }
        
        $users = $oUser->getUsersById($userIds);
        $str = '';
        foreach($withDraworders as $v)
        {
            $str .= '"'.(isset($users[$v['userid']]) ? $users[$v['userid']]: "unknown").'"'.",".
                '\''.$v['bankcard'].",".
                '"'.$v['transfer_out'].'"'.",".
                '"'.$v['fee'].'"'.",".
                '"'.$v['paydate'].'"'."\n";
        }
        $str = mb_convert_encoding($str, "gbk", "utf-8");

        $filename = date('Ymdhis');
        header("Content-Disposition: attachment; filename=".$filename.".csv");
        header("Content-Type:APPLICATION/OCTET-STREAM");
       
        echo $str;
    }
}
?>