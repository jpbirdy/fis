<?php
/**
 * @name Groupon_Api_Web_Library_Util
 * @desc APP公共工具类
 * @author 陈逸斌(chenyibin@baidu.com)
 */

class SF_Wrapper_KSArch_Util{
	/**
	 * PS层参数处理
	 * int类型
	 * 
	 * @param unknown_type $param
	 * @return unknown_type
	 */
	public static function paramIntVal($param){
		return intval($param);
	}
	/**
	 * PS层参数过滤
	 * String类型
	 * 
	 * @param unknown_type $param
	 * @return unknown_type
	 */
	public static function paramStringVal($param){
		//替换掉出现的各种异常字符 = ' ()
		$param = str_replace('=','',$param);
		$param = str_replace('\'','\\\'',$param);
		$param = str_replace('(','',$param);
		$param = str_replace(')','',$param);
		return ($param);
	}
	
	
	/**
	 *unicode编码方法
	 *@param string $str 需要编码的string
	 *@param string $in_charset 输入的编码格式
	 *@return string unicode编码的字符串
	 */
	public static function ues_encode($str, $in_charset = "utf-8") {
	    $buf = '';
	    $u = iconv($in_charset, "UCS-2BE", $str);
	    for ($i = 0, $n = strlen($u); $i < strlen($u); $i += 2) {
	        $tmp = ord($u[$i]);
	        if ($tmp === 0) {
	            $buf .= $u[$i+1];
	        } else {
	            $buf .= sprintf("\\u%02x", ord($u[$i]));
	            $buf .= sprintf("%02x", ord($u[$i+1]));
	        }
	    }
	
	    return $buf;
	}
	/**
	 *unicode解码方法
	 *@param string $str 需要解码的string
	 *@param string $out_charset 输出的编码格式
	 *@return string unicode解码的字符串
	 */
	public static function ues_decode($str, $out_charset = "utf-8") {
	    $buf = '';
	    $n = strlen($str);
	    for ($i = 0; $i < $n; $i++) {
	        if (strtolower(substr($str, $i, 2)) !== "\\u") {
	            $buf .= chr(0);
	            $buf .= $str[$i];
	            continue;
	        }
	
	        $hex = substr($str, $i+2, 4);
	        if (!preg_match("/^[0-9a-f]{4}$/i", $hex)) {
	            $buf .= $str[$i];
	            continue;
	        }	
	        $buf .= chr(intval(substr($hex, 0, 2), 16));
	        $buf .= chr(intval(substr($hex, 2, 2), 16));
	        $i += 5;
	    }
	    return iconv("UCS-2BE", $out_charset, $buf);
	}
	
	
	public static function getIntConf($iConfValue, $iMin, $iMax, $iDefault) {
		$iRetValue = 0;
		if ($iConfValue) {
			$iRetValue = intval($iConfValue);
			if ($iRetValue < $iMin || $iRetValue > $iMax) {
				$iRetValue = $iDefault;
			}
		} else {
			$iRetValue = $iDefault;
		}
		return $iRetValue;
	}
	/**
	 * 获取唯一相对随机10位数字的算法
	 * 传入数字必须为唯一的ID，coupon_id
	 * 
	 * 获取的数据种子为time()+唯一ID
	 * 
	 * 
	 * 钦明提供：太nice了
	 *
	 * @param  int $num 自增长id
	 *
	 * @return int 10位数字，完全不重复 如果签名失败
	 */
	public static function sign($seed)
	{
        $arrShift = array(14, 6, 25, 20, 29, 22, 9, 27, 15, 18, 23, 21, 24, 13, 2, 4, 8, 26, 1, 28, 7, 3, 10, 19, 11, 16, 0, 12, 17, 5);
        $arrReplace = array(20, 13, 19, 28, 16, 27, 0, 8, 24, 14, 3, 23, 17, 11, 26, 30, 22, 9, 25, 1, 7, 6, 29, 21, 18, 12, 5, 31, 10, 15, 4, 2);
        $s = $seed + ((time() - 1356019200) & 1073740800);
        
        for ($round = 0; $round < 7; ++$round) {
            //映射替换
            $tmp = $s;
            for ($i = 0; $i < 6; ++$i) {
                $u = ($tmp >> ($i * 5)) & 31;
                $BIT_MASK = $arrReplace[$u] << ($i * 5);
                $BIT_MASK_CLEAR = ~(31 << ($i * 5));
                $s = ($s & $BIT_MASK_CLEAR) | $BIT_MASK;
            }
            
            //位置交换
            $tmp = $s;
            for ($i = 0; $i < 30; ++$i) {
                $BIT_MASK = ($tmp & (1 << $arrShift[$i])) > 0 ? (1 << $i) : 0;
                $BIT_MASK_CLEAR = ~(1 << $i);
                $s = ($s & $BIT_MASK_CLEAR) | $BIT_MASK;
            }
        }
        
        //最高4bit随机
        $s = $s | (mt_rand(1, 8) << 30);
        return $s;
	}

