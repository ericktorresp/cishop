<?php
    /**
     * 检测不存在用户
     */
    @ini_set( "display_errors", TRUE);
    error_reporting(E_ALL);
    set_time_limit(10000);
    // 1, 安全过滤 ----------------------------------------------------------
    if( !empty($_GET) || !empty($_POST) || !empty($_REQUEST) )
    {
        die('Error');  // 禁止网页 URL 形式调用
    }
    // 2, 初始化
    define('DONT_USE_APPLE_FRAME_MVC', TRUE);
    define('DONT_TRY_LOAD_SYSCONFIG_FILE', TRUE);
    require( realpath(dirname(__FILE__) . '/../') .DIRECTORY_SEPARATOR. 'index.php');
    $aLastResult = array();
    $aUsedUser = array();
    $aResultDetail = array();
    $oDb = new db($GLOBALS['aSysDbServer']['master']);
    $sSql = " SELECT `parenttree`,`parentid` FROM `usertree` WHERE `parentid` != 0";
    $aResult = $oDb->getAll($sSql);
    foreach ( $aResult as $aUser )
    {
        if( !empty($aUser['parenttree']) )
        {
            $aParentTree = explode(",",$aUser['parenttree']);
            $aParentTree[] = $aUser['parentid'];
            foreach ( $aParentTree as $iUserId )
            {
                if(!in_array($iUserId,$aUsedUser))
                {
                    $aUsedUser[] = $iUserId;
                    $sQueryUserSql = " SELECT `userid` FROM `usertree` WHERE `userid` = '" . $iUserId . "'";
                    $aTmpUser = $oDb->getOne($sQueryUserSql);
                    if( empty($aTmpUser) )
                    {
                        $sSql = " SELECT * FROM `usertree` WHERE `parentid` = '" . $iUserId."' 
                                                 OR `parenttree` = '" .$iUserId ."' 
                                                 OR 'parenttree' LIKE '" .$iUserId .",%' 
                                                 OR `parenttree` LIKE '%," .$iUserId ."' 
                                                 OR `parenttree` LIKE '%," .$iUserId .",%'";
                        $aDetail = $oDb->getAll($sSql);
                        foreach ( $aDetail as $aValue )
                        {
                            if(!key_exists($aValue['userid'],$aResultDetail))
                            {
                                $aResultDetail[$aValue['userid']] = array(
                                'userid'=>$aValue['userid'],
                                'parentid'=>$aValue['parentid'],
                                'parenttree' => $aValue['parenttree']
                                );
                            }
                        }
                        if(!in_array($iUserId,$aLastResult))
                        {
                            $aLastResult[] = $iUserId;
                        }
                    }
                }
                unset($aTmpUser);
            }
        }
    }
    echo '*****不存在的ID号****************';
    print_rr($aLastResult,false,false);
    echo '*****异常用户****************';
    print_rr($aResultDetail,false,false);
?>