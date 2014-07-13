<?php
/**
 * author: jiachenhui01@baidu.com
 * Date: 14-1-3
 * Time: 下午2:18
 */
    class SF_Wrapper_KSArch_IdAllocMockSF  extends SF_Wrapper_KSArch_Wrapper
    {
        private $_sock = null;
        public function __construct()
        {
            $this->_sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            $port = '56789';
            $ip = '10.26.144.19';
            $connection = socket_connect($this->_sock, $ip, $port);
        }

        static function getClass()
        {
            return __CLASS__;
        }

        function buildNshead($param)
        {
            $nsheadArr = array(
                'id'        => 0,
                'version'   => 0,
                'log_id'    => rand(),
                'provider'  => str_pad("", 16, "\0", STR_PAD_BOTH),
                'magic_num' => 0xfb709394,
                'reserved'  => 0,
                'body_len'  => 0
            );

            foreach ($param as $key => $value)
            {
                if (isset($nsheadArr[$key]))
                {
                    $nsheadArr[$key] = $value;
                }
            }

            $nshead  = "";
            $nshead  = pack("L*", (($nsheadArr['version'] << 16) + ($nsheadArr['id'])),
                $nsheadArr['log_id']);
            $nshead .= $nsheadArr['provider'];
            $nshead .= pack("L*", $nsheadArr['magic_num'], $nsheadArr['reserved']);
            $nshead .= pack("L", $nsheadArr['body_len']);

            return $nshead;
        }

        function splitNshead($head, $get_buf = true)
        {
            $retArr = array(
                'id'        => 0,
                'version'   => 0,
                'log_id'    => 0,
                'provider'  => "",
                'magic_num' => 0,
                'reserved'  => 0,
                'body_len'  => 0,
                'buf'       => ""
            );

            $ret = unpack("v1id/v1version/I1log_id", substr($head, 0, 8));
            foreach ($ret as $key => $value)
            {
                $retArr[$key] = $value;
            }
            $retArr['provider'] = substr($head, 8, 16);
            $ret = unpack("I1magic_num/I1reserverd/I1body_len", substr($head, 24, 12));
            foreach ($ret as $key => $value)
            {
                $retArr[$key] = $value;
            }

            //36是nshead_t结构体大小
            if ($get_buf)
            {
                $retArr['buf'] = substr($head, 36, $retArr['body_len']);
            }

            return $retArr;
        }

        function read($handler)
        {
            //读取nshead
            $head = socket_read($handler, 36, PHP_BINARY_READ);
            if (!$head)
            {
                return NULL;
            }

            $head = $this->splitNshead($head);
            if (!isset($head['body_len']))
            {
                return NULL;
            }

            //读取数据包内容
            $retbuffer = socket_read($handler, 8094, PHP_BINARY_READ);
            if (!$retbuffer)
            {
                return NULL;
            }

            return $retbuffer;
        }

        function nextval($name = 'couponid')
        {
            $query['name'] = $name;
            $query['id'] = 1;
            $query['step'] = 1;
            $query['command_no'] = 1;

            $str = mc_pack_array2pack($query, PHP_MC_PACK_V2);
            $head_arr['body_len']=strlen($str);
            $buffer = $this->buildNshead($head_arr) . $str;

            if(!socket_write($this->_sock, $buffer))
            {
                return false;
            }

            $str = $this->read($this->_sock);
            $result = mc_pack_pack2array($str);

            return array('err_no'=>$result['error'],'id'=>$result['id']);
        }

        function stepval($name = 'couponid', $step = 1)
        {
            $query['name'] = $name;
            $query['id'] = 1;
            $query['step'] = $step;
            $query['command_no'] = 2;

            $str = mc_pack_array2pack($query, PHP_MC_PACK_V2);
            $head_arr['body_len']=strlen($str);
            $buffer = $this->buildNshead($head_arr) . $str;

            if(!socket_write($this->_sock, $buffer))
            {
                return false;
            }

            $str = $this->read($this->_sock);
            $result = mc_pack_pack2array($str);

            $newId = $result['id'];
            return array(
                'err_no' => $result['error'],
                'end_id' => $newId,
                'start_id' => ($newId - $step + 1)
            );
        }
    }