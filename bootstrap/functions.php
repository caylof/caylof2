<?php
/*
|--------------------------------------------------------------------------
| Define functions
|--------------------------------------------------------------------------
|
| some functions in the web application
|
*/


/*
|--------------------------------------------------------------------------
| Testing Function
|--------------------------------------------------------------------------
*/

/**
 * 友好地打印变量信息
 *
 * @param mixed $var 变量
 * @return void
 */
function print_var($var) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}


/*
|--------------------------------------------------------------------------
| String Function
|--------------------------------------------------------------------------
*/

/**
 * 正常显示json_encode方法返回结果中的中文字符串
 *
 * @param mixed $value 参照json_encode方法中的参数
 * @return string
 */
function json_encode_utf8($value) {
    $jsons = json_encode($value);
    return preg_replace(
        "#\\\u([0-9a-f]{4}+)#ie",
        "iconv('UCS-2', 'UTF-8', pack('H4', '\\1'))",
        $jsons
    );
}

/**
 * utf8编码字符串截取（无乱码）
 * 效果等同于PHP内置的 mb_substr 方法
 *
 * @param string $str 要处理的字符串
 * @param int $start 从哪个位置开始截取，第一个字符位置计0
 * @param int $length 要截取字符串的长度，默认从起始位置到末尾
 * @return string 截取后的字符串
 */
function substr_utf8($str, $start = 0, $length = null) {
    return join('', array_slice(
        preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY), $start, $length
    ));
}

/**
 * utf8编码获取字条串长度
 * 效果等同于PHP内置的 mb_strlen 方法
 *
 * @param string $str 要处理的字符串
 * @return int 字条串长度
 */
function strlen_utf8($str) {
    return count(preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY));
}

/**
 * 反转utf8的字符串
 * PHP内置的 strrev 方法可以实现字符串的反转，但不能解决中文字符串的反转问题
 *
 * @param string $str 要进行处理的字符串
 * @return string 反转后的字符串
 */
function strrev_utf8($str) {
    return join('', array_reverse(
        preg_split('//u', $str)
    ));
}

/**
 * 裁剪字符串，将超过指定长度的字符串进行裁剪
 *
 * @param string $str 要处理的字符串
 * @param int $length 裁剪的指定长度
 * @param string $mask 替代裁剪掉的字符串标志
 * @return string
 */
function str_cut($str, $length, $mask = '...') {
    if (strlen_utf8($str) > $length) {
        return substr_utf8($str, 0, $length) . $mask;
    }
    return $str;
}

/**
 * Interpolates context values into the message placeholders.
 *
 +--------------------------------------------------------------------
   for example:

   $message = "User {username} created"; // 变量必须被{}包围且不能有空格
   $context = array('username' => 'caylof'); // key值与要替换的变量保持一致
   echo interpolate($message, $context);

   this will echo "User caylof created".
 --------------------------------------------------------------------+
 *
 * @param string $message
 * @param array $context
 * @return string
 */
function interpolate($message, array $context = array()) {
  // build a replacement array with braces around the context keys
  $replace = array();
  foreach ($context as $key => $val) {
      $replace['{' . $key . '}'] = $val;
  }

  // interpolate replacement values into the message and return
  return strtr($message, $replace);
}

/**
 * 将文本与密码进行异或运算
 * 供加密函数 caylof_encrypt 和 解密函数 caylof_decrypt 内部调用
 *
 * @param string $text 文本
 * @param string $key 密码
 * @return string
 */
function caylof_key($text, $key) {
    $index = 0;
    $key = md5($key);
    $temp = '';
    $text_len = strlen($text);
    $key_len = strlen($key);
    for ($i = 0; $i < $text_len; ++$i) {
        $index = $index == $key_len ? 0 : $index;
        $temp .= $text[$i] ^ $key[$index++];
    }
    return $temp;
}

