<?php
/**
 * Page 分页类 (参数大小写敏感)
 *  - 默认使用 URL 变量 p 和 pn 表示当前页和每页数据数量
 *  - 默认每页25条记录
 * 
 * 依赖全局:
 *    $_REQUEST['p'] 和 $_REQUEST['pn']  分别表示当前页数,与每页多少条记录
 * 
 * 
 * @author   Tom   090915
 * @version  1.2.0
 * @package  Core
 */
class pages
{
    private $sPageName      = "p";      // page URL 中使用的分页变量名, 例: xxx.php?p=2 中的字符 p
    private $sPerPageName   = 'pn';     // URL中,使用的每页显示的数据条目变量名, 例 xxx.php?p=2&pn=25 中的字符 pn

    /* 分页参数 */
    private $iTotalCounts   = 0;        // 总记录个数,例: 101 条
    private $iTotalPages    = 0;        // 总分页的数量  ceil(总数/每页数)
    private $iCurrentPage   = 1;        // 当前页号
    private $iPerPageCount  = 25;       // 每页显示数据条数,例: 25条
    private $iPageBarNum    = 10;       // 仿GOOGLE 分页条的个数, 例: 10
    private $sBaseUrl       = "";       // sUrl 地址头, 如果存在 p,pn 则会自动滤掉
                                        // 例:  http://www.xx.com/index.php?a=1&b=2&p=300&pn=15&d=1
                                        // 则:  此处存放 :
                                        //      http://www.xx.com/index.php?a=1&b=2&d=1
    private $sQueryString   = '';       // 用于存放当前页, 过滤p&pn 后的 QueryString

    /* 显示参数 */
    private $sNextPage      = '下页';   // 下一页
    private $sPrePage       = '上页';   // 上一页
    private $sFirstPage     = '首页';   // 首页
    private $sLastPage      = '尾页';   // 尾页

    /**
     * 构造函数
     * 
     * @param int $iTotalCounts       共计数据数量,   例: 100 (条)
     * @param int $iPerPageCount      每页显示记录数, 例: 25  (条)
     * @param int $iPageBarNum        仿GOOGLE分页条的个数
     * @param string $sPageName       代表 '当前页号' 的变量名, 默认 p
     * @param string $sPerPageName    代表 '当前每页显示数据条数' 的变量名, 默认 pn
     * @param string $sUrl            当前页完整 URL
     * @author Tom 090511
     */
    public function __construct( $iTotalCounts=0, $iPerPageCount=25, $iPageBarNum=10,
                         $sPageName='p', $sPerPageName='pn', $sUrl='' )
    {
    	$this->initPageVariables( $sPageName,$sPerPageName ); // 初始化当前页数变量名,  http://xx.com/?p=1
        $this->iTotalCounts  = is_numeric($iTotalCounts) && $iTotalCounts>=0 ? intval($iTotalCounts) : 0;
        $this->iPerPageCount = is_numeric($iPerPageCount) && $iPerPageCount>0 ? intval($iPerPageCount) : $this->iPerPageCount;
        $this->iPageBarNum    = is_numeric($iPageBarNum) && $iPageBarNum>0 ? intval($iPageBarNum) : $this->iPageBarNum;

        $this->sBaseUrl      = !empty($sUrl) ? $sUrl : $this->getCurrentUrlBase();//getCurrentURI(TRUE);
        $this->sQueryString  = $this->getCurrentUrlBase(TRUE);

        // 计算总页数
        $this->iTotalPages   = $this->iPerPageCount == 0 ? 0 : ceil($this->iTotalCounts/$this->iPerPageCount);
        if( $this->iCurrentPage > $this->iTotalPages )
        {
            $this->iCurrentPage = $this->iTotalPages;
        }
    }

    public function isLastPage()
    {
        return ($this->iTotalPages==$this->iCurrentPage) ? TRUE : FALSE;
    }
    
    public function getTotalPage()
    {
        return $this->iTotalPages;
    }