	/**
	 * 获取唯一的低于15位用户订单号 
	 * 
	 * @param  $num 可用户订单号ID
	 *
	 * @return int 15位数字，完全不重复 如果签名失败，返回-1
	 */
	public static function signOrder($num){
		$code10 = self::sign($num);
		$head = rand(10000,99999);
		$code = $head * 1000000000 + $code10;
		return $code;
	}

	/**
	 * 获取某一列的最大值
	 *
	 * @param $arr
	 * @param $key
	 * @return unknown_type
	 */
	public static function arrKeyMax($arr,$key){
		$arr = self::sortByCol($arr,$key,SORT_DESC);
		if(count($arr)<=0){
			return false;
		}
		$popArr =  current($arr);
		$keyVal  = $popArr[$key];
		unset($popArr);
		return $keyVal;
	}

	/**
	 * 获取某一列的最小值
	 *
	 * @param $arr
	 * @param $key
	 * @return unknown_type
	 */
	public static function arrKeyMin($arr,$key){
		$arr = self::sortByCol($arr,$key,SORT_ASC);
		if(count($arr)<=0){
			return false;
		}
		$popArr =  $arr[0];
		$keyVal  = $popArr[$key];
		unset($popArr);
		return $keyVal;
	}
	/**
	 * 根据指定的键对数组排序
	 *
	 * 用法：
	 * @code php
	 * @param array $array 要排序的数组
	 * @param string $keyname 排序的键
	 * @param int $dir 排序方向 SORT_ASC从大到小排序
	 *
	 * @return array 排序后的数组
	 */
	public static function randomArray($array)
	{
		return self::sortByMultiCols($array, array($keyname => $dir));
	}
	/**
	 * 根据指定的键对数组排序
	 *
	 * 用法：
	 * @code php
	 * @param array $array 要排序的数组
	 * @param string $keyname 排序的键
	 * @param int $dir 排序方向 SORT_ASC从大到小排序
	 *
	 * @return array 排序后的数组
	 */
	public static function sortByCol($array, $keyname, $dir = SORT_ASC)
	{
		return self::sortByMultiCols($array, 
			array(
				$keyname => $dir,
			)
		);
	}

	/**
	 * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
	 *
	 * 用法：
	 * @code php
	 * $rows = Gpw_Util::sortByMultiCols($rows, array(
	 *           'col1' => SORT_ASC,
	 *           'col2' => SORT_DESC,
	 * ));
	 * @endcode
	 *
	 * @param array $rowset 要排序的数组
	 * @param array $args 排序的键
	 *
	 * @return array 排序后的数组
	 */
	public static function sortByMultiCols($rowset, $args)
	{
		$sortArray = array();
		$sortRule = '';
		foreach ($args as $sortField => $sortDir)
		{
			foreach ($rowset as $offset => $row)
			{
				$sortArray[$sortField][$offset] = $row[$sortField];
			}
			$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
		}

		if (empty($sortArray) || empty($sortRule))
		{
			return $rowset;
		}

		eval('array_multisort(' . $sortRule . '$rowset);');
		return $rowset;
	}

}
