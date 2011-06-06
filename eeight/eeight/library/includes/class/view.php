<?php
/**
 * view 视图类
 * 
 * 依赖全局:
 *    A::$_aIni -> class.bDevelopMode            (全局开发模式), 记录显示SQL错误
 * 
 * @author   Tom,James
 * @version  1.2.0
 * @package  Core
 */

class view
{
    public $template_dir   = '';      // 模板存放目录      例: A_DIR.DS.'/views/'    
    public $template_dir_default = ''; // 模板文件默认/备用目录
    public $direct_output  = FALSE;   // 是否直接输出 (TRUE=忽略缓存) => in fetch()
    public $caching        = FALSE;   // 是否开启缓存功能
    public $cache_lifetime = 10;      // 模板编译,及缓存更新时间(秒)
    public $cache_dir      = '';      // 缓存文件存放目录  例: A_DIR.DS.'/tmp/views_cached/';
    public $force_compile  = FALSE;   // 总是强制编译 HTML
    public $compile_dir    = '';      // 编译文件存放目录  例: A_DIR.DS.'/tmp/views_compiled/';
    public $template       = array(); // (完整绝对路径) 模板文件名数组   
    public $template_out   = '';      // 缓存文件的内容, 通过 isCached() 函数赋值
    public $check_file     = TRUE;    // 检查模板文件是否存在
    
    private $_var           = array();  // 存放 assgin 函数,模板变量对应关系
    private $_vars          = array();  // temp
    private $_mhash         = ''; // 模板哈希
    private $_foreach       = array();  // 
    private $_current_file  = '';
    private $_expires       = 0;        // 被编译模板的过期时间
    private $_errorlevel    = 0;
    private $_nowtime       = NULL;     // 当前时间
    private $_foreachmark   = '';

    private $_temp_key      = array();  // 临时存放 foreach 里 key 的数组
    private $_temp_val      = array();  // 临时存放 foreach 里 item 的数组


    /**
     * 构造函数
     * 根据参数数组, 设置视图层的工作方式
     * @author Tom 090523
     */
    public function __construct( $aConfig = array() )
    {
    	$_REQUEST = array_merge( $_GET, $_POST );//自定义$_REQUEST接收数据类型
        if( !empty($aConfig) && is_array($aConfig) )
        {
            $this->inits($aConfig); // 数据初始化
        }
        $this->_errorlevel = error_reporting();
        $this->_nowtime    = CURRENT_TIMESTAMP; // 使用框架时间
        $this->_mhash      = md5( $_SERVER['SERVER_ADDR']. $_SERVER['SERVER_PORT'] );
    }
 

    /**
     * 根据数组参数初始化
     * 
     *    $aConfig['template_dir']   = A_DIR.DS.'/views/';
     *    $aConfig['compile_dir']    = A_DIR.DS.'/tmp/views_compiled/';
     *    $aConfig['cache_dir']      = A_DIR.DS.'/tmp/views_cached/';
     * 
     *    $aConfig['caching']        = FALSE;  // 是否开启缓存功能
     *    $aConfig['cache_lifetime'] = 10;     // 模板编译,及缓存更新时间(秒)
     * 
     *    $aConfig['direct_output']  = TRUE;   // 总开关.不进行编译,不进行缓存
     *    $aConfig['force_compile']  = FALSE;  // 总是强制编译 HTML

     *    $aConfig['check_file']     = TRUE;   // 检查模板文件是否存在
     *
     * @param array $aConfig
     * @author Tom 090523
     */
    public function inits( $aConfig = array() )
    {
        foreach( $aConfig as $k => $v )
        {
            if( isset( $this->$k ) && $k{0} != '_' )
            {
                //echo "set ==> \$this->$k = $v </br/>";
                $this->$k = $v;
            }
        }
    }