/**
 * 加密函数
 * 将明文修改成（md5[i]+明文[i]）的格式，再进行caylof_key()操作，最后进行base64编码
 *
 * @param string $text 明文
 * @param string $key 密钥
 * @param string 密文
 */
function caylof_encrypt($text, $key = '&%#@!$') {
    $index = 0;
    $md5 = md5(rand(0, 27000));
    $temp = '';
    $text_len = strlen($text);
    $md5_len = strlen($key);
    for ($i = 0; $i < $text_len; ++$i, ++$index) {
        $index = $index == $md5_len ? 0 : $index;
        $temp .= $md5[$index] . ($text[$i] ^ $md5[$index]);
    }
    return base64_encode(caylof_key($temp, $key));
}

/**
 * 解密函数
 * 对密文进行base64解码，再进行caylof_key()操作，得到的是（md5[i]+明文[i]）的格式，最后进行异或运算提取其中的明文部分
 *
 * @param string $mtext 密文
 * @param string $key 密钥
 * @param string 明文
 */
function caylof_decrypt($mtext, $key = '&%#@!$') {
    $mtext = caylof_key(base64_decode($mtext), $key);
    $temp = '';
    $mtext_len = strlen($mtext);
    for ($i = 0; $i < $mtext_len; ++$i) {
        $md5 = $mtext[$i];
        $temp .= $mtext[++$i] ^ $md5;
    }
    return $temp;
}

/**
 * mcrypt_encrypt加密
 * 简直封装 mcrypt_encrypt 加密处理过程，方便更好的进行加密使用
 * PHP需要 mcrypt 支持
 *
 * @param string $str 要进行加密处理的字符串（明文）
 * @param string $key 密钥
 * @param string $cipher 加密类型
 * @param string $mode 加密模式
 * @return string 密文
 */
function caylof_mcrypt_encrypt($str, $key = '&%#@!$', $cipher = MCRYPT_DES, $mode = MCRYPT_MODE_ECB) {
    $iv_size = mcrypt_get_iv_size($cipher, $mode);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
    return mcrypt_encrypt($cipher, $key, $str, $mode, $iv);
}

/**
 * mcrypt_decrypt解密
 * 简直封装 mcrypt_decrypt 解密处理过程，方便更好的进行加密使用
 * PHP需要 mcrypt 支持
 *
 * @param string $mstr 密文
 * @param string $key 密钥
 * @param string $cipher 加密类型
 * @param string $mode 加密模式
 * @return string 明文
 */
function caylof_mcrypt_decrypt($mstr, $key = '&%#@!$', $cipher = MCRYPT_DES, $mode = MCRYPT_MODE_ECB) {
    $iv_size = mcrypt_get_iv_size($cipher, $mode);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);
    return mcrypt_decrypt($cipher, $key, $mstr, $mode, $iv);
}


/*
|--------------------------------------------------------------------------
| Array Function
|--------------------------------------------------------------------------
*/

/**
 * 根据某（几）列对二维数组进行排序
 * 排序成功后会直接影响到要排序的数组
 * 参考PHP手册中的 array_multisort 方法
 *
 +----------------------------------------------------------------------------
  for example:
 
  $data[] = array('volume' => 67, 'edition' => 2);
  $data[] = array('volume' => 86, 'edition' => 1);
  $data[] = array('volume' => 85, 'edition' => 6);
  $data[] = array('volume' => 98, 'edition' => 2);
  $data[] = array('volume' => 86, 'edition' => 6);
  $data[] = array('volume' => 67, 'edition' => 7);
  array_sort($data, array('volume', 'edition'), array(SORT_DESC, SORT_ASC));
 
  上面的例子表示将$data数组按照volume降序（优先）、edition升序进行排序。
 ----------------------------------------------------------------------------+
 *
 * @param array $arr 要处理的二维数组，这通常是通过循环从数据库取得的结果格式
 * @param array $cols 要根据某（几）列来排序，这通常是数据库表中某（几）列的列名
 * @param array $types 对应要排序列的排序顺序标志，由常量 SORT_ASC 和 SORT_DESC 构成
 * @return bool 成功时返回 true， 或者在失败时返回 false
 */
