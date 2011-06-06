<?php

/**
 * todo:比如早晨第一期都没开出，那不应该去抓取
 */

class model_drawsource extends basemodel
{
    static $fields = array('lotteryid', 'name', 'url', 'needlogin', 'loginname', 'loginpwd', 'refresh', 'interface', 'rank', 'enabled', 'date');

    function __construct( $aDBO=array() )
    {
        parent::__construct($aDBO);
    }

    /**
     * 得到单条
     * @param <int> $itemId
     * @return <array>
     * @author Rojer
     */
    public function getItem($id, $enabled = 1)
    {
        if ($id <= 0)
        {
            sysMessage("无效的参数", 1);
        }
        $sql = "SELECT * FROM `drawsource` WHERE id = '$id'";
        if ($enabled !== NULL)
        {
            $sql .= " AND enabled = '$enabled'";
        }
        $result = $this->oDB->getOne($sql . ' LIMIT 1');

        return $result;
    }

    /**
     * 得到列表
     * @param <type> $lotteryId
     * @author  Rojer
     */
    function getItems($lotteryId = 0, $enabled = 1)
    {
        $sql = "SELECT * FROM `drawsource` WHERE 1";
        if ($lotteryId > 0)
        {
            $sql .= " AND lotteryid = '$lotteryId'";
        }
        if ($enabled !== NULL)
        {
            $sql .= " AND enabled = '$enabled'";
        }
        $sql .= " ORDER BY lotteryid ASC, `rank` DESC";

        return $this->oDB->getAll($sql);
    }

    function getLastHistories($onlyError = false)
    {
        $sql = "SELECT lotteryid, max(id) AS id FROM drawhistory WHERE 1";
        if ($onlyError)
        {
            $sql .= " AND errno > 0";
        }
        $sql .= " GROUP BY lotteryid ORDER BY lotteryid ASC";
        $ids = array();
        foreach ($this->oDB->getAll($sql) as $v)
        {
            $ids[$v['lotteryid']] = $v['id'];
        }

        return self::getHistoriesById($ids);
    }

    function getHistories($lotteryId, $pageRecord = 0, $currentPage = 0)
    {
        if ($lotteryId <= 0)
        {
            sysMessage("无效的参数", 1);
        }
        $condition = " lotteryid = '$lotteryId'";

        $orderBy = " ORDER BY issue DESC, sourceid ASC";
        $sql = "SELECT count(distinct(issue)) FROM `drawhistory` WHERE lotteryid = '$lotteryId'";
        $resultNumbers = $this->oDB->getOne($sql);

        $result = $this->oDB->getPageResult( 'drawhistory', 'distinct(issue)', $condition, $pageRecord, $currentPage, $orderBy );
        $issues = array_keys(self::array_spec_key($result['results'], 'issue'));
        $sql = "SELECT * FROM `drawhistory` WHERE $condition" . " AND issue IN ('".implode("','", $issues). "') ORDER BY id DESC";
        $result['results'] = $this->oDB->getAll($sql);
        $result['affects'] = reset($resultNumbers);

        return $result;
    }

    function getHistoriesById($ids)
    {
        if (empty($ids))
        {
            return array();
        }
        
        $sql = "SELECT * FROM drawhistory WHERE id IN (".implode(',', $ids).")";
        
        return $this->oDB->getAll($sql);
    }

    /**
     * 添加奖期历史
     * @param <type> $lotteryid
     * @param <type> $issue
     * @param <type> $sourceid
     * @param <type> $number
     * @param <type> $retry
     * @param <type> $spent
     * @param <type> $date
     * @return <type>
     */
    public function addHistory($lotteryid, $issue, $sourceid, $rank, $errno, $number, $retry, $spent, $date)
    {
        $sql = "INSERT INTO drawhistory (lotteryid,issue,sourceid,rank,errno,number,retry,spent,date) VALUES(".
            "'$lotteryid', '$issue', '$sourceid', '$rank', '$errno', '$number', '$retry', '$spent', '$date')".
            " ON DUPLICATE KEY UPDATE retry=retry+1, errno='$errno', number='$number', date='$date'";
        $this->oDB->query($sql);

        return $this->oDB->ar();
    }

    /**
     * 插入记录
     * @param <integer> $lotteryId
     * @param <string> $url
     * @param <integer> $refresh
     * @param <integer> $interface
     * @param <integer> $rank
     * @return <type>
     * @authoer Rojer
     */
    public function addItem($lotteryId, $name, $url, $needlogin, $loginname, $loginpwd, $refresh, $interface, $rank = 0, $enabled = 1)
    {
        if (empty($lotteryId) || empty($url))
        {
            sysMessage("无效的参数", 1);
        }
        $data = array(
            'lotteryid' => $lotteryId,
            'name' => $name,
            'url' => $url,
            'needlogin' => $needlogin,
            'loginname' => $loginname,
            'loginpwd' => $loginpwd,
            'refresh' => $refresh,
            'interface' => $interface,
            'rank' => $rank,
            'enabled' => $enabled,
            'date' => date('Y-m-d H:i:s'),
        );
        $this->oDB->insert('drawsource', $data);
        return $this->oDB->ar();
    }

    public function updateItem($id, $data)
    {
        if (empty($id) || array_diff(array_keys($data), self::$fields))
        {
            sysMessage("无效的参数", 1);
        }
        if (!isset($data['date']))
        {
            $data['date'] = date('Y-m-d H:i:s');
        }

        return $this->oDB->update("drawsource", $data, "id=".intval($id));
    }

    public function deleteItem($id)
    {
        if (empty($id))
        {
            sysMessage("无效的参数", 1);
        }

        $sql = 'DELETE FROM drawsource WHERE id = '.intval($id);
        $this->oDB->query($sql);
        return $this->oDB->ar();
    }

    /**
     * 抓取号码，功能单一，只取指定彩种指定奖期，按设置一次轮一圈，直到取完，出错抛异常
     * @param <type> $lotteryId
     * @param <type> $issue 
     */
    public function fetchDrawNumber($lottery, $expectedDate, $expectedIssue, $debug = 0)
    {
        if (!$sources = self::getItems($lottery['lotteryid']))
        {
            throw new exception("No source to be fetched", 31);
        }

        $oConfig    = A::singleton("model_config");
        $config = $oConfig->getConfigs( array("least_score", "person_score") );
        $retry = $rank = 0;
        $result = array();
        do
        {
            foreach ($sources as $k => $v)
            {
                // $tmp = array('errno' => 0, 'issuestr' => NULL, 'issue' => $tmp['issue'], 'number' => $tmp['number'], 'time' => $tmp['time'])
                try
                {
                    echo "调试信息：源 {$v['name']}(id={$v['id']})\t(URL={$v['url']})";
                    $tmp = self::fetchFromURL($lottery, $v['url'], $expectedDate, $expectedIssue);
                    // 写抓取历史
                    if (!$debug)
                    {
                        self::addHistory($lottery['lotteryid'], $expectedIssue, $v['id'], $v['rank'], $tmp['errno'], isset($tmp['number']) ? $tmp['number'] : '', 0, isset($tmp['time']) ? $tmp['time'] : 0, date('Y-m-d H:i:s'));
                    }
                    
                    if ($tmp['errno'])
                    {
                        echo "没取到对应奖期`$expectedIssue`，可能因为源还没有更新"."\n"; // 返回错误消息是".$tmp['errstr']
                        if ($retry >= $lottery['retry'])
                        {
                            $fn = "/tmp/{$v['name']}_{$expectedIssue}_".date('YmdHis').".txt";
                            file_put_contents($fn, "\n\n".str_repeat('=', 50)."\n\n", FILE_APPEND);
                            file_put_contents($fn, $tmp['errstr'], FILE_APPEND);
                        }
                        continue;
                    }
                    $tmp['sourceid'] = $v['id'];
                    $result[] = $tmp;
                    $rank += $v['rank'];
                    unset($sources[$k]);
                    echo "\t号码 {$tmp['number']}, 时间 {$tmp['time']}\n";
                    // 如果rank达标就返回
                    if ($rank >= $config['least_score'])
                    {
                        self::assertEqual($result);
                        return array('number' => $tmp['number'], 'rank' => $rank);
                    }
                }
                catch (Exception $e)
                {
                    if (!$debug)
                    {
                        self::addHistory($lottery['lotteryid'], $expectedIssue, $v['id'], $v['rank'], $e->getCode(), '', 0, 0, date('Y-m-d H:i:s'));
                    }
                    // 中止
                    throw new exception($e->getMessage()." [source id={$v['id']}]", $e->getCode());
                }
            }
//break;
            // 数据源已经轮询一次，未到达标分值
            $retry++;
            if ($retry > $lottery['retry'])
            {
                echo "程序反复执行超过{$lottery['retry']}次仍然没取到，退出\n";
                break;
            }

            if ($sources)
            {
                $delay = $lottery['delay'];
                echo "\n以下开奖源(id=".implode(',', array_keys(self::array_spec_key($sources, 'id'))).")没有取到号码！将延时{$delay}秒，现在开始重试第 {$retry}/{$lottery['retry']} 次......\n";
                sleep($delay);
            }
        } while (!empty($sources));
        
        if (!$result)
        {
            throw new exception("All source fetch failed!", 35);
        }
//dump($result);
        self::assertEqual($result);
        $tmp = reset($result);
        
        return array('number' => $tmp['number'], 'rank' => $rank);
    }
    