    /**
     * 注册模板用的变量
     * @author Tom   090523
     * @param	$mTplVar	变量名, 或数组
     * @param	$value		变量值
     */
    public function assign( $mTplVar, $sValue = '' )
    {
        if( is_array($mTplVar) )
        {
            foreach( $mTplVar AS $k => &$v )
            {
                if( $k != '' )
                {
                    $this->_var[$k] = $v;
                }
            }
        }
        else
        {
            if( $mTplVar != '' )
            {
                $this->_var[$mTplVar] = $sValue;
            }
        }
    }


    /**
     * 显示页面函数
     * @author Tom    090523
     * @param  string $sFileName
     * @param  string $sCacheId
     */
    public function display( $sFileName, $sCacheId = '' )
    {
        error_reporting(E_ALL ^ E_NOTICE);
        $out = $this->fetch( $sFileName, $sCacheId );
        error_reporting($this->_errorlevel);
        echo $out;
    }


    /**
     * 处理模板文件, 返回 PHP+HTML 数据 
     * @author Tom    090523
     * @param  string  $sFileName
     * @param  string  $sCacheId
     * @return string
     */
    public function fetch( $sFileNames, $sCacheId = '' )
    {
        $sFileName = $this->template_dir . DS . $sFileNames;
        if( $this->check_file == TRUE && !file_exists($sFileName) )
        {
            //die('a='.$sFileName);
            $sFileName = $this->template_dir_default . DS . $sFileNames;
            if( !file_exists($sFileName) )
            {
                die("Template file load failed : $sFileName");
            }
        }
        unset($sFileNames);
        if( $this->direct_output ) // 不使用缓存. 直接输出
        {
            $this->_current_file = $sFileName;
            $out = $this->_eval($this->fetch_str(file_get_contents($sFileName)));
        }
        else
        {
            // 开启了缓存开关, 并且存在 $cache_id. 则输出 cache 内容
            if( $this->caching && $sCacheId && $this->isCached( $sFileName, $sCacheId ) )
            {
                $out = $this->template_out;
            }
            else
            {
                if( !in_array($sFileName, $this->template) )
                {
                    $this->template[] = $sFileName;
                }
                $out = $this->make_compiled( $sFileName ); // 编译模板
                if ( $sCacheId ) // 创建缓存
                {
                    $cachename = basename($sFileName, strrchr($sFileName, '.')) . '_' . $sCacheId;
                    $data = serialize( array(
                    					'template' => $this->template, 
                    					'expires' => $this->_nowtime + $this->cache_lifetime, 
                    					'maketime' => $this->_nowtime ));
                    $out = str_replace("\r", '', $out);
                    $out = str_replace("\n\n", "\n", $out);
                    $hash_dir = $this->cache_dir . DS . substr(md5($cachename), 0, 1);
                    if( !is_dir($hash_dir) )
                    {
                        mkdir($hash_dir);
                    }
                    if( file_put_contents($hash_dir . DS . $cachename . '.php', '<?php exit;?>' . $data . $out, LOCK_EX) === FALSE )
                    {
                        trigger_error('can\'t write:' . $hash_dir . DS . $cachename . '.php');
                    }
                    $this->template = array();
                    unset($cachename,$data,$hash_dir);
                }
            }
        }
        //print_rr(h($out));exit;
        return $out; // 返回html数据
    }


    /**
     * 编译模板函数
     * @author Tom     090523
     * @param string   $sFilename
     * @return string
     */
    public function make_compiled( $sFilename )
    {
        //echo 'filename='.$sFilename;
        if( !is_dir( $this->compile_dir ) )
        {
            @mkdir($this->compile_dir);
        }
        $name = $this->compile_dir . DS . basename($sFilename) . '.php';
        if( $this->_expires )
        {
            $expires = $this->_expires - $this->cache_lifetime;
        }
        else
        {
            $filestat = @stat($name);
            $expires  = $filestat['mtime'];
        }
        // 检查被编译模板的过期时间
        $filestat = @stat($sFilename);

        /* 
         * 源文件未被更新的处理
         *   - 如果模板源(html)文件的最后修改时间, 早于 被编译文件(php)的时间(即:未更新)
         *   - require() 将编译文件引入
         */ 
        if( $filestat['mtime'] <= $expires && !$this->force_compile )
        {
            if( file_exists($name) )
            {
                $source = $this->_require($name);
                if( $source == '' )
                {
                    $expires = 0;
                }
            }
            else
            {
                $source = '';
                $expires = 0;
            }
        }
        // 如果被编译的文件已过期,或强制模板编译, 则: 开始编译模板
        if( $this->force_compile || $filestat['mtime'] > $expires )
        {
            $this->_current_file = $sFilename;
            $source = $this->fetch_str( file_get_contents($sFilename) );
            if( file_put_contents($name, $source, LOCK_EX) === FALSE )
            {
                trigger_error('can\'t write:' . $name);
            }
            $source = $this->_eval($source);
        }
        return $source;
    }


