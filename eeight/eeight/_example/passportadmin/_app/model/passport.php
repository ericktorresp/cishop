<?php
/**
 * 文件 : /_app/model/passport.php
 * 功能 : 模型 - PASSPORT 通用功能模型
 * 
 * @author	    Tom
 * @version    1.1.0
 * @package    passportadmin
 * @since      2009-06-15
 */

class model_passport extends basemodel 
{
	/**
	 * 构造函数
	 * @return	void
	 * @author Tom 090511
	 */
	function __construct( $aDBO=array() )
	{
		parent::__construct( $aDBO );
	}



	/**
	 * 获取全部唯一的 控制器名
	 * @author Tom 090511
	 * @return mix
	 */
	public function getDistintController( $bReturnArray = TRUE, $sSelected = '' )
	{
	    $aTmpArray = $this->oDB->getAll("SELECT DISTINCT `controller` FROM `adminmenu` WHERE `controller` != '' ");
	    $aReturn = '';
	    if( $bReturnArray == TRUE )
	    {
            foreach( $aTmpArray as $v )
    	    {
    	        $aReturn[] = $v['controller'];
    	    }
            return $aReturn;
	    }
	    else
	    {
	        foreach( $aTmpArray as $v )
	        {
	            $sSel = $sSelected==$v['controller'] ? 'SELECTED' : '';
	            $aReturn .= "<OPTION $sSel value=\"".$v['controller']."\">".$v['controller']."</OPTION>";
	        }
	        return $aReturn;
	    }
	}



	/**
	 * 获取全部唯一的 行为器名
	 * @author Tom 090511
	 * @return mix
	 */
	public function getDistintActioner( $bReturnArray = TRUE, $sSelected = '' )
	{
	    $aTmpArray = $this->oDB->getAll("SELECT DISTINCT `actioner` FROM `adminmenu` WHERE `actioner` != '' ");
	    $aReturn = '';
	    if( $bReturnArray == TRUE )
	    {
            foreach( $aTmpArray as $v )
    	    {
    	        $aReturn[] = $v['actioner'];
    	    }
            return $aReturn;
	    }
	    else
	    {
	        foreach( $aTmpArray as $v )
	        {
	            $sSel = $sSelected==$v['actioner'] ? 'SELECTED' : '';
	            $aReturn .= "<OPTION $sSel value=\"".$v['actioner']."\">".$v['actioner']."</OPTION>";
	        }
	        return $aReturn;
	    }
	}



	/**
	 * 获取全部唯一的 管理员登陆名
	 * @author Tom 090511
	 * @return mix
	 */
	public function getDistintAdminName( $bReturnArray = TRUE, $sSelected = '' )
	{
	    $aTmpArray = $this->oDB->getAll("SELECT DISTINCT adminid,`adminname` FROM `adminuser` WHERE `adminname` != '' ");
	    $aReturn = '';
	    if( $bReturnArray == TRUE )
	    {
            foreach( $aTmpArray as $k => $v )
    	    {
    	        $aReturn[$k] = $v['adminid'];
    	        $aReturn[$k] = $v['adminname'];
    	    }
            return $aReturn;
	    }
	    else
	    {
	        foreach( $aTmpArray as $k => $v )
	        {
	            $sSel = $sSelected==$v['adminid'] ? 'selected' : '';
	            $aReturn .= "<OPTION $sSel value=\"".$v['adminid']."\">".$v['adminname']."</OPTION>";
	        }
	        return $aReturn;
	    }
	}



	/**
	 * 获取全部唯一的总代名和ID
	 * @author Tom 090511
	 * @return mix
	 */
	public function getTopProxyName( $bReturnArray = TRUE, $sSelected = '', $iAdminId = 0 )
	{
	    if( $iAdminId > 0 )
	    { // 只获取销售管理员对应的总代
	        $aTmpArray = $this->oDB->getAll("SELECT b.`userid`,b.`username` FROM adminproxy a LEFT JOIN `usertree` b ".
	        	" ON a.`topproxyid` = b.`userid` WHERE b.`isdeleted` = 0 AND b.`parentid`=0 AND a.`adminid`='$iAdminId' ORDER BY `username`");
	    }
	    else 
	    { // 取所有用户数据
	        $aTmpArray = $this->oDB->getAll("SELECT `userid`,`username` FROM `usertree` WHERE `isdeleted` = 0 AND `parentid`=0 ORDER BY `username`");
	    }
	    $aReturn = '';
	    if( $bReturnArray == TRUE )
	    {
            foreach( $aTmpArray as $k => $v )
    	    {
    	        $aReturn[$k] = $v['userid'];
    	        $aReturn[$k] = $v['username'];
    	    }
            return $aReturn;
	    }
	    else
	    {
	        foreach( $aTmpArray as $k => $v )
	        {
	            $sSel = $sSelected==$v['userid'] ? 'SELECTED' : '';
	            $aReturn .= "<OPTION $sSel VALUE=\"".$v['userid']."\">".$v['username']."</OPTION>";
	        }
	        return $aReturn;
	    }
	}



    /**
     * 取得程序版本号. 用于打印
     * @author Tom 090511
     * @param  string $sFormat
     * @return string
     */
    public function getVersion( $sFormat = 'all' )
    {
        $aVerion = @explode( ',', PRJ_VERSION );
        foreach( $aVerion as $k => $v )
        {
            $arr[$k] = trim($v);
        }
        if( $sFormat == 'all' )
        {
             return 'v' . $arr[0] . ' Build(r' . $arr[1] . ') ' . $arr[2];
        }
        if( $sFormat == 'ver' )
        {
            return $arr[0];
        }
        if( $sFormat == 'svn' )
        {
            return $arr[1];
        }
        return '';
    }
}
?>