    // 判断是否全等，如果有一个错就是非常严重的错误，因为一个网站没有及时更新数据是正常的，但如果更新一个错误的号码是不能容忍的
    private function assertEqual($numbers)
    {
        if (!is_array($numbers))
        {
            return true;
        }
        
        $tmp = array_pop($numbers);
        foreach ($numbers as $v)
        {
            if ($tmp['number'] !== $v['number'])
            {
                throw new exception("The source id {$v['sourceid']} result ({$v['number']}) is different from {$tmp['sourceid']}({$tmp['number']})", 14);
            }
        }
        
        return true;
    }

    public function fetchFromURL($lottery, $url, $expectedDate, $expectedIssue = 0)
    {
    	
        $t1 = microtime(true);
        $parts = parse_url($url);
        //var_dump($parts);die;
        preg_match('`[\w.]+(\w+)\.\w{2,3}$`Ui', $parts['host'], $match);
        $oWget = A::singleton("model_wget");
        // 为防止被spam，必须引入随机延迟
        $oWget->setRandDelay(10);
        switch ($match[1])
        {
            case 'shishicai':
                $result = self::_getFromShiShiCaiCn($lottery, $url, $expectedIssue);
                break;
            case '500wan':
                $result = self::_getFrom500WanCom($lottery, $url, $expectedIssue);
                break;
            case 'sohu':
                $result = self::_getFromSohuCom($lottery, $url, $expectedDate, $expectedIssue);
                break;
            case 'starlott':
                $result = self::_getFromStarlottCom($lottery, $url, $expectedIssue);
                break;
            case 'xjflcp':
                //必须指定要抓的奖期
                //$expectedIssue = '100620084';
                if ($expectedIssue == '0')
                {
                    return self::failed(15, "XJSSC必须指定奖期");
                    break;
                }
                $result = self::_getFromXjflcpCom($lottery, $url, $expectedIssue);
                break;
            case '2caipiao':
                $result = self::_getFrom2caipiaoCom($lottery, $url, $expectedIssue);
                break;
            case 'caishijie':
                $result = self::_getFromCaishijieCom($lottery, $url, $expectedIssue);
                break;
                //edit by jack
            case 'betzc':
            	$result = self::_getFromBetzcCom($lottery,$url,$expectedIssue);
            	break;
            case 'ecp888':
            	$result = self::_getFromEcp888Com($lottery, $url, $expectedIssue);
            	break;
            case 'iloto':	
            	$result = self::_getFromIlotoCom($lottery, $url, $expectedIssue);
            	break;
            case 'cailele':
            	$result = self::_getFromCaileleCom($lottery, $url, $expectedIssue);
            	break;
            case 'wozhongla':
            	$result = self::_getFromWozhonglaCom($lottery, $url, $expectedIssue);
            	break;
            case 'huacai':
            	$result = self::_getFromHuacaiCn($lottery, $url, $expectedIssue);
            	break;
            case 'cp2y':
            	$result = self::_getFromCp2yCom($lottery, $url, $expectedIssue);
            	break;
            case 'gdlottery':
            	$result = self::_getFromGdlotteryCn($lottery, $url, $expectedIssue);
            	break;

            case 'ourloto':
                $pattern = '`\<td\swidth="30%"\sclass="oCH">第&nbsp;(\d{11})&nbsp;期\</td>.*\<b\sclass="eBal">(\d)\</b>&nbsp;\<b\sclass="eBal">(\d)\</b>&nbsp;\<b\sclass="eBal">(\d)\</b>&nbsp;\<b\sclass="eBal">(\d)\</b>&nbsp;\<b\sclass="eBal">(\d)\</b>&nbsp;`Uims';
                dump('no'); die();
                $result = self::_getFromOurlotoCom($lottery, $url, $expectedIssue);
                break;
            case 'gov':
                if( $lottery['lotteryid'] == 9 )
                {//北京福利彩票
                    $result = self::_getFromBJFCGov($lottery, $url, $expectedDate,$expectedIssue);
                }
                elseif ($lottery['lotteryid'] == 10)
                {//重庆体彩
                    $result = self::_getFromCqLottery($lottery, $url, $expectedIssue);
                }
                break;
            default:
                return self::failed(11, "暂不支持的网站`{$url}`");
                break;
        }
        if (!$result)
        {
            return false;
        }

        $result['time'] = round(microtime(true) - $t1, 3);

        return $result;
    }

    /**
     * 广东体彩中心 http://www.gdlottery.cn/
     * @param <int> $lottery
     * @param <string> $url
     * @param <int> $expectedIssue
     * @return <array>
     */
    private function _getFromGdlotteryCn($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $port = 80;
        $method = "POST";
        $referer = $cookie = '';
        $post = "callCount=1&page=/&httpSessionId=ADAEA9ECADDC6D91DC3A7D561FD9CA76&scriptSessionId=C8371569907FEE7A51C72A4857B36157705&c0-scriptName=lot&c0-methodName=getLot11x5&c0-id=0&batchId=672";
        $oWget->setInCharset('utf-8');
        switch ($lottery['lotteryid'])
    	{
    		case '8':
    			$url = "http://www.gdlottery.cn/dwr/call/plaincall/lot.getLot11x5.dwr";
    			break;
    	}
        if (!$oWget->fetchContent("SOCKET", $url, $port, $method, $referer, $cookie, $post))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());