function array_sort(&$arr, $cols, $types) {
    $sort_col = array();
    foreach ($cols as $col) {
        $tmp = array();
        foreach ($arr as $key => $row) {
            $tmp[$key] = $row[$col];
        }
        $sort_col[] = $tmp;
    }

    $args = array();
    foreach ($sort_col as $idx => $col) {
        $args[] = $col;
        $args[] = isset($types[$idx]) ? $types[$idx] : SORT_ASC;
    }
    $args[] = &$arr;

    return call_user_func_array('array_multisort', $args);
}

/**
 * 实现无限分类（采用递归）
 * 分类层级信息用新增节点(level)来表示
 *
 +---------------------------------------------------------------------------------
  for example:
 
  $items[] = array('id' => 1, 'pid' => 0, 'name' => 'PHP');
  $items[] = array('id' => 2, 'pid' => 0, 'name' => 'Java');
  $items[] = array('id' => 3, 'pid' => 0, 'name' => 'Python');
  $items[] = array('id' => 4, 'pid' => 1, 'name' => 'PDO');
  $items[] = array('id' => 5, 'pid' => 2, 'name' => 'JSP');
  $items[] = array('id' => 6, 'pid' => 4, 'name' => 'PDOStatement');
 
  $tree = tree_level($items);
  采用分类后会生成如下结果：
  $tree = array(
      array('id' => 1, 'pid' => 0, 'name' => 'PHP', 'level' => 0),
      array('id' => 4, 'pid' => 1, 'name' => 'PDO', 'level' => 1),
      array('id' => 6, 'pid' => 4, 'name' => 'PDOStatement', 'level' => 2),
      array('id' => 2, 'pid' => 0, 'name' => 'Java', 'level' => 0),
      array('id' => 5, 'pid' => 2, 'name' => 'JSP', 'level' => 1),
      array('id' => 3, 'pid' => 0, 'name' => 'Python', 'level' => 0)
  );
 ---------------------------------------------------------------------------------+
 *
 * @param array $arr 要处理的分类数据，这通常是通过循环从数据库取得的结果格式
 * @param array $config array('node' => '节点名称', 'pNode' => '父节点名称') 分类基本配置信息
 * @param int $pid 父节点值，顶级默认为0
 * @param int $level 节点所在的层级，默认为0
 * @return array 分类处理后的数据，数据中会新增 level 项 代表所处层级
 */
function tree_level($arr, $config = array('node' => 'id', 'pNode' => 'pid'), $pid = 0, $level = 0) {
    static $list = array();
    foreach ($arr as $v) {
        if ($v[$config['pNode']] == $pid) {
            $v['level'] = $level;
            $list[] = $v;
            tree_level($arr, $config, $v[$config['node']], $level + 1);
        }
    }
    return $list;
}

/**
 * 实现无限分类（采用递归）
 * 分类层级信息用新增节点(children)来表示
 *
 +---------------------------------------------------------------------------------
  for example:
 
  $items[] = array('id' => 1, 'pid' => 0, 'name' => 'PHP');
  $items[] = array('id' => 2, 'pid' => 0, 'name' => 'Java');
  $items[] = array('id' => 3, 'pid' => 0, 'name' => 'Python');
  $items[] = array('id' => 4, 'pid' => 1, 'name' => 'PDO');
  $items[] = array('id' => 5, 'pid' => 2, 'name' => 'JSP');
  $items[] = array('id' => 6, 'pid' => 4, 'name' => 'PDOStatement');
 
  $tree = tree_children($items);
  采用分类后会生成如下结果：
  $tree = array(
      array('id' => 1, 'pid' => 0, 'name' => 'PHP', 'children' => array(
          array('id' => 4, 'pid' => 1, 'name' => 'PDO', 'children' => array(
              array('id' => 6, 'pid' => 4, 'name' => 'PDOStatement', 'children' => array())
          ))
      )),
      array('id' => 2, 'pid' => 0, 'name' => 'Java', 'children' => array(
          array('id' => 5, 'pid' => 2, 'name' => 'JSP', 'children' => array())
      )),
      array('id' => 3, 'pid' => 0, 'name' => 'Python', 'children' => array())
  );
 ---------------------------------------------------------------------------------+
 *
 * @param array $arr 要处理的分类数据，这通常是通过循环从数据库取得的结果格式
 * @param array $config array('node' => '节点名称', 'pNode' => '父节点名称') 分类基本配置信息
 * @param int $pid 父节点值，顶级默认为0
 * @param array $tree 用来存储结果
 * @return array 分类处理后的数据，数据中会新增 children 项 代表所有子节点
 */