    /**
     * 获取基础 URL, 过滤掉分页变量
     * 例: 
     *    当前URL :  http://www.a.com/?a=1&b=2&p=99&pn=100&c=3
     *    过滤后  :  http://www.a.com/?a=1&b=2&c=3
     * 
     * @param bool $bDisplayQueryStringOnly 只返回QueryString而不是整体返回
     * @author Tom 090511
     */
    private function getCurrentUrlBase( $bDisplayQueryStringOnly = FALSE )
    {
        if( empty($_SERVER['QUERY_STRING']) )
        { // 在 URL 参数信息(QUERY_STRING)为空时, 直接返回当前 URL+QUERY_STRING
            if( $bDisplayQueryStringOnly == TRUE )
            {
                return '';
            }
            else 
            {
                return getCurrentURI(TRUE);
            }
        }
        else
        {
            $sQueryString = '';
            foreach( $_GET as $k => $v )
            {
                if( $k==$this->sPageName || $k==$this->sPerPageName )
                { // 过滤掉URI中的分页信息
                    continue;
                }
                //echo "\$_GET[$k] = $v <br/>";
                if( $sQueryString == '' ) //
                {
                    $sQueryString .= '?' . $k . '=' . $v; 
                }
                else 
                {
                    $sQueryString .= '&' . $k . '=' . $v; 
                }
            }
            if( $bDisplayQueryStringOnly == TRUE )
            {
                return $sQueryString;
            }
            else 
            {
                $sCurrentBaseURI = detect_uri_base();
                return $sCurrentBaseURI.$sQueryString;
            }
        }
    }



    /**
     * 变量初始化
     *   - 当前页数变量名         [ 默认 p  ]
     *   - 每页数据条数变量名     [ 默认 pn ]
     *   - 初始化当前页数         [ 默认 1  ]
     *   - 初始化每页记录条数     [ 默认 25 ]
     * @author Tom 090511
     */
    private function initPageVariables( $sPageName='p', $sPerPageName='pn' )
    {
        // 初始化变量名
        $this->sPageName      = $sPageName;
        $this->sPerPageName   = $sPerPageName;
        // $this->iCurrentPage = $_REQUEST['p'],  默认当前第1页
        $this->iCurrentPage   = !empty($_REQUEST[$this->sPageName]) && is_numeric($_REQUEST[$this->sPageName]) ?
                                intval($_REQUEST[$this->sPageName]) : $this->iCurrentPage;
        // $this->iCurrentPage = $_REQUEST['pn'], 默认每页 25 条数据
        $this->iPerPageCount  =  !empty($_REQUEST[$this->sPerPageName]) && is_numeric($_REQUEST[$this->sPerPageName]) ?
                                intval($_REQUEST[$this->sPerPageName]) : $this->iPerPageCount;
    }



    /**
     * 设定类中指定变量名的值, 如果改变量不属于这个类, 则程序中断
     * @param string $sKey
     * @param string $sVal
     * @author Tom 090511
     */
    public function set( $sKey, $sVal )
    {
        if( isset($this->$sKey) )
        {
           $this->$sKey = $sVal;
        }
        else
        {
            $this->halt("set( $sKey, $sVal ): sKey Error");
        }
    }

    public function get( $sKey )
    {
        if( isset($this->$sKey) )
        {
           return $this->$sKey;
        }
        else
        {
            $this->halt("get( $sKey ): sKey Error");
        }
        return FALSE;
    }



    /**
     * 获取显示 "上一页" 的代码
     *
     * @param string $sHtmlCssId
     * @return string
     * @author Tom 090511
     */
    function getPrePageMsg( $sHtmlCssId = '' )
    {
        if( $this->iCurrentPage > 1 )
        {
            return $this->_getLink( $this->_getPageUrl( $this->iCurrentPage-1 ),$this->sPrePage, $sHtmlCssId );
        }
        else 
        { // 只显示 '上一页' 的文字,不加入连接
            //return $this->sPrePage;
            return '';
        }
    }


