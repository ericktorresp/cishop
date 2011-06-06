<?php
/**
 * astats 统计图类
 * 路径: /library/includes/class/astats.php
 * 实现功能: 
 *    根据数据表现形式, 输出 XML 形式数据文档
 *    中文数据需用 iconv 转码    
 *
 * TODO: 未完成的统计图
 *   统计概况- 饼图+曲线图
 *   时段分析- 今日统计, 竖向柱状图+同期对比
 *   来路分析- 来路域名 饼图
 *   地区分布- 中国地图
 *   插件安装- 横向柱状图
 * 
 * 使用范例: 
 * 		$oXml = new astats();
 *	    $oXml->addLabels( array( '0:00', '1:00', '2:00', '3:00', '4:00' ) );
 * 	    $oXml->addData( array('tooltext1'=>1,'tooltext2'=>2,'tooltext3'=>3,'tooltext4'=>4,'tooltext5'=>5,'tooltext6'=>6), 'line1' );
 * 	    $oXml->addData( array('tooltext'=>11,23,35,46,57,68), 'line2', 'FF0000');
 * 	    $oXml->addData( array(19,22,36,84,53,96), 'line3', '3BD12E');
 * 	    $oXml->display();
 * 
 * @author	    Tom   090523 06:28
 * @version    1.1.0
 * @package    core
 */

class astats
{
    private $aChart  = array(); 
    private $aLabels = array();  // 标签数组, 共多少个数据节点 [位于横坐标]
    private $aDatas  = array();  // 数据数组, 2维 $aDatas[0] =>
                                 // array (
                                 //        'seriesname' => 'newData',        // 线条名
                                 //        'anchorbordercolor' => '0033CC',  // 线条节点圈颜色
                                 //        'color'      => '0033CC'          // 线条颜色
                                 //        datas = array( '[tooltext]'=> value )
                                 // )

    public function __construct()
    {
        // <chart showFCMenuItem='0' ... >
        $this->inits();
        //print_rr($this->aChart);exit;
    }


    /**
     * 增加新标签
     * @param array $aArr  array( '0:00', '1:00', '2:00', '3:00', '4:00' );
     * @author Tom 090523
     */
    public function addLabels( $aArr = array() )
    {
        $this->aLabels = $aArr;
    }


    /**
     * 输出 XML 结果
     * @author Tom 090523
     */
    public function display()
    {
        // step 01, 解析 $aChart <chart ...> </chart>
        $sCharts = '';
        foreach( $this->aChart as $k => $v )
        {
            $sCharts .= "$k='$v' ";
        }
        $out = "<chart $sCharts>\n";
        
        // step 02, 解析标签 labels 
        if( !is_array($this->aLabels) || empty($this->aLabels) )
        {
            A::halt('From Class.astats : Error array aLabels');
        }
        $sLabels = "<categories>\n";
        foreach( $this->aLabels as $v )
        {
            $sLabels .= "<category label='".h(@iconv( 'UTF-8', 'GB2312', $v) )."' />\n";
        }
        $sLabels .= "</categories>\n\n";
        $out .= $sLabels;
        unset($sLabels, $sLabels);
        
        // step 03, 解析数据内容
        //print_rr( $this->aDatas );exit;
        $sDatas = '';
        if( is_array($this->aDatas) && !empty($this->aDatas) )
        {
            foreach( $this->aDatas as $aDatas )
            {
                //print_rr($aDatas);exit;
                $sDatas .= '<dataset seriesName=\''.$aDatas['seriesname']
                        . '\' color=\''.$aDatas['color']
                		. '\' anchorBorderColor=\''.$aDatas['anchorbordercolor'].'\'>'."\n";
                if( is_array($aDatas['datas']) && !empty($aDatas['datas']) )
                {
                    //print_rr($aDatas['datas']);
                    foreach( $aDatas['datas'] AS $k => $v )
                    {
                        if( !is_numeric($k) )
                        { // 数组键值不为数字时, 则判断为 tooltext, 鼠标浮动说明
                            $sDatas .= '<set value=\''.$v.'\' tooltext=\''.$k.'\' />';
                        }
                        else 
                        {
                            $sDatas .= '<set value=\''.$v.'\' />';
                        }
                        $sDatas .= "\n";
                    }
                }
                $sDatas .= "</dataset>\n\n";
            }
        }
        $out .= $sDatas;
        
        $out .= "\n<styles><definition><style name='myLegendFont' type='font' size='12' /></definition>".
        		"<application><apply toObject='Legend' styles='myLegendFont' /></application></styles>";
        $out .= "</chart>";
        //echo "<pre>";die( h($out) );
        echo $out;
        exit;
    }


