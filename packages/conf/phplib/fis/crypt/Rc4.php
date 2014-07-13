<?php
/**
 * @ function: 用rc4算法生成加密串，除非提供密钥无法破解
 * @ params: $string 加解密的明文或密文   
 * @ params: $operation 加密或解密
 * @ params: $key 密钥 
 * @ example:  rc4('a'); 用默认的key对字符 a 进行加密
 * @ example:  rc4('a', 'DECODE'); 用默认的key对a进行解密
 * @ example:  rc4('a', 'ENCODE', 'abc'); 用指定的 key 'abc'对字符a进行加密
 * @ example:  rc4('a', 'ENCODE', 'abc', 15); 用指定的 key 'abc'对字符a进行加密, 设定有效期 15 秒
 */
class Fis_Crypt_Rc4{
    const KEY = 'BaiduRc4Key';

    static function rc4($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
        $ckey_length = 4;

        $key = md5($key != '' ? $key : self::KEY);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $box[$i] = $box[$i] ^ $box[$j];
            $box[$j] = $box[$i] ^ $box[$j];
            $box[$i] = $box[$i] ^ $box[$j];
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $box[$a] = $box[$a] ^ $box[$j];
            $box[$j] = $box[$a] ^ $box[$j];
            $box[$a] = $box[$a] ^ $box[$j];
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) 
                && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace('=', '', base64_encode($result));
        }
    }
}