    /**
     * 处理字符串函数, 替换模板中的宏 {...}
     * @author Tom     090523
     * @param  string  $source
     * @return string
     */
    public function fetch_str( $source )
    {
        return preg_replace( "/{([^\}\{\n]*)}/e", "\$this->select('\\1');", $source );
    }


    /**
     * 判断模板文件是否被缓存, 并检查缓存文件的有效性
     * @author Tom    090523
     * @param string  $sFilename
     * @param string  $sCacheId
     * @return bool
     */
    public function isCached( $sFilename, $sCacheId = '' )
    {
        if( $this->caching == TRUE && $this->direct_output == FALSE )
        {
            $cachename = basename( $sFilename, strrchr($sFilename, '.') ) . '_' . $sCacheId;
            $hash_dir = $this->cache_dir . DS . substr(md5($cachename), 0, 1);
            if( $data = @file_get_contents($hash_dir . '/' . $cachename . '.php') )
            {
                $data = substr( $data, 13 ); // 忽略 <?php exit; ? >
                $pos  = strpos( $data, '<' ); // <html 标签开始的位置
                $paradata = substr( $data, 0, $pos ); // serialize 的数组信息
                $para     = @unserialize( $paradata ); 
                // 模板文件缓存截止时间,与系统当前时间比较
                if( $para === FALSE || $this->_nowtime > $para['expires'] )
                {
                    $this->caching = FALSE;
                    return FALSE;
                }
                $this->_expires = $para['expires'];
                $this->template_out = substr($data, $pos);
                foreach( $para['template'] AS $val )
                {
                    $stat = @stat($val); // 读取模板文件信息
                    // 模板文件缓存截止时间, 与模板文件最后修改时间比较
                    if( $para['maketime'] < $stat['mtime'] )
                    {
                        $this->caching = FALSE;
                        return FALSE;
                    }
                }
            }
            else
            {
                $this->caching = FALSE;
                return FALSE;
            }
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }


    /**
     * 处理各种 {} 宏标签
     *   调用关系  _eval() => fetch_str() => select() 
     * @author Tom    090523
     * @param  string $tag
     * @return string
     */
    public function select( $tag )
    {
        $tag = stripslashes( trim($tag) );
        if( empty($tag) )
        {
            return '{}';
        }
        elseif( $tag{0} == '*' && substr($tag, -1) == '*' ) // 注释部分
        {
            return '';
        }
        elseif( $tag{0} == '$' ) // 变量
        {
            return '<?php echo ' . $this->get_val(substr($tag, 1)) . '; ?>';
        }
        elseif( $tag{0} == '/' ) // 结束 tag
        {
            switch( substr($tag, 1) )
            {
                case 'if':
                    return '<?php endif; ?>';
                    break;

                case 'foreach':
                    if( $this->_foreachmark == 'foreachelse' )
                    {
                        $output = '<?php endif; unset($_from); ?>';
                    }
                    else
                    {
                        array_pop( $this->_patchstack );
                        $output = '<?php endforeach; endif; unset($_from); ?>';
                    }
                    $output .= "<?php \$this->pop_vars(); ?>";
                    return $output;
                    break;

                case 'literal':
                    return '';
                    break;

                default:
                    return '{'. $tag .'}';
                    break;
            }
        }
        else
        {
            $tag_sel = array_shift( explode(' ', $tag) );
            switch( $tag_sel )
            {
                case 'if':
                    return $this->_compile_if_tag( substr($tag, 3) );
                    break;

                case 'else':
                    return '<?php else: ?>';
                    break;

                case 'elseif':
                    return $this->_compile_if_tag( substr($tag, 7), TRUE );
                    break;

                case 'foreachelse':
                    $this->_foreachmark = 'foreachelse';
                    return '<?php endforeach; else: ?>';
                    break;

                case 'foreach':
                    $this->_foreachmark = 'foreach';
                    if( !isset($this->_patchstack) )
                    {
                        $this->_patchstack = array();
                    }
                    return $this->_compile_foreach_start( substr($tag, 8) );
                    break;

                case 'assign':
                    $t = $this->get_para( substr($tag, 7),0 );
                    if( $t['value']{0} == '$' )
                    { // 如果传进来的值是变量，就不用用引号
                        $tmp = '$this->assign(\'' . $t['var'] . '\',' . $t['value'] . ');';
                    }
                    else
                    {
                        $tmp = '$this->assign(\'' . $t['var'] . '\',\'' . addcslashes($t['value'], "'") . '\');';
                    }
                    // $tmp = $this->assign($t['var'], $t['value']);
                    return '<?php ' . $tmp . ' ?>';
                    break;

                case 'include':
                    $t = $this->get_para( substr($tag, 8), 0 );
                    return '<?php echo $this->fetch(' . "'$t[file]'" . '); ?>';
                    break;

                case 'insert_scripts':
                    $t = $this->get_para( substr($tag, 15), 0 );
                    return '<?php echo $this->smarty_insert_scripts(' . $this->make_array($t) . '); ?>';
                    break;

                case 'insert' :
                    $t = $this->get_para( substr($tag, 7), FALSE );
                    $out = "<?php \n" . '$k = ' . preg_replace("/(\'\\$[^,]+)/e" , "stripslashes(trim('\\1','\''));", var_export($t, TRUE)) . ";\n";
                    $out .= 'echo $this->_mhash . $k[\'name\'] . \'|\' . serialize($k) . $this->_mhash;' . "\n?>";
                    return $out;
                    break;

                case 'literal':
                    return '';
                    break;

                default:
                    return '{' . $tag . '}';
                    break;
            }
        }
    }


    /**
     * 处理smarty标签中的变量标签
     * @author Tom    090523
     * @access  public
     * @param   string     $val  (模板中的变量名,例:模板的 {$var1} 此处为 var1
     * 用法参考: 
     *    {$a|default:123}  => <?php echo empty($this->_var['a']) ? '123' : $this->_var['a']; ?>
     *    {$a[b]}           => <?php echo $this->_var['a'][''b'']; ?>
     */
    public function get_val( $val )
    {
        if( strrpos($val, '[') !== FALSE )
        {
            $val = preg_replace( "/\[([^\[\]]*)\]/eis", "'.'.str_replace('$','\$','\\1')", $val );
        }
        
        if( strrpos($val, '|') !== FALSE )
        {
            $moddb = explode( '|', $val );
            $val = array_shift( $moddb );
        }

        if( empty($val) )
        {
            return '';
        }

        if( strpos($val, '.$') !== FALSE )
        {
            $all = explode( '.$', $val );
            foreach( $all AS $key => $val )
            {
                $all[$key] = $key == 0 ? $this->make_var($val) : '['. $this->make_var($val) . ']';
            }
            $p = implode('', $all);
        }
        else
        {
            $p = $this->make_var($val);
        }

        if( !empty($moddb) ) // 解析 | 符号的限定符
        {
            foreach( $moddb AS $key => $mod )
            {
                $s = explode(':', $mod); // e.g: $mod = default:123
                switch( $s[0] )
                {
                    case 'escape':
                        $s[1] = trim($s[1], '"');
                        if( $s[1] == 'html' )
                        {
                            $p = 'htmlspecialchars(' . $p . ')';
                        }
                        elseif( $s[1] == 'url' )
                        {
                            $p = 'urlencode(' . $p . ')';
                        }
                        elseif( $s[1] == 'decode_url' )
                        {
                            $p = 'urldecode(' . $p . ')';
                        }
                        elseif( $s[1] == 'quotes' )
                        {
                            $p = 'addslashes(' . $p . ')';
                        }
                        elseif( $s[1] == 'u8_url' )
                        {
                            if( EC_CHARSET != 'utf-8' )
                            {
                                $p = 'urlencode(ecs_iconv("' . EC_CHARSET . '", "utf-8",' . $p . '))';
                            }
                            else
                            {
                                $p = 'urlencode(' . $p . ')';
                            }
                        }
                        elseif($s[1] == 'date')//日期转化
                        {
                        	$p = 'date("Y-m-d",strtotime('.$p.'))';
                        }
                        elseif($s[1] == 'money')//金钱转化,对2位精度小数取整
                        {
                            $s[2] = isset($s[2]) ? intval($s[2]) : 2;
                        	$p = 'number_format((floor('. $p .'*1000000)/1000000)' .','.$s[2].',".",",")';
                        }
                        else
                        {
                            $p = 'htmlspecialchars(' . $p . ')';
                        }
                        break;

                    case 'nl2br':
                        $p = 'nl2br(' . $p . ')';
                        break;

                    case 'default':
                        $s[1] = $s[1]{0} == '$' ?  $this->get_val(substr($s[1], 1)) : "'$s[1]'";
                        $p = '!isset(' . $p . ') ? ' . $s[1] . ' : ' . $p;
                        break;

                    case 'truncate':
                        $p = 'substr(' . $p . ",$s[1])";
                        break;

                    case 'strip_tags':
                        $p = 'strip_tags(' . $p . ')';
                        break;

                    default:
                        # code...
                        break;
                }
            }
        }
        return $p;
    }


    /**
     * 处理去掉$的字符串
     * @author Tom    090523
     * @param  string $val
     * @return string
     */
    public function make_var( $val )
    {
        if( strrpos($val, '.') === FALSE )
        {
            if( isset($this->_var[$val]) && isset($this->_patchstack[$val]) )
            {
                $val = $this->_patchstack[$val];
            }
            $p = '$this->_var[\'' . $val . '\']';
        }
        else
        {
            $t = explode( '.', $val );
            $_var_name = array_shift($t);
            if( isset($this->_var[$_var_name]) && isset($this->_patchstack[$_var_name]) )
            {
                $_var_name = $this->_patchstack[$_var_name];
            }
            if( $_var_name == 'smarty' )
            {
                 $p = $this->_compile_smarty_ref($t);
            }
            else
            {
                $p = '$this->_var[\'' . $_var_name . '\']';
            }
            foreach ($t AS $val)
            {
                $p .= '[\'' . $val . '\']';
            }
        }
        return $p;
    }



    // 处理insert外部函数/需要include运行的函数的调用数据
    public function get_para( $val, $type = 1 )
    {
        $pa = $this->str_trim( $val );
        foreach( $pa AS $value )
        {
            if( strrpos($value, '=') )
            {
                list($a, $b) = explode( '=', str_replace(array(' ', '"', "'", '&quot;'), '', $value) );
                if( $b{0} == '$' )
                {
                    if( $type )
                    {
                        eval( '$para[\'' . $a . '\']=' . $this->get_val(substr($b, 1)) . ';' );
                    }
                    else
                    {
                        $para[$a] = $this->get_val( substr($b, 1) );
                    }
                }
                else
                {
                    $para[$a] = $b;
                }
            }
        }
        return $para;
    }

    // 判断变量是否被注册并返回值
    public function &get_template_vars( $name = NULL )
    {
        if( empty($name) )
        {
            return $this->_var;
        }
        elseif( !empty($this->_var[$name]) )
        {
            return $this->_var[$name];
        }
        else
        {
            $_tmp = NULL;
            return $_tmp;
        }
    }

    /*
     * 处理if标签
     */
    public function _compile_if_tag( $tag_args, $elseif = FALSE )
    {
        $match = array();
        preg_match_all('/\-?\d+[\.\d]+|\'[^\'|\s]*\'|"[^"|\s]*"|[\$\w\.]+|!==|===|==|!=|<>|<<|>>|<=|>=|&&|\|\||\(|\)|,|\!|\^|=|&|<|>|~|\||\%|\+|\-|\/|\*|\@|\S/', $tag_args, $match);
        $tokens = $match[0];
        $token_count = array_count_values($tokens);
        if( !empty($token_count['(']) && $token_count['('] != $token_count[')'] )
        {
            // $this->_syntax_error('unbalanced parenthesis in if statement', E_USER_ERROR, __FILE__, __LINE__);
        }
        $iCountTokens = count($tokens);
        for( $i = 0, $count = $iCountTokens; $i < $count; $i++ )
        {
            $token = &$tokens[$i];
            if ($token[0] == '$')
            {
                $token = $this->get_val( substr($token, 1) );
            }
        }
        unset($iCountTokens);
        //print_rr($tokens);exit;
        if ($elseif)
        {
            return '<?php elseif (' . implode(' ', $tokens) . '): ?>';
        }
        else
        {
            return '<?php if (' . implode(' ', $tokens) . '): ?>';
        }
    }

    /*
     * 处理foreach标签
     */
    public function _compile_foreach_start( $tag_args )
    {
        $attrs = $this->get_para( $tag_args, 0 );
        //$arg_list = array();
        $from = $attrs['from'];
        if( isset($this->_var[$attrs['item']]) && !isset($this->_patchstack[$attrs['item']]) )
        {
            $this->_patchstack[$attrs['item']] = $attrs['item'] . '_' . 
            									 str_replace( array(' ', '.'), '_', microtime() );
            $attrs['item'] = $this->_patchstack[$attrs['item']];
        }
        else
        {
            $this->_patchstack[$attrs['item']] = $attrs['item'];
        }
        $item = $this->get_val($attrs['item']);

        if( !empty($attrs['key']) )
        {
            $key = $attrs['key'];
            $key_part = $this->get_val($key).' => ';
        }
        else
        {
            $key = NULL;
            $key_part = '';
        }

        if( !empty($attrs['name']) )
        {
            $name = $attrs['name'];
        }
        else
        {
            $name = NULL;
        }

        $output = '<?php ';
        $output .= "\$_from = $from; 
        		   if (!is_array(\$_from) && !is_object(\$_from)) 
        		   { settype(\$_from, 'array'); }; 
        		   \$this->push_vars('$attrs[key]', '$attrs[item]');";

        if( !empty($name) )
        {
            $foreach_props = "\$this->_foreach['$name']";
            $output .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
            $output .= "if ({$foreach_props}['total'] > 0):\n";
            $output .= "    foreach (\$_from AS $key_part$item):\n";
            $output .= "        {$foreach_props}['iteration']++;\n";
        }
        else
        {
            $output .= "if (count(\$_from)):\n";
            $output .= "    foreach (\$_from AS $key_part$item):\n";
        }
        return $output . '?>';
    }

    // 将 foreach 的 key, item 放入临时数组
    public function push_vars( $key, $val )
    {
        if( !empty($key) )
        {
            @array_push( $this->_temp_key, "\$this->_vars['$key']='" .$this->_vars[$key] . "';" );
        }
        if( !empty($val) )
        {
            @array_push( $this->_temp_val, "\$this->_vars['$val']='" .$this->_vars[$val] ."';" );
        }
    }



    /*
     * 弹出临时数组的最后一个
     */
    public function pop_vars()
    {
        $key = array_pop( $this->_temp_key );
        $val = array_pop( $this->_temp_val );
        if ( !empty($key) )
        {
            eval( $key );
        }
    }



    /*
     * 处理smarty开头的预定义变量
     */
    public function _compile_smarty_ref( &$indexes )
    {
        $_ref = $indexes[0];
        switch( $_ref )
        {
            case 'now':
                $compiled_ref = 'time()';
                break;

            case 'foreach':
                array_shift( $indexes );
                $_var		= $indexes[0];
                $_propname	= $indexes[1];
                switch( $_propname )
                {
                    case 'index':
                        array_shift( $indexes );
                        $compiled_ref = "(\$this->_foreach['$_var']['iteration'] - 1)";
                        break;

                    case 'first':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['iteration'] <= 1)";
                        break;

                    case 'last':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['iteration'] == \$this->_foreach['$_var']['total'])";
                        break;

                    case 'show':
                        array_shift($indexes);
                        $compiled_ref = "(\$this->_foreach['$_var']['total'] > 0)";
                        break;

                    default:
                        $compiled_ref = "\$this->_foreach['$_var']";
                        break;
                }
                break;

            case 'get':
                $compiled_ref = '$_GET';
                break;

            case 'post':
                $compiled_ref = '$_POST';
                break;

            case 'cookies':
                $compiled_ref = '$_COOKIE';
                break;

            case 'env':
                $compiled_ref = '$_ENV';
                break;

            case 'server':
                $compiled_ref = '$_SERVER';
                break;

            case 'request':
                $compiled_ref = '$_REQUEST';
                break;

            case 'session':
                $compiled_ref = '$_SESSION';
                break;

            default:
                // $this->_syntax_error('$smarty.' . $_ref . ' is an unknown reference', E_USER_ERROR, __FILE__, __LINE__);
                break;
        }
        array_shift( $indexes );
        return $compiled_ref;
    }

    /*
     * 插入JS文件(多个用,隔开)
     * @param	array $args	文件信息数组	etc. $args=array( 'filse' = './1.js, demo/test.js' );
     */
    public function smarty_insert_scripts( $args )
    {
        static $scripts = array();
        $arr = explode( ',', str_replace(' ', '', $args['files']) );
        $str = '';
        foreach( $arr AS $val )
        {
            if( in_array($val, $scripts) == FALSE )
            {
                $scripts[] = $val;
                if( $val{0} == '.' )
                {
                    $str .= '<script type="text/javascript" src="' . $val . '"></script>';
                }
                else
                {
                    $str .= '<script type="text/javascript" src="js/' . $val . '"></script>';
                }
            }
        }
        return $str;
    }



    /*
     * 处理动态内容
     * @param	string $name	etc. 'public function|serialize(array(1,2,3))'
     */
    public function insert_mod( $name )
    {
        list($fun, $para) = explode('|', $name);
        $para = unserialize( $para );
        $fun = 'insert_' . $fun;
        return $fun( $para );
    }


    /*
     * 去掉字符串等号两边的空格，并已空格为分离符返回数组
     * @param	string $str	字符串
     * @return	array
     */
    public function str_trim( $str )
    {
        while( strpos($str, '= ') != 0 )
        {
            $str = str_replace('= ', '=', $str);
        }
        while( strpos($str, ' =') != 0 )
        {
            $str = str_replace(' =', '=', $str);
        }
        return explode( ' ', trim($str) );
    }



    /*
     * 把内容作为PHP代码 编译
     * @param	string $content	要执行的代码
     * 
     * @return	string	执行后输出的内容
     */
    public function _eval( $content )
    {
        ob_start();
        @eval( '?' . '>' . trim($content) );
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }


    /*
     * 引用文件并返回文件执行后的输入
     * @param	string $filename	要引用的文件
     * 
     * @return	string	执行后输出的内容
     */
    public function _require( $filename )
    {
        ob_start();
        include $filename;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }


	/*
	 * 格式化数组为一个PHP可编译字符串
	 * @param	array $arr 	需要格式化的数组
	 * 
	 * @return string
	 */
    public function make_array( $arr )
    {
        $out = '';
        foreach( $arr AS $key => $val )
        {
            if( $val{0} == '$' )
            {
                $out .= $out ? ",'$key'=>$val" : "array('$key'=>$val";
            }
            else
            {
                $out .= $out ? ",'$key'=>'$val'" : "array('$key'=>'$val'";
            }
        }

        return $out . ')';
    }
}

?>