    /**
     * 获取显示 "下一页" 的代码
     * 
     * @param string $sHtmlCssId
     * @return string
     * @author Tom 090511
     */
    function getNextPageMsg( $sHtmlCssId = '' )
    {
        if( $this->iCurrentPage < $this->iTotalPages )
        {
            return $this->_getLink($this->_getPageUrl($this->iCurrentPage+1),$this->sNextPage,$sHtmlCssId);
        }
        else 
        { // 只显示 '下一页' 的文字,不加入连接
            //return $this->sNextPage;
            return '';
        }
    }
    

    
    /**
     * 获取显示 "首页" 的代码
     * @return string
     * @author Tom 090511
     */
    function getFirstPageMsg( $sHtmlCssId='' )
    {
        if( $this->iCurrentPage == 1 || $this->iCurrentPage==0 )
        {
            //return $this->sFirstPage; // 已经为首页,则隐藏按钮文字
            return '';
        }
        else 
        { // 只显示 '首页' 的文字,不加入连接
            return $this->_getLink($this->_getPageUrl(1),$this->sFirstPage,$sHtmlCssId);
        }
    }


    /**
     * 获取显示 "尾页" 的代码
     * @return string
     * @author Tom 090511
     */
    function getLastPageMsg($sHtmlCssId='')
    {
        if($this->iCurrentPage==$this->iTotalPages)
        {
            //return $this->sLastPage;// 已经为尾页,则隐藏按钮文字
            return '';
        }
        else 
        { // 只显示 '尾页' 的文字,不加入连接
            return $this->_getLink($this->_getPageUrl($this->iTotalPages),$this->sLastPage,$sHtmlCssId);
        }
    }


    /**
     * 获取链接地址
     * @author Tom 090511
     */
    function _getLink( $sUrl, $sTextMessage, $sHtmlCssId='' )
    {
        $sHtmlCssId = (empty($sHtmlCssId)) ? '' : 'ID="'.$sHtmlCssId.'"';
        return '<A '.$sHtmlCssId.' HREF="'.$sUrl.'">' . $sTextMessage . '</A>';
    }


    /**
     * 创建仿 GOOGLE 的分页条
     *
     * @param string $sHtmlCssId         分页 CSS Class Name
     * @param string $iCurrentHtmlCssName  当前被激活的 CSS Class Name
     * @return string
     * @author Tom 090511
     */
    function getPageBar( $sHtmlCssId='' )
    {
        $iPlus = ceil( $this->iPageBarNum / 2 );
        if( ($this->iPageBarNum - $iPlus + $this->iCurrentPage) > $this->iTotalPages )
        {
            $iPlus = ( $this->iPageBarNum - $this->iTotalPages + $this->iCurrentPage );
        }
        $begin  = $this->iCurrentPage - $iPlus + 1;
        $begin  = ($begin>=1) ? $begin : 1;
        $return = '';
        for( $i=$begin; $i<($begin+$this->iPageBarNum); $i++ )
        {
            if( $i <= $this->iTotalPages )
            {
                if( $i != $this->iCurrentPage )
                {
                    $return .= $this->_getLink($this->_getPageUrl($i),$i,$sHtmlCssId);
                }
                else
                { 
                    $return .= '<STRONG>'.$i.'</STRONG>';
                }
            }
            else
            {
                break;
            }
            $return.="\n";
        }
        unset($begin);
        return $return;
    }


    /**
     * 获取显示跳转按钮的代码
     * @return string
     * @author Tom 090511
     */
    function getHtmlSelectBox()
    {
        if( $this->iTotalPages <= 1 )
        {
            return '';
        }
        $return='<SELECT NAME="PAGES" onchange="window.location.href=\''.
                $this->sBaseUrl . 
                ( ( $this->sQueryString == '' ) ? '?' : '&' ) .
                $this->sPageName. '=\'+this.options[this.selectedIndex].value'.
                '+\''. '&'.$this->sPerPageName . '=' . $this->iPerPageCount .'\'">' ;
                
                ;
        for( $i=1; $i<=$this->iTotalPages; $i++ )
        {
            if( $i == $this->iCurrentPage )
            {
                $return .= '<OPTION VALUE="'.$i.'" SELECTED>'.$i.'</OPTION>';
            }
            else
            {
                $return .= '<OPTION VALUE="'.$i.'">'.$i.'</OPTION>';
            }
        }
        unset($i);
        $return.='</SELECT>';
        return '第'.$return.'页';
    }



