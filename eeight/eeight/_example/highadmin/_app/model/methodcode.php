<?php
/**
 * 数据模型: 生成玩法分类号码
 * 
 * @author     mark
 * @version    1.0.0
 * @package    highadmin
 */

class model_methodcode extends basemodel
{
    /**
     * 构造函数
     *
     * @param array $aDBO
     */
    function __construct( $aDBO = array())
    {
        parent::__construct( $aDBO );
    }
    
    
    /**
     * 生成玩法分类号码
     *
     */
    public function createMethodCode()
    {
        /* @var $oMethod model_method */
        $oMethod = A::singleton("model_method");
        $aMethod = $oMethod->methodGetList('a.`methodid`,a.`methodname`,a.`code`,a.`jscode`,b.`cnname`,b.`lotterytype`', 'a.`pid` != 0');
        //生成用于php的号码展开码
        $sPhpCodeFileContent = "<?php\n\$_METHODS = array(//玩法ID和内容对应关系[用于号码展开]\n";
        $aJsCode =  array();
        foreach ($aMethod as $aData )
        {
            $sPhpCodeFileContent .= "    ".$aData['methodid']."=>'".$aData['code']."',//";
            $sPhpCodeFileContent .= $aData['cnname'].":".$aData['methodname']."\n";
            $aJsCode[$aData['lotterytype']][] = $aData;
        }
        $sPhpCodeFileContent .= ");";
        file_put_contents(PDIR_HIGH_GAME.DS."_tmp".DS."static_caches".DS."methods.php",$sPhpCodeFileContent);
        //生成用于js的号码展开码
        foreach ($aJsCode as $iLotteryType=>$aLottery)
        {
            $sJCodeFileContent = "var methods = {//玩法ID和内容对应关系[用于号码展开]\n";
            foreach ($aLottery as $aMethodData)
            {
                $sJCodeFileContent .= "    ".$aMethodData['methodid'].":'".$aMethodData['jscode']."',//";
                $sJCodeFileContent .= $aMethodData['cnname'].":".$aMethodData['methodname']."\n";
            }
            $sJCodeFileContent = substr($sJCodeFileContent,0,strlen($sJCodeFileContent)-strlen($aMethodData['cnname'])-strlen($aMethodData['methodname'])-5);
            $sJCodeFileContent .= "//".$aMethodData['cnname'].":".$aMethodData['methodname']."\n";
            $sJCodeFileContent .= "};";
            file_put_contents(PDIR_HIGH_GAME.DS."js".DS."new_higame".DS."lottery".DS."methods.".$iLotteryType.".js",$sJCodeFileContent);
        }
    }
}
?>