function tree_children($arr, $config = array('node' => 'id', 'pNode' => 'pid'), $pid = 0, $tree = array()) {
    foreach ($arr as $val) {
        if ($val[$config['pNode']] == $pid) {
            $val['children'] = tree_children($arr, $config, $val[$config['node']]);
            $tree[] = $val;
        }
    }
    return $tree;
}


/*
|--------------------------------------------------------------------------
| Other Function
|--------------------------------------------------------------------------
*/

/**
 * 获取客户端IP
 */
function get_client_ip(){
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
            $ip = getenv("HTTP_CLIENT_IP");
    else
        if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"),
            "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR");
        else
            if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR");
            else
                if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'],
                    "unknown")) $ip = $_SERVER['REMOTE_ADDR'];
                else  $ip = "unknown";
    return $ip;
}

/**
 * 获取服务器IP
 */
function get_server_ip(){
	  static $serverip = NULL;
	  if ($serverip !== NULL){
		  return $serverip;
	  }
	  if (isset($_SERVER)){
		  if (isset($_SERVER['SERVER_ADDR'])){
			  $serverip = $_SERVER['SERVER_ADDR'];
		  }else{
			  $serverip = '0.0.0.0';
		  }
	  }else{
		  $serverip = getenv('SERVER_ADDR');
	  }
	  return $serverip;
}

/**
 * 获取客户端操作系统
 */
function get_client_os() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return 'Unknown';
    }
    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $os = '';
    if (strpos($agent, 'win') !== false) {
        if (strpos($agent, 'nt 5.1') !== false) {
            $os = 'Windows XP';
        } elseif (strpos($agent, 'nt 5.2') !== false) {
            $os = 'Windows 2003';
        } elseif (strpos($agent, 'nt 5.0') !== false) {
            $os = 'Windows 2000';
        } elseif (strpos($agent, 'nt 6.0') !== false) {
            $os = 'Windows Vista';
        } elseif (strpos($agent, 'nt') !== false) {
            $os = 'Windows NT';
        } elseif (strpos($agent, 'win 9x') !== false && strpos($agent, '4.90') !== false) {
            $os = 'Windows ME';
        } elseif (strpos($agent, '98') !== false) {
            $os = 'Windows 98';
        } elseif (strpos($agent, '95') !== false) {
            $os = 'Windows 95';
        } elseif (strpos($agent, '32') !== false) {
            $os = 'Windows 32';
        } elseif (strpos($agent, 'ce') !== false) {
            $os = 'Windows CE';
        }
    } elseif (strpos($agent, 'linux') !== false) {
        $os = 'Linux';
    } elseif (strpos($agent, 'unix') !== false) {
        $os = 'Unix';
    } elseif (strpos($agent, 'sun') !== false && strpos($agent, 'os') !== false) {
        $os = 'SunOS';
    } elseif (strpos($agent, 'ibm') !== false && strpos($agent, 'os') !== false) {
        $os = 'IBM OS/2';
    } elseif (strpos($agent, 'mac') !== false && strpos($agent, 'pc') !== false) {
        $os = 'Macintosh';
    } elseif (strpos($agent, 'powerpc') !== false) {
        $os = 'PowerPC';
    } elseif (strpos($agent, 'aix') !== false) {
        $os = 'AIX';
    } elseif (strpos($agent, 'hpux') !== false) {
        $os = 'HPUX';
    } elseif (strpos($agent, 'netbsd') !== false) {
        $os = 'NetBSD';
    } elseif (strpos($agent, 'bsd') !== false) {
        $os = 'BSD';
    } elseif (strpos($agent, 'osf1') !== false) {
        $os = 'OSF1';
    } elseif (strpos($agent, 'irix') !== false) {
        $os = 'IRIX';
    } elseif (strpos($agent, 'freebsd') !== false) {
        $os = 'FreeBSD';
    } elseif (strpos($agent, 'teleport') !== false) {
        $os = 'teleport';
    } elseif (strpos($agent, 'flashget') !== false) {
        $os = 'flashget';
    } elseif (strpos($agent, 'webzip') !== false) {
        $os = 'webzip';
    } elseif (strpos($agent, 'offline') !== false) {
        $os = 'offline';
    } else {
        $os = 'Unknown';
    }

    return $os;
}