    /**
     * 增加新数据项
     *
     * @param array  $aData   ['tooltext'] => value
     * @param string $sName
     * @param string $sLineColor   FF0000 | 3BD12E | 
     * @param string $sBorderColor
     * @author Tom 090523
     */
    public function addData( $aData, $sName='NewData', $sLineColor='0033CC', $sBorderColor='0033CC' )
    {
        if( !is_array($aData) || empty($aData) )
        {
            A::halt('From Class.astats : Error array $aData');
        }
        if( $sBorderColor == '0033CC' && $sLineColor != '0033CC' )
        {
            $sBorderColor = $sLineColor;
        }
        $aTmpArr = array(); // 临时数组声明
        $aTmpArr['seriesname']  =  h(@iconv( 'UTF-8', 'GB2312', $sName));   // 线条名字 <dataset seriesname="xxx" ..
        $aTmpArr['color']       =  $sLineColor;         // 线条颜色
        $aTmpArr['anchorbordercolor']=$sBorderColor;    // 线条圆圈颜色
        $aTmpArr['datas'] = $aData;
        $this->aDatas[] = $aTmpArr;
        unset($aTmpArr);
    }


    /**
     * 设置私有属性 $aChart 的值
     * @param string $sKey
     * @param string $sVal
     * @author Tom 090523
     */
    public function setChart( $sKey, $sVal )
    {
        if( isset($this->aChart[$sKey]) )
        {
            $this->aChart[$sKey] = $sVal;
        }
        //print_rr($this->aChart);
    }


    /**
     * 初始化参数数组
     * @author Tom 090523
     */
    public function inits()
    {
        $this->aChart = array
        (
            'showfcmenuitem' => 0,                // 未知
            'linethickness'  => '2',              // SWF 曲线宽度
            'showvalues'     => '0',              // 是否在SWF中直接显示变量值(默认浮动显示)
            'anchorradius'   => '4',              // 数据节点圆圈半径
            'divlinealpha'   => '20',             // 背景方格的透明度(百分比)
            'divlinecolor'   => 'CC3300',         // 背景方格线颜色
            'divlineisdashed'=> '1',              // 背景方格的边线是否为虚线
            'showalternatehgridcolor' => '1',     // 背景方格(行)是否颜色交替显示
            'alternatehgridalpha' => '5',         // (行)交替交替颜色的透明度
            'alternatehgridcolor' => 'CC3300',    // 交替行颜色值
            'shadowalpha' => '40',                // 无效? 
            'labelstep' => '1',                   // [横] 坐标值显示时的跳跃步伐
            'numvdivlines' => '25',               // 背景方格显示的数量, 从0开始计数, 25意味着26格
            'showalternatevgridcolor' => '1',     // 背景方格(列)是否颜色交替显示
            'chartsshowshadow' => '1',            // 无效?
            'chartrightmargin' => '20',           // SWF 图表, 与右的边距
            'charttopmargin' => '15',             // SWF 图表, 与上的边距
            'chartleftmargin' => '0',             // SWF 图表, 与左的边距
            'chartbottommargin' => '3',           // SWF 图表, 与下的边距
            'bgcolor' => 'FFFFFF',                // 外圈(非绘图区) 的背景色
            'canvasborderthickness' => '1',       // 外圈边框线宽度
            'showborder' => '0',                  // 无效?
            'legendborderalpha' => '0',           // 图例区边框透明度
            'bgangle' => '360',                   // 无效?
            'showlegend' => '1',                  // 是否显示图例区
            'bordercolor' => 'DEF3F3',            // 无效?
            'tooltipbordercolor' => 'cccc99',     // 无效
            'canvaspadding' => '0',               // 曲线图, 离两端的距离
            'tooltipbgcolor' => 'ffffcc',         // 无效
            'legendShadow' => '0',                // 图例区是否显示阴影
            'baseFontSize' => '12',               // 数据节点上, 文字大小
            'canvasBorderAlpha' => '20',          // 外边框透明度
            'outCnvbaseFontSize' => '10',         // 周边文字大小
            'outCnvbaseFontColor' => '000000',    // 周边文字颜色
            'numberScaleValue' => '10000,1,1,1000',  // 数字格式
            'formatNumberScale' => '1',           // 是否显示大写中文,例: 2.6千万
            'palette' => '2',                     // 
            'numberScaleUnit' => ' , ,万,千万',
        	'lineColor' => 'AFD8F8'               // 无效?
        );
    }
}
?>