        if (strlen($contents) < 10)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }

        $pattern = '`<div\s+class=\\\\"text_wrap\\\\">.*<div\s+class=\\\\"text_1\\\\">(\d{8})</div>.*<span\s+class=\\\\"lot11x5_blue\\\\">(\d{2})</span><span\s+class=\\\\"lot11x5_blue\\\\">(\d{2})</span><span\s+class=\\\\"lot11x5_blue\\\\">(\d{2})</span><span\s+class=\\\\"lot11x5_blue\\\\">(\d{2})</span><span\s+class=\\\\"lot11x5_blue\\\\">(\d{2})</span>`Uims';
        preg_match_all($pattern, $contents, $matches);
        if (count($matches[1]) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
            case '8':
                /**
                 *  正确格式：10091801  05 10 06 02 03
                 * 10100828
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^\d{8}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    $tmpNumber = $matches[2][$k].' '.$matches[3][$k].' '.$matches[4][$k].' '.$matches[5][$k].' '.$matches[6][$k];
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    /**
     * 彩票二元网  http://www.cp2y.com/buy/?lid=10046
     * @param array $lottery
     * @param string $url
     * @param int $expectedIssue
     */
    private function _getFromCp2yCom($lottery, $url, $expectedIssue = 0)
    {
    	$oWget = A::singleton("model_wget");
        $iPort = 80;
        $sMethod = "GET";
        $referer = $cookie = $post = "";
    	$oWget->setInCharset('gb2312');
        switch ($lottery['lotteryid'])
    	{
    		case 5:
    			$url = 'http://www.cp2y.com/buy/draw_number!.jsp?rc=0.03606850378205917&lid=10046&baseDir=../';
    			break;
    	}
    	if (!$oWget->fetchContent( "FILE", $url, $iPort, $sMethod, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = $oWget->getBody();
        if (strlen($contents) < 100)
        {
            return self::failed(32, '取内容异常：contents='.$contents);
        }
    	$pattern = "/\<td\s*align=\"center\"\s*bgcolor=\"#\w{6}\"\s*class=\"instant-lt\s*instant-ll\s*ft\">(\d{8})\<\/td>.*\<td\s*align=\"center\"\s*class=\"instant-lt\s*instant-ll\s*font-red\s*ft\"\s*bgcolor=\"#\w{6}\">(\d{2}\s\d{2}\s\d{2}\s\d{2}\s\d{2})\<\/td>/Uims";
    	preg_match_all($pattern, $contents, $matches);

    	if ( count($matches[1]) < 2 )
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
    	switch ($lottery['lotteryid'])
    	{
    		case '5':
    			foreach ( $matches[1] as $k=>$v )
        		{
        			//正确格式 奖期：100709033	奖号：29144
        			$v = trim($v);
        			if ( !preg_match('/^\d{8}$/', $v) )
        			{
        				return self::failed(25, '奖期'.$v);
        			}
        			$tmpIssue =$v;
        			if ( !preg_match('/^(\d{2})\s(\d{2})\s(\d{2})\s(\d{2})\s(\d{2})$/', $matches[2][$k]))
        			{
        				return self::failed(25, '号码'.$matches[2][$k]);
        			}
        			$tmpNumber = $matches[2][$k];
        			$result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
        		}
        		break;
    		default:
    			return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
    			break;
    	}

    	return self::getNumber($result, $expectedIssue);
    }

    /**
     * 华彩网 http://www.huacai.cn/goBuyLotteryAction.do?lotteryId=150
     * @param array $lottery
     * @param string $url
     * @param int $expectedIssue
     * @return array
     */
    private function _getFromHuacaiCn($lottery, $url, $expectedIssue = 0)
    {
    	$oWget = A::singleton("model_wget");
        $iPort = 80;
        $sMethod = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('gb2312');
        if($lottery['lotteryid'] == 9)
        {
            $url = 'http://www2.huacai.cn/html_cn/js/lot_award_161_i_20.js';//北京快乐八在华彩上的历史号码具体地址
        }
    	if (!$oWget->fetchContent( "SOCKET", $url, $iPort, $sMethod, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = $oWget->getBody();
        if (strlen($contents) < 100)
        {
            return self::failed(32, '取内容异常：contents='.$contents);
        }
        if( $lottery['lotteryid'] == 9)
        {
            $pattern = "/\"content\":\"(.*)#.*\"issue_num\":\"(\d{6})\"/Uims";//北京快乐八
        }
        else
        {
            $pattern = "/\<td\s*align=\"center\">(\d{8}).*\<span\s*class='orange'>(\d{2}).*(\d{2}).*(\d{2}).*(\d{2}).*(\d{2}).*\<\/span>/Uims";
        }
    	preg_match_all($pattern, $contents, $matches);
    	if( $lottery['lotteryid'] == 9)//北京快乐八
    	{
    	    if ( count($matches[2]) < 1 )
    	    {
    	        return self::failed(33, '获取匹配内容失败：content：'.$contents);
    	    }
    	}
    	else
    	{
    	    if ( count($matches[1]) < 1 )
    	    {
    	        return self::failed(33, '获取匹配内容失败：content：'.$contents);
    	    }
    	}
        $result = array();
        // 严格检查并修正奖期
    	switch ($lottery['lotteryid'])
    	{
    		case 5:
    			foreach ( $matches[1] as $k=>$v )
        		{
        			//正确格式 奖期：100709033	奖号：29144
        			$v = trim($v);
        			if ( !preg_match('/^\d{8}$/', $v) )
        			{
        				return self::failed(25, '奖期'.$v);
        			}
        			$tmpIssue =$v;
        			$tmpNumber = $matches[2][$k]." ".$matches[3][$k]." ".$matches[4][$k]." ".$matches[5][$k]." ".$matches[6][$k];
        			if ( !preg_match('/^\d{2}\s\d{2}\s\d{2}\s\d{2}\s\d{2}$/', $tmpNumber))
        			{
        				return self::failed(25, '号码'.$tmpNumber);
        			}
        			$result[$tmpIssue] = array('issue'=>$tmpIssue, 'number'=>$tmpNumber);
        		}
        		break;
    		case 9://北京快乐八
    		    foreach ( $matches[2] as $k=>$v )
    		    {
    		        //正确格式 奖期：402686 开奖号码：02 16 19 23 26 28 33 40 41 43 46 49 50 55 58 59 62 70 74 79
    		        $v = trim($v);
    		        if ( !preg_match('/^\d{6}$/', $v) )
    		        {
    		            return self::failed(25, '奖期'.$v);//获取的奖期格式不正确
    		        }
    		        $tmpIssue =$v;
    		        $aTmpNumber = explode(",",$matches[1][$k]);
    		        if( count($aTmpNumber) != 20 )
    		        {
    		            return self::failed(25, '号码'.$matches[1][$k]);//获取的号码不正确
    		        }
    		        sort($aTmpNumber);//号码排序处理
    		        $tmpNumber = implode(" ",$aTmpNumber);
    		        $result[$tmpIssue] = array('issue'=>$tmpIssue, 'number'=>$tmpNumber);
    		    }
    		    break;
    		default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
    			break;
    	}

    	return self::getNumber($result, $expectedIssue);
    }
    
    /**
     * 我中啦 http://www.wozhongla.com/lottery/115/index.shtml
     * @param <type> $lottery
     * @param <type> $url
     * @param <type> $expectedIssue
     * @return <type> 
     */
    private function _getFromWozhonglaCom($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $port = 80;
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        switch ($lottery['lotteryid'])
    	{
    		case '3':
    			$url = "http://www.wozhongla.com/sp1/act/data.resultsscListOne.action?page.pagesize=10&page.no=1&type=006";
    			break;
    		case '5':
    			$url = "http://www.wozhongla.com/sp1/act/data.resultsscListOne.action?page.pagesize=10&page.no=1&type=107";
    		case '9':
    		    $url = "http://www.wozhongla.com/lotdata/WzlChart.dll?wAgent=100&wAction=101&wParam=LotID=20108_ChartID=20111_StatType=0";
    			break;
    	}
        if (!$oWget->fetchContent("SOCKET", $url, $port, $method, $referer, $cookie, $post))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());

        if (strlen($contents) < 10)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }
        if($lottery['lotteryid'] == 9)
        {
            $pattern = "/\<td\s*class=\"Issue\">\s*(\d{6})\s*\<\/td>.*\<td\s*class=\"Issue\">(.*)\+.*\<\/td>/Uims";
        }
        else
        {
            $pattern = '`"issueNumber":"(\d{7,11})",.*"resultNumber":"(\d[^"]+\d)"`Uims';
        }
        preg_match_all($pattern, $contents, $matches);
        if (count($matches[1]) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
            case '3':
                /**
                 *  正确格式：20100315-001 96513
                 * 0713046  1,8,2,7,4
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^\d{7}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = date('Y') . substr($v, 0, 4) . '-' . substr($v, 4, 3);
                    if (!preg_match('`^\d,\d,\d,\d,\d$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = implode('', explode(',', trim($matches[2][$k])));
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '5':
                /**
                 *  正确格式：10062301  07 04 11 01 08
                 * 10071341  05,09,11,02,03
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^1\d{7}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    if (!preg_match('`^\d{2},\d{2},\d{2},\d{2},\d{2}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = implode(' ', explode(',', trim($matches[2][$k])));
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '9'://北京快乐八
                /**
                 *  正确格式：奖期：402686 开奖号码：02 16 19 23 26 28 33 40 41 43 46 49 50 55 58 59 62 70 74 79
                 */
                krsort($matches[1]);
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    //奖期格式检测
                    if (!preg_match('`^\d{6}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    //开奖号码检测
                    $tmpNumber = trim($matches[2][$k]);
                    if(!preg_match('`^(\d{2}\s){19}(\d{2})$`', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $tmpIssue = $v;
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }
    
    /**
     * 彩乐乐 http://www.cailele.com/lottery/ssl/ 仅支持IE,fb不作用
     * @author Rojer
     * @param int $lottery
     * @param string $url
     * @param int $expectedIssue
     * @return array
     */
    private function _getFromCaileleCom($lottery, $url, $expectedIssue=0)
    {
    	$oWget = A::singleton("model_wget");
        $iPort = 80;
        $sMethod = "GET";
        $referer = $cookie = $post = "";
    	$oWget->setInCharset('utf-8');
    	switch ( $lottery['lotteryid'] )
    	{
    		case 1:
    			$url = "http://www.cailele.com/static/ssc/newlyopenlist.xml";
    			break;
    		case 3:
    			$url = "http://www.cailele.com/static/jxssc/newlyopenlist.xml";
    			break;
            case 4:
    			$url = "http://www.cailele.com/static/ssl/newlyopenlist.xml";
    			break;
    		case 5:
    			$url = "http://www.cailele.com/static/11yun/newlyopenlist.xml";
    			break;
            case 7:
    			$url = "http://www.cailele.com/static/jxdlc/newlyopenlist.xml";
    			break;
    	}
        if (!$oWget->fetchContent( "SOCKET", $url, $iPort, $sMethod, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }

        $contents = trim($oWget->getBody());
        if (!$xmlArray = self::parseXML($contents))
        {
            return self::failed(23, "解析XML出错");
        }
        $xmlArray = $xmlArray['xml'][0]['row'];
        if (!$xmlArray || count($xmlArray) == 0)
        {
            return self::failed(24, "XML2array出错");
        }

        $result = array();
        // 严格判断
        switch ($lottery['lotteryid'])
        {
            case '1':
                /*
                    * 正确格式：100604024 69262
                    Array
                    (
                        [@expect] => 20100712069
                        [@opencode] => 0,1,1,1,7
                        [@opentime] => 2010-07-12 17:30:30
                    )
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode('', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^201\d{8}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    $tmpIssue = substr($tmpIssue, 2, 9);
                    if (!preg_match('`^\d{5}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '3':
                /*
                    * 正确格式：20100622-001 70405
                    * <row expect="20100713008" opencode="0,3,5,9,0" opentime="2010-07-13 10:22:31"/>
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode('', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^201\d{8}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    $tmpIssue = substr($tmpIssue, 0, 8). '-' . substr($tmpIssue, 8, 3);
                    if (!preg_match('`^\d{5}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '4':
                /*
                    * 正确格式：20100622-01 772
                         <row expect="20100713-01" opencode="432" opentime="2010-7-13 10:30:00"/>
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = $xmlArray[$i]['@opencode'];
                    if (!preg_match('`^201\d{5}-\d{2}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^\d{3}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '5':
                /*
                    * 正确格式：10062301  07 04 11 01 08
                         <row expect="10071308" opencode="03,08,09,05,02" opentime="2010-07-13 10:30:00"/>
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode(' ', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^1[01]\d{6}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '7':
                /*
                    * 正确格式：20100623-01  07 04 11 01 08
                         <row expect="2010071311" opencode="09,10,08,07,05" opentime="2010-07-13 11:12:00"/>
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode(' ', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^201[01]\d{6}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    $tmpIssue = substr($tmpIssue, 0, 8) . '-' . substr($tmpIssue, 8, 2);
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    /**
     * 愛乐透 http://www.iloto.cn/Home/Lottery/Jxssc/Play.aspx
     * 限制很多
     * @author jack,rojer
     * @param int $lottery
     * @param string $url
     * @param int $expectedIssue
     * @return array
     */
    private function _getFromIlotoCom($lottery, $url, $expectedIssue=0)
    {
    	$oWget = A::singleton("model_wget");
        $port = 80;
        $method = "POST";
        $cookie = "ASP.NET_SessionId=it12fx55clcvbz55w5o5c5fi";
        $referer = "http://www.iloto.cn/Home/Lottery/Jxssc/Play.aspx";
        $post = "{}";
        $add = "x-ajaxpro-method: GetWinNumber";
        $oWget->setInCharset('utf-8');
        switch ($lottery['lotteryid'])
    	{
    		case '3':
    			$url = "http://www.iloto.cn/ajaxpro/Home_Lottery_Jxssc_Play,App_Web_lu11dzlq.ashx";
    			break;
    	}
        if (!$oWget->fetchContent("SOCKET", $url, $port, $method, $referer, $cookie, $post, $add))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 100)
        {
            return self::failed(32, '取内容异常：contents='.$contents);
        }
        $pattern = "`<tr[^>]*><td>(\d{7})</td>.*\">(\d{5})</td></tr>`Uims";
        preg_match_all($pattern, $contents, $matches);
        if (count($matches[1]) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格判断
    	switch ($lottery['lotteryid'])
        {
            case '3':
                /**
                 *  正确格式：20100315-001 96513
                 * 0713028  16756
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^0\d{6}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = date('Y') . substr($v, 0, 4) . '-' . substr($v, 4, 3);
                    if (!preg_match('`^\d{5}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = $matches[2][$k];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

    	return self::getNumber($result, $expectedIssue);
    }
    
    /**
     * 彩票直通车 http://tools.ecp888.com/Trade/sscjx/bin/NumberallXml.asp
     * XML形式数据
     * @author jack,rojer
     * @param int $lottery
     * @param string $url
     * @param int $expectedIssue
     * @return array
     */
    private function _getFromEcp888Com($lottery, $url, $expectedIssue = 0)
    {
    	$oWget = A::singleton("model_wget");
        $iPort = 80;
        $sMethod = "GET";
        $referer = $cookie = $post = "";
    	switch ($lottery['lotteryid'])
        {
      		case 1:
      			$url = "http://tools.ecp888.com/Trade/ssc/bin/NumberallXml.asp";
      			break;
      		case 3:
      			$url = "http://tools.ecp888.com/Trade/sscjx/bin/NumberallXml.asp";
      			break;
      		case 4:
      			$url = "http://tools.ecp888.com/Trade/ssl/bin/NumberallXml.asp";
      			break;
            case 7:
      			$url = "http://tools.ecp888.com/Trade/SyJx/bin/NumberallXml.asp";
      			break;
        }
        $oWget->setInCharset('gb2312');
        if (!$oWget->fetchContent( "SOCKET", $url, $iPort, $sMethod, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }

        $contents = trim($oWget->getBody());
        if (!$xmlArray = self::parseXML($contents))
        {
            return self::failed(23, "解析XML出错");
        }

        $xmlArray = $xmlArray['xml'][0]['row'];
        if (!$xmlArray || count($xmlArray) == 0)
        {
            return self::failed(24, "XML2array出错");
        }

        // At the first of issue the first element may be empty so need to be moved as none
        if (trim($xmlArray[0]['@anum']) == '')
        {
            array_shift($xmlArray);
        }

        $result = array();
        // 严格判断
        switch ($lottery['lotteryid'])
        {
            case '1':
                /*
                    * 正确格式：100604024 69262
                     array (
                          '@digit' => '20100712059',
                          '@atime' => '15:50',
                          '@anum' => '7 4 2 0 8',
                        ),
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@digit']);
                    $tmpNumber = implode('', explode(' ', $xmlArray[$i]['@anum']));
                    if (!preg_match('`^201\d{8}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    $tmpIssue = substr($tmpIssue, 2, 9);
                    if (!preg_match('`^\d{5}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '3':
                /*
                    * 正确格式：20100622-001 70405
                         <row  digit="20100712041" atime="15:57" anum="9 0 7 9 8" />
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@digit']);
                    $tmpNumber = implode('', explode(' ', $xmlArray[$i]['@anum']));
                    if (!preg_match('`^201\d{8}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    $tmpIssue = substr($tmpIssue, 0, 8). '-' . substr($tmpIssue, 8, 3);
                    if (!preg_match('`^\d{5}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '4':
                /*
                    * 正确格式：20100622-01 772
                         <row  digit="20100712-12" atime="16:00" anum="3 0 2" />
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@digit']);
                    $tmpNumber = implode('', explode(' ', $xmlArray[$i]['@anum']));
                    if (!preg_match('`^201\d{5}-\d{2}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^\d{3}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '7':
                /*
                    * 正确格式：20100623-01     04 08 11 01 07
                         <row  digit="2010071333" atime="15:34" anum="03 08 01 10 09" />
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@digit']);
                    $tmpNumber = $xmlArray[$i]['@anum'];
                    if (!preg_match('`^201\d{7}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    $tmpIssue = substr($tmpIssue, 0, 8).'-'.substr($tmpIssue, 8, 2);
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }
    
    /**
     * 盈彩网 http://ssl.betzc.com/
     * @author jack
     * @param int $lottery
     * @param string $url
     * @param int $expectedIssue
     * @return array
     */
    private function _getFromBetzcCom($lottery, $url, $expectedIssue=0)
    {
     	$oWget = A::singleton("model_wget");
        $port = 80;
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('gbk');
        if (!$oWget->fetchContent( "SOCKET", $url, $port, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 50000)
        {
            return self::failed(32, '取内容异常：contents='.$contents);
        }

        /*
         * CQSSC
         * <tr class="ssl_fatr2">
                  <td >0713061</td>
                  <td >16:10</td>
                  <td >96253</td>
                </tr>

         * <tr class="ssl_fatr2">
                  <td >0713-12</td>
                  <td >16:00</td>
                  <td >490</td>
                </tr>
         */
        /*
         * GD115
         * <tr class="trw">
	            <td>10100814</td>
	            <td>11:46</td>
	            <td class="rebchar">08&nbsp;05&nbsp;11&nbsp;03&nbsp;01&nbsp;</td>

	          </tr>
	          <tr class="trgray">
	            <td>10100813</td>
	            <td>11:34</td>
	            <td class="rebchar">02&nbsp;07&nbsp;04&nbsp;11&nbsp;06&nbsp;</td>

	          </tr>
         */
        switch ($lottery['lotteryid'])
        {
        	case '1':
            case '4':
                $pattern = "/\<tr\s*class=\"ssl_fatr[23]\">\s*<td\s*>([\d-]{7,})\<\/td>.*\<td\s*>(\d{3,5})\<\/td>/Uims";
                break;
            case '8':
                $pattern = '`\<tr\s*class="(?:trw|trgray)">\s*<td\s*>(\d{8})\</td>.*\<td\s+class="rebchar">(\d{2})&nbsp;(\d{2})&nbsp;(\d{2})&nbsp;(\d{2})&nbsp;(\d{2})&nbsp;\</td>`Uims';
                break;
            default:
                return self::failed(13, "没有定义规则");
                break;
        }
		preg_match_all($pattern, $contents, $matches);

        if (count($matches[1]) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
        	case '1':
                /**
                 *  正确格式：100623-01  490
                 * 0713061  96253
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('/^\d{7}$/', $v))
                    {
                        return self::failed(25, '奖期：'.$v);
                    }
                    $tmpIssue = date('y').$v;
                    if (!preg_match('/^\d{5}$/', $matches[2][$k]))
                    {
                        return self::failed(25, '号码：'.$matches[2][$k]);
                    }
                    $tmpNumber = $matches[2][$k];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
        	case 4:
                /**
                 *  正确格式：20100623-01  490
                 * 0713-12  490
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('/^\d{4}-\d{2}$/', $v))
                    {
                        return self::failed(25, '奖期：'.$v);
                    }
                    $tmpIssue = date('Y').$v;
                    if (!preg_match('/^\d{3}$/', $matches[2][$k]))
                    {
                        return self::failed(25, '号码：'.$matches[2][$k]);
                    }
                    $tmpNumber = $matches[2][$k];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '8':
                /**
                 *  正确格式：10091801  05 10 06 02 03
                 * 10100814
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('/^\d{8}$/', $v))
                    {
                        return self::failed(25, '奖期：'.$v);
                    }
                    $tmpIssue = $v;
                    $tmpNumber = $matches[2][$k].' '.$matches[3][$k].' '.$matches[4][$k].' '.$matches[5][$k].' '.$matches[6][$k];
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, '号码：'.$matches[2][$k]);
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
        	default:
        		return self::failed(12, '没有相应彩种'.$lottery['cnname']);
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }
    
    /**
     * 
     * @param <int> $lottery
     * @param <string> $url
     * @param <int> $expectedIssue
     * @return <array> 
     */
    private function _getFromCaishijieCom($lottery, $url, $expectedIssue = 0)
    {
    	//下面地址可以取得xml的数据，取出号码
    	//$url = "http://ssc.caishijie.com/luckynumber/newsscnumbernew.xml?tt=70661.87565214932";
        $oWget = A::singleton("model_wget");
        $port = 80;
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        
        switch ($lottery['lotteryid'])
        {
            case '8':
                $randNum = "0.".rand(10000000,100000000-1).rand(10000000,100000000-1);
                $url = "http://uc.caishijie.com/normalsta/shiyixuanwu_gd.html?id=$randNum";
                break;
            case '9'://北京快乐八
                $url = "http://uc.caishijie.com/beijing/kai_kl8.html";
                break;
             default:
                 break;
        }

        if (!$oWget->fetchContent( "FILE", $url, $port, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 100)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }
        switch ($lottery['lotteryid'])
        {
            case '2':
                $pattern = "`\<dl\s*class=\"box_1[12]\">\s*\<dt>(\d{7})\</dt>.*\<dd[^>]*>(\d,\d,\d,\d,\d)\</dd>`Uims";
                break;
            case '8':
                $pattern = '`\<dl\s+class="dl_0[3|2]">\<dt>(\d{8})</dt>\<dd\s+class="eexf">(\d{2})&nbsp;(\d{2})&nbsp;(\d{2})&nbsp;(\d{2})&nbsp;(\d{2})\</dd>\</dl>`Uims';
                break;
            case '9':
                $pattern = "/期号(\d{6})";
                for($i=0;$i<20;$i++)
                {
                    $pattern .= ".*class=\"kj_(\d{2})\"";
                }
                $pattern .= "/Uims";
                break;
            default:
                return self::failed(13, "没有定义规则");
                break;
        }
        preg_match_all($pattern, $contents, $matches);
        if (count($matches[1]) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
            case '2':
                /**
                 *  正确格式：0027628 96513
                 * 0027628 2,5,8,9,5
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^00\d{5}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    if (!preg_match('`^(\d),(\d),(\d),(\d),(\d)$`', $matches[2][$k], $match))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => "{$match[1]}{$match[2]}{$match[3]}{$match[4]}{$match[5]}");
                }
                break;
            case '8':
                /**
                 *  正确格式：10100833  04 09 07 01 10
                 * 10100833
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^\d{8}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    $tmpNumber = $matches[2][$k].' '.$matches[3][$k].' '.$matches[4][$k].' '.$matches[5][$k].' '.$matches[6][$k];
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '9'://北京快乐八
                /**
                 *  正确格式：奖期：402686 开奖号码：02 16 19 23 26 28 33 40 41 43 46 49 50 55 58 59 62 70 74 79
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^\d{6}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    $tmpNumber = $matches[2][$k];
                    for ($i=3;$i<22;$i++)
                    {
                        $tmpNumber .= " ".$matches[$i][$k];
                    }
                    if(!preg_match('`^(\d{2}\s){19}(\d{2})$`', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    private function _getFrom2caipiaoCom($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $port = 80;
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('gb2312');
        if (!$oWget->fetchContent( "FILE", $url, $port, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());

        if (strlen($contents) < 150000)
        {
            $fn = "/tmp/".$lottery['cnname']."_".$expectedIssue."_".date("H:i:s")."_".strlen($contents).".txt";
            file_put_contents($fn, $oWget->getResponseHeadAsString(), FILE_APPEND);
            file_put_contents($fn, "::内容::".$contents, FILE_APPEND);
            return self::failed(32, "取内容异常：contents=$contents");
        }

        switch ($lottery['lotteryid'])
        {
            case '3':
                $pattern = "`\<\!--时时彩开奖公告--\>(.*)\<\!--时时彩开奖公告 结束--\>`Uims";
                break;
            case '8':
                $pattern = "`\<\!--开奖公告\s*开始--\>(.*)\<\!--开奖公告\s*结束--\>`Uims";
                break;
            default:
                return self::failed(13, "没有定义规则");
                break;
        }

        if (!preg_match($pattern, $contents, $match))
        {
            return self::failed(22, "获取块内容出错：content length:".strlen($contents).", content=$contents");
        }

        switch ($lottery['lotteryid'])
        {
            case '3':
                $pattern = "`\<tr[^>]*>\s*\<td[^>]*>(\d{11})\</td>.*(\d,\d,\d,\d,\d)\</td>`Uims";
                break;
            case '8':
                $pattern = "`\<tr[^>]*>\s*\<td[^>]*>(\d{10})\</td>.*(\d{2},\d{2},\d{2},\d{2},\d{2})\</td>`Uims";
                break;
            default:
                return self::failed(13, "没有定义规则");
                break;
        }
        
        $contents = $match[1];
        preg_match_all($pattern, $contents, $matches);

        if (count($matches) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
            case '3':
                /**
                 *  正确格式：20100315-001 96513
                 * 20100608016 2,4,3,0,3
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{8}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 0, 8) . '-' . substr($v, 8, 3);
                    if (!preg_match('`^\d,\d,\d,\d,\d$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = implode('', explode(',', trim($matches[2][$k])));
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '8':
                /**
                 *  正确格式：10100626  07 02 09 01 11
                 * 2010100623 05,10,08,01,11
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{7}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 0, 8) . '-' . substr($v, 8, 3);
                    if (!preg_match('`^\d{2},\d{2},\d{2},\d{2},\d{2}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = implode(' ', explode(',', trim($matches[2][$k])));
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    /**
     * starlott.com处理，必须指定奖期
     * @param <type> $url
     * @return 成功返回array('issue' => $issue, 'number' => $number)，出错返回false，不在奖期范围内返回字符串
     */
    private function _getFromXjflcpCom($lottery, $url, $expectedIssue)
    {
        $oWget = A::singleton("model_wget");
        $method = "GET";
        $referer = $cookie = $post = "";
        $date = date('Ymd');
        $oWget->setInCharset('gbk');
        switch ($lottery['lotteryid'])
        {
            case '6':   // http://www.xjflcp.com/openprize.do 100620084 2010062084
                $adjustIssue = "20".$expectedIssue;
                $url = "http://www.xjflcp.com/video/prizeDetail.do?operator=detailssc&lotterydraw=$adjustIssue";
                break;
        }

        if (!$oWget->fetchContent("SOCKET", $url, 80, $method, $referer, $cookie, $post))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 100)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }

        $pattern = "`<td.*>\s*期号.*(\d{10}).*开奖号码.*</td>\s*<td.*>\s*(\d\D*\d\D*\d\D*\d\D*\d)\s*</td>`Uims";
        preg_match($pattern, $contents, $match);
        if (empty($match[1]) || empty($match[2]))
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        // 严格判断
        $result = array();
        switch ($lottery['lotteryid'])
        {
            case '6':
                /**
                 *  正确格式：10062084 96513
                 * 2010062084 4&nbsp;2&nbsp;8&nbsp;7&nbsp;4
                 */
                $match[1] = trim($match[1]);
                if (!preg_match('`^201\d{7}$`', $match[1]))
                {
                    return self::failed(25, "奖期：{$match[1]}");
                }
                $tmpIssue = substr($match[1], 2, 8);
                if (!preg_match('`^(\d)\D*(\d)\D*(\d)\D*(\d)\D*(\d)$`', $match[2], $match2))
                {
                    return self::failed(25, "号码：{$match[2]}");
                }
                $tmpNumber = $match2[1].$match2[2].$match2[3].$match2[4].$match2[5];
                $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    /**
     * starlott.com处理
     * @param <type> $url
     * @return 成功返回array('issue' => $issue, 'number' => $number)，出错返回false，不在奖期范围内返回字符串
     */
    private function _getFromStarlottCom($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $method = "POST";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        $date = date('Ymd');
        switch ($lottery['lotteryid'])
        {
            case '1':   // http://ssc.starlott.com/
                $url = 'http://ssc.starlott.com/dwr/exec/Index.getAwardInfo.dwr';
                $post = 'callCount=1&c0-scriptName=Index&c0-methodName=getAwardInfo&c0-id=8600_1275890468199&c0-param0=string:FCCQSSC&c0-param1=string:&xml=true';
                $referer = 'http://ssc.starlott.com';
                break;
            case '4':  // http://ssl.starlott.com/
                $url = 'http://ssl.starlott.com/dwr/exec/Index.getAwardInfo.dwr';
                $post = 'callCount=1&c0-scriptName=Index&c0-methodName=getAwardInfo&c0-id=7576_1275896804009&c0-param0=string:FCSHSSL&c0-param1=string:&xml=true';
                $referer = 'http://ssl.starlott.com';
                break;
            case '7':  // http://11x5.starlott.com/
                $url = 'http://11x5.starlott.com/dwr/exec/Index.getAwardInfo.dwr';
                $post = 'callCount=1&c0-scriptName=Index&c0-methodName=getAwardInfo&c0-id=638_1279005181403&c0-param0=string:TCJXDLC&c0-param1=string:&xml=true';
                $referer = 'http://11x5.starlott.com';
                break;
        }

        if (!$oWget->fetchContent( "SOCKET", $url, 80, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 100)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }

        $pattern = "`\{issue:\\\\'([\d-]{8,11})\\\\.*number:\\\\'(\d.*\d)\\\\',`Uims";
        preg_match_all($pattern, $contents, $matches);
        if (empty($matches[1]) || empty($matches[2]))
        {
            return self::failed(22, "获取块内容出错：content length:".strlen($contents).", content=$contents");
        }

        // 严格判断
        $result = array();
        switch ($lottery['lotteryid'])
        {
            case '1':
                /**
                 *  正确格式：100604051 96513
                 * 20100607059 6&nbsp;&nbsp;4&nbsp;&nbsp;8&nbsp;&nbsp;3&nbsp;&nbsp;8
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{8}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 2, 6) . substr($v, 8, 3);
                    if (!preg_match('`^(\d)\D*(\d)\D*(\d)\D*(\d)\D*(\d)$`', $matches[2][$k], $match2))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = $match2[1].$match2[2].$match2[3].$match2[4].$match2[5];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '4':
                /**
                 *  正确格式：20100315-01 515
                 * 20100607-14	2&nbsp;&nbsp;9&nbsp;&nbsp;7
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{2}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    if (!preg_match('`^(\d)\D*(\d)\D*(\d)$`', $matches[2][$k], $match2))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = $match2[1].$match2[2].$match2[3];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '7':
                /**
                 *  正确格式：20100623-01   04 08 11 01 07
                 * 10071332     09&nbsp;&nbsp;01&nbsp;&nbsp;02&nbsp;&nbsp;05&nbsp;&nbsp;11
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^1\d{7}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = '20'.substr($v, 0, 6).'-'.substr($v, 6, 2);
                    if (!preg_match('`^(\d{2})\D*(\d{2})\D*(\d{2})\D*(\d{2})\D*(\d{2})$`', $matches[2][$k], $match2))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = $match2[1].' '.$match2[2].' '.$match2[3].' '.$match2[4].' '.$match2[5];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    /**
     * sohu.com处理
     * @param <type> $url
     * @return 成功返回array('issue' => $issue, 'number' => $number)，出错返回false，不在奖期范围内返回字符串
     */
    private function _getFromSohuCom($lottery, $url, $expectedDate, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        $date = date("Ymd", strtotime($expectedDate));
        switch ($lottery['lotteryid'])
        {
            case '1':   // http://lottery.sports.sohu.com/open/ssc.shtml
                $url = 'http://lottery.sports.sohu.com/open/inc/getHttpXml.php?lotname=ssc&expect='.$date.'&type=0&callback=_getGPResult';
                break;
            case '4':  // http://lottery.sports.sohu.com/open/ssl.shtml
                $url = 'http://lottery.sports.sohu.com/open/inc/getHttpXml.php?lotname=ssl&expect='.$date.'&type=0&callback=_getGPResult';
                break;
            case '5':  // http://lottery.sports.sohu.com/open/syydj.shtml
                $url = 'http://lottery.sports.sohu.com/open/inc/getHttpXml.php?lotname=syydj&expect='.$date.'&type=0&callback=_getGPResult';
            case '9':
                $url = 'http://bjfcdt.gov.cn/LtrAPI/happy8/v1/getAwardNumber.aspx?date='.$expectedDate;
                break;
        }

        if (!$oWget->fetchContent( "SOCKET", $url, 80, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 10)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }

        // _getGPResult('ssl','');
        if($lottery['lotteryid'] == 9)
        {
            $pattern = "/\<span\s*class=\"flow_font\">(\d{6})";
            for($i=0;$i<20;$i++)
            {
                $pattern .= ".*\<td\s*align=\"center\"\s*class=\".*\">(\d{2})\<\/td>";
            }
            $pattern .= "/Uims";
            preg_match_all($pattern, $contents, $match);
        }
        else
        {
            $pattern = '`<\?xml.*</xml>`Uims';
            preg_match($pattern, $contents, $match);
        }
        if($lottery['lotteryid'] == 9)
        {
            if ( count($match[1]) < 1 )
            {
                return self::failed(36, "没取到对应奖期`$expectedIssue`，可能因为源还没有更新");
            }
        }
        else
        {
            if (strlen($match[0]) < 10 || empty($match[0]))
            {
                return self::failed(36, "没取到对应奖期`$expectedIssue`，可能因为源还没有更新");
            }

            $contents = $match[0];
            if (!$xmlArray = self::parseXML($contents))
            {
                return self::failed(23, "解析XML出错");
            }

            $xmlArray = $xmlArray['xml'][0]['row'];
            if (!$xmlArray || count($xmlArray) == 0)
            {
                return self::failed(24, "XML2array出错");
            }
        }
        $result = array();
        // 严格判断
        switch ($lottery['lotteryid'])
        {
            case '1':
                /*
                    * 正确格式：100604024 69262
                     [31] => Array
                        (
                            [@expect] => 100607032
                            [@opencode] => 3,3,1,8,7
                            [@opentime] => 2010-6-7 11:20:00
                        )
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode('', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^1\d{8}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^\d{5}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '4':
                /*
                    * 正确格式：20100315-01 515
                         [2] => Array
                        (
                            [@expect] => 20100607-03
                            [@opencode] => 355
                            [@opentime] => 2010-6-7 11:30:00
                        )
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode('', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^201\d{5}-\d{2}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^\d{3}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '5':
                /*
                    * 正确格式：10031501 04 09 07 05 11
                         (
                            [@expect] => 10061809
                            [@opencode] => 04,05,07,09,10
                            [@opentime] => 2010-06-18 00:00:00
                        )
                     */
                for ($i = count($xmlArray) - 1, $j = 0; $i>=0 && $j<120; $i--, $j++)
                {
                    $tmpIssue = trim($xmlArray[$i]['@expect']);
                    $tmpNumber = implode(' ', explode(',', $xmlArray[$i]['@opencode']));
                    if (!preg_match('`^1[01]\d{6}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                arsort($result);
                break;
            case '9'://北京快乐八
                /**
                 *  正确格式：奖期：402686 开奖号码：02 16 19 23 26 28 33 40 41 43 46 49 50 55 58 59 62 70 74 79
                 */
                foreach ($match[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^\d{6}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    $tmpNumber = $match[2][$k];
                    for ($i=3;$i<22;$i++)
                    {
                        $tmpNumber .= " ".$match[$i][$k];
                    }
                    if(!preg_match('`^(\d{2}\s){19}(\d{2})$`', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    /**
     * 500wan.com处理
     * @param <type> $url
     * @return 成功返回array('issue' => $issue, 'number' => $number)，出错返回false，不在奖期范围内返回字符串
     */
    private function _getFrom500WanCom($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        switch ($lottery['lotteryid'])
        {
            case '1':   // http://www.500wan.com/pages/info/ssc/
                $url = 'http://jk.trade.500wan.com/static/public/ssc/xml/newlyopenlist.xml';
                break;
            case '7':  // 'http://jk.trade.500wan.com/pages/trade/dlc/'
                $url = 'http://jk.trade.500wan.com/static/public/dlc/xml/newlyopenlist.xml';
                break;
        }

        if (!$oWget->fetchContent( "SOCKET", $url, 80, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        if (strlen($contents) < 100)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }

        //$pattern = '`<row expect="(\d+)".*opencode="(.+)"`Uims';
        if (!$xmlArray = self::parseXML($contents))
        {
            return self::failed(23, "解析XML出错");
        }

        $xmlArray = $xmlArray['xml'][0]['row'];
        if (!$xmlArray || count($xmlArray) < 2)
        {
            return self::failed(24, "XML2array出错");
        }
//dump($xmlArray); die();
        $result = array();
        // 严格判断
        switch ($lottery['lotteryid'])
        {
            case '1':
                /*
                    * 正确格式：100604024 69262
                     $tmp = Array
                        (
                            [@expect] => 100604024
                            [@opencode] => 6,9,2,6,2
                            [@abbdate] => 20100604
                            [@endtime] => 2010-6-4 10:00:00
                            [@order] => 24
                            [@opentime] => 2010-6-4 10:00:00
                        )
                     */
                foreach ($xmlArray as $v)
                {
                    $tmpIssue = trim($v['@expect']);
                    $tmpNumber = implode('', explode(',', $v['@opencode']));
                    if (!preg_match('`^1\d{8}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^\d{5}$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '7':
                /*
                 *  正确格式：20100604-21   01 03 09 06 02
                 * Array
                    (
                        [@expect] => 10060421
                        [@opencode] => 01,03,09,06,02
                        [@abbdate] => 20100604
                        [@endtime] => 2010-6-4 13:12:00
                        [@order] => 21
                        [@opentime] => 2010-6-4 13:12:00
                    )
                 */
                foreach ($xmlArray as $v)
                {
                    $tmpIssue = '20' . substr($v['@expect'], 0, 6) . '-' . substr($v['@expect'], 6, 2);
                    $tmpNumber = implode(' ', explode(',', $v['@opencode']));
                    if (!preg_match('`^201\d{5}-\d{2}$`Ui', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }

    private function _getFromShiShiCaiCn($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        if (!$oWget->fetchContent( "SOCKET", $url, 80, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());

        if (strlen($contents) < 32000)
        {
            $fn = "/tmp/".$lottery['cnname']."_".$expectedIssue."_".date("H:i:s")."_".strlen($contents).".txt";
            file_put_contents($fn, $oWget->getResponseHeadAsString(), FILE_APPEND);
            file_put_contents($fn, "::内容::".$contents, FILE_APPEND);
            return self::failed(32, "取内容异常：contents=$contents");
        }
        $pattern = '`<table>\s*<tr>\s*<td>期号</td>\s*<td>开奖号码</td>(.*)((更多开奖号码)|(</table>))`Uims';
        if (!preg_match($pattern, $contents, $match))
        {
            return self::failed(22, "获取块内容出错：content length:".strlen($contents).", content=$contents");
        }

        $contents = $match[1];
        $pattern = '`<tr>\s*<td>([\d-]{8,12})</td>\s*<td>([\d,]{3,14})</td>`Uims';
        preg_match_all($pattern, $contents, $matches);

        if (count($matches[1]) < 2)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
        // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
            case '1':
                /**
                 *  1 正确格式：100604051 96513
                 * 20100604-051 96513
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{3}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 2, 6) . substr($v, 9, 3);
                    if (!preg_match('`^\d{5}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $matches[2][$k]);
                }
                break;
            case '2':
                /**
                 *  1 正确格式：0027621 96513
                 * 10027621 96513
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^100\d{5}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 1);
                    if (!preg_match('`^\d{5}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $matches[2][$k]);
                }
                break;
            case '3':
                /**
                 *  3 正确格式：20100315-001 96513
                 * 20100604-051 96513
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{3}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    if (!preg_match('`^\d{5}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $matches[2][$k]);
                }
                break;
            case '4':
                /**
                 *  3 正确格式：20100315-01 515
                 * 20100609-01 290
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{2}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    if (!preg_match('`^\d{3}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $matches[2][$k]);
                }
                break;
            case '6':
                /**
                 *  6 正确格式：10060451 96513
                 * 20100607-35 96513
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{2}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 2, 6) . substr($v, 9, 2);
                    if (!preg_match('`^\d{5}$`', $matches[2][$k]))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $matches[2][$k]);
                }
                break;
            case '5':
                /**
                 * 7 正确格式：10031501   01 09 11 07 05
                 * 20100618-06	01,02,04,08,11
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{2}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 2, 6).substr($v, 9, 2);
                    $tmpNumber = implode(' ', explode(',', trim($matches[2][$k])));
                    if (!preg_match('`^1[01]\d{6}$`', $tmpIssue))
                    {
                        return self::failed(25, "奖期：{$tmpIssue}");
                    }
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '7':
                /**
                 * 7 正确格式：20100524-01   01 09 11 07 05
                 * 20100604-44	01,02,04,08,11
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{2}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = trim($v);
                    $tmpNumber = implode(' ', explode(',', trim($matches[2][$k])));
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            case '8':
            case '10':
                /**
                 * 7 正确格式：10091801  01 09 11 07 05
                 * 20101008-13	02,07,04,11,06
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`^201\d{5}-\d{2}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = substr($v, 2, 6).substr($v, 9, 2);
                    $tmpNumber = implode(' ', explode(',', trim($matches[2][$k])));
                    if (!preg_match('`^[01]\d [01]\d [01]\d [01]\d [01]\d$`Ui', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }
    
    
    /**
     * 北京福利彩票中心
     *
     * @author mark
     * 
     * @param int $lottery 彩种ID
     * @param string $url 号码地址
     * @param string $expectedDate 号码时间
     * @param string $expectedIssue 奖期
     * 
     * @return array
     */
    private function _getFromBJFCGov($lottery, $url, $expectedDate, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $port = 80;
        $method = "GET";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        switch ($lottery['lotteryid'])
        {
            case '9':
                $url = "http://tb.bjfcdt.gov.cn/interface.aspx?years=".$expectedDate."&charttype=H8other_2";
                break;
            default:
                break;
        }
        if (!$oWget->fetchContent("SOCKET", $url, $port, $method, $referer, $cookie, $post))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());

        if (strlen($contents) < 10)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }
        if($lottery['lotteryid'] == 9)
        {
            $pattern = "/new h8_succ\((\d{6}),\[(.*)\],.*\)/Uims";
        }
        preg_match_all($pattern, $contents, $matches);
        if (count($matches[1]) < 1)
        {
            return self::failed(33, '获取匹配内容失败：content：'.$contents);
        }

        $result = array();
         // 严格检查并修正奖期
        switch ($lottery['lotteryid'])
        {
            case '9'://北京快乐八
                /**
                 *  正确格式：奖期：402686 开奖号码：02 16 19 23 26 28 33 40 41 43 46 49 50 55 58 59 62 70 74 79
                 */
                krsort($matches[1]);
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    //奖期格式检测
                    if (!preg_match('`^\d{6}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    //开奖号码检测
                    $aNumber = explode(",",trim($matches[2][$k]));
                    $tmpNumber = implode(" ",$aNumber);
                    if(!preg_match('`^(\d{2}\s){19}(\d{2})$`', $tmpNumber))
                    {
                        return self::failed(25, "号码：{$tmpNumber}");
                    }
                    $tmpIssue = $v;
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }
        return self::getNumber($result, $expectedIssue);
    }
    static public function parseXML($xml)
    {
        $reader = new XMLReader();

        if (!$reader->XML($xml))
        {
            return false;
        }

        $container = array();
        $container_stack = array();
        $result = &$container;
        $is_empty_element = false;

        while ($reader->read())
        {
            switch ($reader->nodeType)
            {
            case XMLReader::ELEMENT :
                $container[$reader->name][] = array();
                $container = &$container[$reader->name][count($container[$reader->name]) - 1];
                $container_stack[] = &$container;

                if ($reader->isEmptyElement)
                {
                    $is_empty_element = true;
                }

                if ($reader->hasAttributes)
                {
                    while ($reader->moveToNextAttribute())
                    {
                        $container['@' . $reader->name] = $reader->value;
                    }
                }

                if ($is_empty_element)
                {
                    array_pop($container_stack);
                    $container = &$container_stack[count($container_stack) - 1];
                    $is_empty_element = false;
                }

                break;

            case XMLReader::TEXT :
            case XMLReader::CDATA :
                if (isset($container['#text']))
                {
                    $container['#text'] .= $reader->value;
                }
                else
                {
                    $container['#text'] = $reader->value;
                }

                break;

            case XMLReader::END_ELEMENT :
                array_pop($container_stack);
                $container = &$container_stack[count($container_stack) - 1];

                break;

            default :
                continue;
            }
        }

        $reader->close();

        if (empty($result))
        {
            print_rr($xml);
        }

        return empty($result) ? false : $result;
    }

    static public function array_spec_key($array, $key, $unset_key = false)
    {
        if (empty($array) || !is_array($array))
        {
            return array();
        }

        $new_array = array();
        foreach ($array AS $value)
        {
            if (!isset($value[$key]))
            {
                continue;
            }
            $value_key = $value[$key];
            if ($unset_key === true)
            {
                unset($value[$key]);
            }
            $new_array[$value_key] = $value;
        }

        return $new_array;
    }

    static public function getNumber($result, $expectedIssue)
    {
        if (!$expectedIssue)
        {
            $tmp = reset($result);
        }
        else
        {
            if (empty($result[$expectedIssue]))
            {
                // 没找到号码，一般是因为没有及时更新，这不应该视为出错
                //return self::failed(36, "out of issue range.the expected issue is $expectedIssue but the result is (" . implode(',', array_keys($result)) . ")");
                //return array('errno' => 36, 'issueList' => $result);
                return self::failed(36, "issueList:".var_export($result,true)); // .
            }
            $tmp = $result[$expectedIssue];
        }

        return array('errno' => 0, 'issue' => $tmp['issue'], 'number' => $tmp['number']);
    }

    static public function failed($errno, $errstr)
    {
        /*
        switch ($errno)
        {
            case 11: // 网址错误
                return array('errno' => $errno, 'errstr' => "开奖源设置错误:$errstr");
                break;
            case 21: // 网站不可访问
                return array('errno' => $errno, 'errstr' => "网站不可访问:$errstr");
                break;
            case 22: // 解析错误
                return array('errno' => $errno, 'errstr' => "解析错误:$errstr");
                break;
            case 23: // 格式错误
                return array('errno' => $errno, 'errstr' => "格式错误:$errstr");
                break;
            case 36: // 未读取到奖期 不抛异常
                return array('errno' => $errno, 'errstr' => "未读取到奖期:$errstr");
                break;
            case 14: // 错误号码
                return array('errno' => $errno, 'errstr' => "错误号码:$errstr");
                break;
            default:
                die("unknown errno $errno");
                break;
        }
        */
        switch (substr($errno, 0, 1))
        {
            case 1:// error 开奖源设置错误 14号码错误
                throw new exception("$errstr", $errno);
                break;
            case 2:
                // warning级错误: 数据格式类错误，一般应报警
                throw new exception("$errstr", $errno);
                break;
            case 3:
                // notice: 更新不及时，网络读取错误，这里不抛异常
                //throw new exception("$errstr", $errno);
                return array('errno' => $errno, 'errstr' => $errstr);
                break;
            default:
                throw new exception("unknown error", 10);
                break;
        }
    }

    
    /**
     * http://www.cqlottery.gov.cn重庆体彩网处理
     * @param <type> $url
     * @return 成功返回array('issue' => $issue, 'number' => $number)，出错返回false，不在奖期范围内返回字符串
     */
    private function _getFromCqLottery($lottery, $url, $expectedIssue = 0)
    {
        $oWget = A::singleton("model_wget");
        $method = "POST";
        $referer = $cookie = $post = "";
        $oWget->setInCharset('utf-8');
        $date = date('Y-m-d');
        switch ($lottery['lotteryid'])
        {
            case '10':   // http://www.cqlottery.gov.cn/jsps/11x5/index.jsp
                $url = 'http://www.cqlottery.gov.cn/dwr/exec/lotteryInfo.get11x5AwardInfo.dwr';
                $post = 'callCount=1&c0-scriptName=lotteryInfo&c0-methodName=get11x5AwardInfo&c0-id=7633_1290153146250&c0-param0=string:FCCQ11X5&c0-param1=string:'.$date.'&xml=true';
                $referer = 'http://www.cqlottery.gov.cn';
                break;
            default:
                break;
        }
        if (!$oWget->fetchContent( "SOCKET", $url, 80, $method, $referer, $cookie, $post ))
        {
            return self::failed(31, "取内容出错：postheader=" . $oWget->getPostHeadAsString());
        }
        $contents = trim($oWget->getBody());
        $contents = str_replace("\"","",$contents);
        $contents = str_replace("\\","",$contents);
        if (strlen($contents) < 100)
        {
            return self::failed(32, "取内容异常：contents=$contents");
        }
        $pattern = "/\<tr>.*\<td.*class=STYLE4>(\d{8})\<\/span>.*class=STYLE6>(.*)\<\/span>.*\<\/tr>/Uims";
        preg_match_all($pattern, $contents, $matches);
        if (empty($matches[1]) || empty($matches[2]))
        {
            return self::failed(22, "获取块内容出错：content length:".strlen($contents).", content=$contents");
        }
        // 严格判断
        $result = array();
        switch ($lottery['lotteryid'])
        {
            case '10':
                /**
                 *  正确格式：10111942 01 09 02 07 10
                 * 
                 */
                foreach ($matches[1] as $k => $v)
                {
                    $v = trim($v);
                    if (!preg_match('`\d{8}$`', $v))
                    {
                        return self::failed(25, "奖期：{$v}");
                    }
                    $tmpIssue = $v;
                    if (!preg_match('`^\d{2}\s\d{2}\s\d{2}\s\d{2}\s\d{2}$`', $matches[2][$k], $match2))
                    {
                        return self::failed(25, "号码：{$matches[2][$k]}");
                    }
                    $tmpNumber = $matches[2][$k];
                    $result[$tmpIssue] = array('issue' => $tmpIssue, 'number' => $tmpNumber);
                }
                break;
            default:
                return self::failed(12, "没有相应彩种`{$lottery['cnname']}`");
                break;
        }

        return self::getNumber($result, $expectedIssue);
    }
}

?>
