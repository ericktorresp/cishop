<?php
/**
 * astatspie 统计图类 (饼图)
 * 路径: /library/includes/class/astatspie.php
 * 实现功能: 
 *    根据数据表现形式, 输出 XML 形式数据文档
 * 
 * 使用范例:
 * 		$oXml = new astatspie();
 * 	    $oXml->addData( '最喜欢时时乐 SSL', 35,  '0D8ECF', 'pullout'=>1 );
 *      $oXml->addData( '最喜欢时时彩 SSC', 155, '0D8ECF' );
 *      $oXml->addData( '最喜欢排列三 P3',  98 );
 *      $oXml->addData( array( 'title'=>'统计数据10', 'color'=>'0x54F034') );
 * 	    $oXml->display();
 * 
 * @author	    Tom     090523 06:28
 * @version    1.1.0
 * @package    core
 */

class astatspie
{
    private $aDatas  = array();  // 数据数组, 2维 $aDatas[0] =>
                                 // array (
                                 //        'title'   => '答案1',   // 线条名
                                 //        'pullout' => TRUE,      // 线条节点圈颜色
                                 //        'color'   => '0033CC'   // 线条颜色
                                 // )
    private $aColors = array();  // 默认饼图颜色序列



    public function __construct()
    {
        // 初始化默认颜色数组
        $this->aColors = array(
                '0D8ECF',
                '04D215',
                'B0DE09',
                'F8FF01',
                'FF9E01',
                'FF6600',
                'FF1F11',
                '814EE6',
                'F234B0',
                '54F034',
                '0D8ECF',
                '04D215',
                'B0DE09',
                'F8FF01',
                'FF9E01',
                'FF6600',
                'FF1F11',
                '814EE6',
                'F234B0',
                '54F034',
            );
    }



    /**
     * 增加新数据项
     *    $oXml->addData( '最喜欢时时乐 SSL', 35,  '0D8ECF', 'pullout'=>1 );
     * @param string $sTitle     饼图区域标题   [必须]
     * @param string $sValue     饼图区域值     [必须]
     * @param string $sPullOut   是否默认选中   [可选]
     * @param string $sColor     颜色值         [可选]
     */
    public function addData( $sTitle='', $sValue='', $sPullOut='false', $sColor='' )
    {
        $aTmpArr = array();                // 临时数组声明
        $aTmpArr['title']    =  @iconv( 'GB2312', 'UTF-8', trim($sTitle));
        $aTmpArr['value']    =  $sValue;
        $aTmpArr['pullout']  =  $sPullOut == 'false' ?  'false' : 'true'; // 是否已选中
        if( $sColor != '' )
        {
            $aTmpArr['color']=  '0x'. $sColor;
        }
        else 
        {
            $aTmpArr['color']= isset($this->aColors[ count($this->aDatas) ]) ? 
                        ('0x'. $this->aColors[ count($this->aDatas) ]) : '0x0D8ECF' ; 
        }
        $this->aDatas[]      =  $aTmpArr;
        unset($aTmpArr);
    }



    /**
     * 输出统计结果
     * @author Tom 090523
     */
    public function display()
    {
        $sOut    = '<?xml version="1.0" encoding="UTF-8" ?><pie>';
        if( !is_array($this->aDatas) || empty($this->aDatas) )
        {
            A::halt('From Class.astatspie : Error array aDatas');
        }

        foreach( $this->aDatas as $v )
        {
            $sOut .= "<slice title='". $v['title'] ."' color='". $v['color'] .
            			"' pull_out='". $v['pullout'] ."'>". $v['value'] ."</slice>";
        }
        $sOut .= '</pie>';
        echo $sOut;
        exit;
    }





    /**
     * 初始化参数数组
     * TODO: 与类进行封装, 允许设置饼图的参数
     * @author Tom 090622
     */
    public function getSettingsFile()
    {
        $sTmpMessage = <<< EOT
<?xml version="1.0" encoding="UTF-8" ?>
<settings>
 <data_type>xml</data_type>
 <pie>
   <x>450</x>
   <y>120</y>
   <radius>100</radius>
   <inner_radius>30</inner_radius>
   <height>15</height>
   <angle>35</angle>
   <alpha>90</alpha>
 </pie>
 <animation>
   <start_time>1</start_time>
   <start_radius>100%</start_radius>
   <start_effect>strong</start_effect>
   <pull_out_time>1</pull_out_time>
 </animation>
 <data_labels>
   <radius>20</radius>
   <text_size>12</text_size>
   <text_color>#000000</text_color>
   <line_color>#000000</line_color>
   <show><![CDATA[{percents}%]]></show>
   <hide_labels_percent>10</hide_labels_percent>
 </data_labels>
 <decimals_separator>.</decimals_separator>
 <legend>
   <enabled>true</enabled>
   <x>20</x>
   <y>20</y>
   <width>350</width>
   <max_columns>1</max_columns>
   <color>#FFFFFF</color>
   <text_color>#000000</text_color>
   <text_size>12</text_size>
   <spacing>2</spacing>
   <margins></margins>
   <align>left</align>
   <key>
      <size>16</size>
      <border_color></border_color>
   </key>
   <values>
      <enabled>true</enabled>
      <width></width>
      <text><![CDATA[]]></text>
   </values>
 </legend>
</settings>
EOT;
    echo $sTmpMessage;exit;
    }
}
?>