	/**
     * 获取显示跳转按钮的代码
     * @return string
     * @author Tom 090511
     */
    function getHtmlInputBox()
    {
        $sInputName = "iGotoPage";
        $sToUrl     = $this->sBaseUrl . ( ( $this->sQueryString == '' ) ? '?' : '&' ) . $this->sPerPageName.'='.$this->iPerPageCount.'&' ;
        // @a = alert("'.$sToUrl . $this->sPageName. '="+iPage);
        $sHtml = '<SCRIPT LANGUAGE="JAVASCRIPT">'.
                'function keepKeyNum(obj,evt){var  k=window.event?evt.keyCode:evt.which; '.
                'if( k==13 ){ goPage(obj.value);return false; }} '.
                //'if ((k<=57) && (k>=48)) {return true;}else {return false;}}'.
                'function goPage( iPage ){if( !isNaN(parseInt(iPage)) ) '.
                '{window.location.href="'.$sToUrl . $this->sPageName. '="+iPage;}}</SCRIPT>';
        $return= $sHtml.'<INPUT onKeyPress="return keepKeyNum(this,event);" TYPE="TEXT" ID="'.$sInputName.'" NAME="'.$sInputName.'" size="6">';
        return '转至 ' .$return .
        	'页 <input type="button" onClick="javascript:goPage( document.getElementById(\''.
            $sInputName.'\').value );return false;" class="button" value="GO">';
    }


    /**
     * 为指定的页面返回地址值
     * @param int $iPageNo 第几页
     * @return string $sUrl
     * @author Tom 090511
     */
    function _getPageUrl( $iPageNo = 1 )
    {
        // 如果参数为空, 则直接加上 ?p=1&pn=15
        $sUrl = '';
        $sFlag = '';
        if( $this->sQueryString == '' )
        {
            $sFlag = '?';
        }
        else
        {
            $sFlag = '&';
        }
        $sUrl .= $sFlag. $this->sPageName.'='.$iPageNo.'&'.$this->sPerPageName.'='.$this->iPerPageCount;
        return $this->sBaseUrl . $sUrl;
    }



    /**
     * 出错处理方式
     * @author Tom 090511
     */
    function halt($haltmsg)
    {
        die('From class.pages : '.$haltmsg);
    }


	/**
     * 控制分页显示风格 (可增加相应的风格)
     * @param int $mode
     * @return string
     * @author Tom 090511
     */
    function show( $iMode=1 )
    {
        if( $iMode == 1 && $this->iTotalPages > 1000 )
        { // 分页数太多的情况, 强制禁止使用下拉框
            $iMode = 2;
        }
        switch( $iMode )
        {
            case '1' :
                $str = '总计 ' .$this->iTotalCounts.'个记录,  分为 '.$this->iTotalPages.' 页'.
                        ', 当前第 '. $this->iCurrentPage .' 页<SPAN ID="tPages"> '.
                        $this->getFirstPageMsg(). ' '.$this->getPrePageMsg().
                        $this->getPageBar().$this->getNextPageMsg(). ' '. $this->getLastPageMsg().
                        "</SPAN>\n". $this->getHtmlSelectBox();
                return $str;
                break;

            case '2':
                $str = '总计 ' .$this->iTotalCounts. ' 个记录,  分为 '.$this->iTotalPages.' 页'.
                        ', 当前第 '. $this->iCurrentPage .' 页<SPAN ID="tPages"> '.
                        $this->getFirstPageMsg(). ' '.$this->getPrePageMsg(). ' '.
                        $this->getPageBar().$this->getNextPageMsg(). ' '. $this->getLastPageMsg().
                        "</SPAN>\n". $this->getHtmlInputBox() ;
                return $str;
                break;

            case '100':
                return $this->getFirstPageMsg().$this->getPrePageMsg().'[第'.$this->iCurrentPage.'页]'.$this->getNextPageMsg().$this->getLastPageMsg().'第'.$this->getHtmlSelectBox().'页';
                break;
            case '101':
                return $this->getFirstPageMsg().$this->getPrePageMsg().$this->getNextPageMsg().$this->getLastPageMsg();
                break;
            case '102':
                return $this->getPrePageMsg().$this->getPageBar().$this->getNextPageMsg();
                break;
            case '103':
                //
                break;
        }
        return '';
    }
}
?>