/**
 * 获取客户端浏览器信息
 */
function get_client_browser($agent=''){
    if (empty($agent)){
        $agent = $_SERVER['HTTP_USER_AGENT'];
    }
	if(empty($agent)){
		return '';
	}
    $browser = '';
    $browser_ver = '';
    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)){
        $browser     = 'Internet Explorer';
        $browser_ver = $regs[1];
    }elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)){
        $browser     = 'FireFox';
        $browser_ver = $regs[1];
    }elseif (preg_match('/Maxthon/i', $agent, $regs)){
        $browser     = '(Internet Explorer ' .$browser_ver. ') Maxthon';
        $browser_ver = '';
    }elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)){
        $browser     = 'Opera';
        $browser_ver = $regs[1];
    }elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)){
        $browser     = 'OmniWeb';
        $browser_ver = $regs[2];
    }elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)){
        $browser     = 'Netscape';
        $browser_ver = $regs[2];
    }elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs)){
        $browser     = 'Safari';
        $browser_ver = $regs[1];
    }elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)){
        $browser     = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
        $browser_ver = $regs[1];
    }elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)){
        $browser     = 'Lynx';
        $browser_ver = $regs[1];
    }elseif (preg_match('/UCWEB([^\s|]+)\//i', $agent, $regs)){
        $browser = 'UCWEB(phone)';
		$ver=explode('/',$regs[1]);
        $browser_ver =$ver[0];
    }elseif (preg_match('/AppleWebKit\/([^\s]+)/i', $agent, $regs)){
        $browser     = 'WebKit(phone)';
        $browser_ver = $regs[1];
    }elseif (preg_match('/CoolpadWebkit\/([^\s]+)/i', $agent, $regs)){
        $browser     = 'CoolpadWebkit(phone)';
        $browser_ver = $regs[1];
    }elseif (preg_match('/rv\:([^\s]+)/i', $agent, $regs)){
        $browser     = 'rv';
        $browser_ver = $regs[1];
    }elseif (preg_match('/AppleWebKit([^\s|]+)/i', $agent, $regs)){
        $browser = 'AppleWebKit(phone)';
        $browser_ver =$regs[1];
    }elseif (preg_match('/Safari/i', $agent, $regs)){
        $browser = 'Safari(phone)';
        $browser_ver =$regs[1];
    }elseif (preg_match('/Mozilla\/([^\s]+)/i', $agent, $regs)){
        $browser = 'Mozilla';
        $browser_ver =$regs[1];
    }

    if (!empty($browser)){
       return addslashes($browser . ' ' . $browser_ver);
    }else{
        return 'Unknow browser';
    }
}
