<?php
/**
 * @file FcryptUtil.php
 * @author liaohuiqin01
 * @date 2013/05/13 20:25:23
 * @brief 从地图拿过来，poi 加解密算法;
 */


class SF_Wrapper_KSArch_FcryptUtil {
	const UID_CRYPT_MAGIC_NUM = 0x493907bf;
	const UID_CRYPT_SECRET_KEY = 'mapui.2009.04.14';
	const ENCRYPT_KEY_PROMO_ID = '*HhzsTsl#';

	public static function uidDecode($str){
		$ret = fcrypt_hstr_2id(SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_SECRET_KEY, $str);
		if (sizeof($ret) == 2){
			$uid = 0;
			$uid = (0xFFFFFFFF - SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_MAGIC_NUM + $ret[1]) % 0xFFFFFFFF;
			$uid = ($uid << 32) + $ret[0];
			$new_uid = sprintf("%u", $uid);
			return $new_uid;
		}
		return false;
	}

	/**
	 * uid加密, 推荐用该函数, 通过将64位uid分解为高32位uid，和低32位uid实现
	 * @param $uid1        高32位uid
	 *        $uid2        低32位uid
	 * @return 正确返回string，错误返回false
	 */
	public static function uidEncodeNew($uid1, $uid2){
		$uid = ($uid1 << 32) + $uid2;
		$uid = intval($uid);
		$uid1 = $uid & 0xFFFFFFFF;
		if ($uid < 0) {
			$uid = (~$uid) + 1;
			$tmp = $uid >> 32;
			$tmp = ~$tmp;
			$tmp = $tmp & 0xFFFFFFFF;
		} else {
			$tmp = $uid >> 32;
		}
		$uid2 = ($tmp + SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_MAGIC_NUM) % 0xFFFFFFFF;
		$str = fcrypt_id_2hstr(SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_SECRET_KEY, intval($uid1), intval($uid2));
		return $str;
	}

	/**
	 * uid加密, 推荐用该函数, 通过将64位uid分解为高32位uid，和低32位uid实现
	 * @param $uid        uid
	 * @return 正确返回string，错误返回false
	 */
	public static function uidEncode1($uid)
	{
		$uid = bcadd($uid, 0);
		$uid1 = bcmod($uid, 1<<32);
		$uid2 = bcmod(bcadd(bcdiv($uid, 1<<32), SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_MAGIC_NUM), 0xFFFFFFFF);
		$str = fcrypt_id_2hstr(SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_SECRET_KEY, (int)$uid1, (int)$uid2);
		return $str;
	}

	/**
	 * uid解密
	 * @param $str
	 * @return 正确返回uid string,错误返回false;
	 */
	public static function uidDecode1($str){
		$ret = fcrypt_hstr_2id(SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_SECRET_KEY, $str);
		if (sizeof($ret) == 2){
			$uid = 0;
			$uid = bcmod(bcadd(bcsub(0xFFFFFFFF, SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_MAGIC_NUM), $ret[1]), 0xFFFFFFFF);
			$uid = bcadd(bcmul($uid, 1<<32), $ret[0]);
			return $uid;
		}
		return false;
	}

	/**
	 * uid加密, 不推荐，因为64位的uid值容易传
	 * @param $uid
	 * @return 正确返回string，错误返回false
	 */
	public static function uidEncode($uid){
		$uid = intval($uid);
		$uid1 = $uid & 0xFFFFFFFF;
		if ($uid < 0) {
			$uid = (~$uid) + 1;
			$tmp = $uid >> 32;
			$tmp = ~$tmp;
			$tmp = $tmp & 0xFFFFFFFF;
		} else {
			$tmp = $uid >> 32;
		}
		$uid2 = ($tmp + SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_MAGIC_NUM) % 0xFFFFFFFF;
		$str = fcrypt_id_2hstr(SF_Wrapper_KSArch_FcryptUtil::UID_CRYPT_SECRET_KEY, intval($uid1), intval($uid2));
		return $str;
	}

	/**
	 * level级别下，一张图片所代表的宽度，单位为m
	 * @param $level
	 * @return int
	 */
	public static function getZoomUnit($level){
		return 256 * pow(2, 18 - $level);
	}

	/**
	 * 为了跨域而设置的callback，往往与json一起出现
	 * @param $arr array
	 * @param $callback
	 * @return string
	 */
	public static function myJsonEncode($arr, $callback = ''){
		$json = json_encode($arr);
		if (!empty($callback)){
			$json = "$callback && $callback ($json)";
		}
		return $json;
	}

	/**
	 * 计算两个向量之间的夹角
	 * @param $os array 第一个向量的起点
	 * @param $s  array 第一个向量的终点
	 * @param $oe array 第二个向量的起点  
	 * @param $e  array 第二个向量的终点
	 * @return 两个向量间的弧度
	 */
	 public static function angle($os, $s, $oe, $e) {
		$cosfi = 0.0;
		$fi = 0.0;
		$norm = 0.0;

		$dsx = $s['x'] - $os['x'];
		$dsy = $s['y'] - $os['y'];
		$dex = $e['x'] - $oe['x'];
		$dey = $e['y'] - $oe['y'];

		$cosfi = $dsx * $dex + $dsy * $dey;
		$norm = ($dsx * $dsx + $dsy * $dsy) * ($dex * $dex + $dey * $dey);
		if (abs($norm) < 1E-13 && abs($cosfi) < 1E-13) {
			return 0;
		}
		$cosfi /= sqrt($norm);

		if ($cosfi >= 1.0)
		return 0;
		if ($cosfi <= -1.0)
		return -3.1415926;

		$fi = acos($cosfi);
		if ($dsx * $dey - $dsy * $dex > 0)
		return $fi; // 说明矢量os 在矢量 oe的顺时针方向
		return -$fi;
	}
}

