<?php
/**
 * @file Log.php
 * @author jpbirdy
 * @date 2014年7月14日16:57:11
 * @brief
 *
 **/

class Fis_Log
{
    const LOG_LEVEL_FATAL = 0x01;
    const LOG_LEVEL_WARNING = 0x02;
    const LOG_LEVEL_NOTICE = 0x04;
    const LOG_LEVEL_TRACE = 0x08;
    const LOG_LEVEL_DEBUG = 0x10;

    public static $arrLogLevels = array(
        self::LOG_LEVEL_FATAL => 'FATAL',
        self::LOG_LEVEL_WARNING => 'WARNING',
        self::LOG_LEVEL_NOTICE => 'NOTICE',
        self::LOG_LEVEL_TRACE => 'TRACE',
        self::LOG_LEVEL_DEBUG => 'DEBUG',
    );

    protected $level;
    protected $log_file;
    protected $auto_rotate;
    protected $str_format;
    protected $str_format_wf;
    protected $add_notice = array();

    private static $arr_instance = array();
    public static $current_instance;
    private static $log_writers = array();

    const DEFAULT_FORMAT = '%L: %t fileLine[%f:%N] errno[%E] logId[%l] uri[%U] clintIp[%u] refer[%{referer}i] cookie[%{cookie}i] %S %M';
    const DEFAULT_FORMAT_STD = '%L: %{%m-%d %H:%M:%S}t %{app}x * %{pid}x [logid=%l filename=%f lineno=%N errno=%{err_no}x %{encoded_str_array}x errmsg=%{u_err_msg}x]';

    private function __construct($log_config)
    {
        date_default_timezone_set("Asia/ShangHai");
        $this->level = $log_config['level'];
        $this->auto_rotate = $log_config['auto_rotate'];
        $this->log_file = $log_config['log_file'];
        $this->str_format = $log_config['format'];
        $this->str_format_wf = $log_config['format_wf'];
    }

    public static function getLogPrefix()
    {
        return MAIN_APP;
    }

    /**
     * 打印日志的目录，在Init中配置
     * @return string
     */
    public static function getLogPath()
    {
        $path = LOG_PATH . '/' . MAIN_APP;
        if(!file_exists($path))
        {
            mkdir($path);
        }
        return $path;
    }

    /**
     * @brief 数据文件目录
     **/
    public static function getDataPath()
    {
        return DATA_PATH;
    }
    /**
     * @return Fis_Log
     * */
    public static function getInstance($app = null)
    {
        if (empty($app))
        {
            $app = self::getLogPrefix();
        }
        if (empty(self::$arr_instance[$app]))
        {
            // 生成路径
            $log_path = self::getLogPath();
            $log_file = $log_path . "/$app.log";
            $format = self::DEFAULT_FORMAT;
            $format_wf = $format;

            $log_conf = array(
                'level' => intval(16),
                'auto_rotate' => (true),
                'log_file' => $log_file,
                'format' => $format,
                'format_wf' => $format_wf,
            );

            $log_app_conf = Fis_Conf::getAppConf('log');
            if(!empty($log_level))
            {
                array_merge($log_conf , $log_app_conf);
            }
            self::$arr_instance[$app] = new Fis_Log($log_conf);
        }
        return self::$arr_instance[$app];
    }

    /**
     * @param $str
     * @param int $errno
     * @param null $arr_args
     * @param int $depth
     * @return int
     */
    public static function debug($str, $errno = 0, $arr_args = null, $depth = 0,$filename_suffix = '')
    {
//        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $arr_args, $depth + 1, '.new'.$filename_suffix, self::DEFAULT_FORMAT_STD);
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_DEBUG, $str, $errno, $arr_args, $depth + 1,$filename_suffix);
        return $ret;
    }

    public static function trace($str, $errno = 0, $arr_args = null, $depth = 0,$filename_suffix = '')
    {
//        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $arr_args, $depth + 1, '.new'.$filename_suffix, self::DEFAULT_FORMAT_STD);
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_TRACE, $str, $errno, $arr_args, $depth + 1,$filename_suffix);
        return $ret;
    }

    public static function notice($str, $errno = 0, $arr_args = null, $depth = 0,$filename_suffix = '')
    {
//        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $arr_args, $depth + 1, '.new'.$filename_suffix, self::DEFAULT_FORMAT_STD);
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_NOTICE, $str, $errno, $arr_args, $depth + 1,$filename_suffix);
        return $ret;
    }

    public static function warning($str, $errno = 0, $arr_args = null, $depth = 0,$filename_suffix = '')
    {
//        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $arr_args, $depth + 1, '.new'.$filename_suffix, self::DEFAULT_FORMAT_STD);
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_WARNING, $str, $errno, $arr_args, $depth + 1,$filename_suffix);
        return $ret;
    }

    public static function fatal($str, $errno = 0, $arr_args = null, $depth = 0,$filename_suffix = '')
    {
//        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $arr_args, $depth + 1, '.new'.$filename_suffix, self::DEFAULT_FORMAT_STD);
        $ret = self::getInstance()->writeLog(self::LOG_LEVEL_FATAL, $str, $errno, $arr_args, $depth + 1,$filename_suffix);
        return $ret;
    }

    public static function addNotice($key, $value)
    {
        $log = self::getInstance();

        if (!isset($value))
        {
            $value = $key;
            $key = '@';
        }

        $info = is_array($value) ? strtr(strtr(var_export($value, true), array(
                    "  array (\n" => '{',
                    "array (\n" => '{',
                    ' => ' => ':',
                    ",\n" => ',',
                )), array(
                '{  ' => '{',
                ":\n{" => ':{',
                '  ),  ' => '},',
                '),' => '},',
                ',)' => '}',
                ',  ' => ',',
            )) : $value;
        $log->add_notice[$key] = $info;
    }

    // 生成logid
    public static function genLogID()
    {
        if (defined('LOG_ID'))
        {
            return LOG_ID;
        }
        if (getenv('HTTP_X_Fis_LOGID'))
        {
            define('LOG_ID', trim(getenv('HTTP_X_Fis_LOGID')));
        }
        elseif (isset($_REQUEST['logid']))
        {
            define('LOG_ID', intval($_REQUEST['logid']));
        }
        else
        {
            $arr = gettimeofday();
            $logId = ((($arr['sec'] * 100000 + $arr['usec'] / 10) & 0x7FFFFFFF) | 0x80000000);
            define('LOG_ID', $logId);
        }
        return LOG_ID;
    }

    // 获取客户端ip
    public static function getClientIp()
    {
        return Fis_Ip::getClientIp();
    }

    private function writeLog($level, $str, $errno = 0, $arr_args = null, $depth = 0, $filename_suffix = '', $log_format = null)
    {
        if ($level > $this->level || !isset(self::$arrLogLevels[$level]))
        {
            return;
        }

        //log file name
        $strLogFile = $this->log_file;
        if (($level & self::LOG_LEVEL_WARNING) || ($level & self::LOG_LEVEL_FATAL))
        {
            $strLogFile .= '.wf';
        }
        $strLogFile .= $filename_suffix;
        //assign data required
        $this->current_log_level = self::$arrLogLevels[$level];

        //build array for use as strargs
        $_arr_args = false;
        $_add_notice = false;
        if (is_array($arr_args) && count($arr_args) > 0)
        {
            $_arr_args = true;
        }
        if (!empty($this->add_notice))
        {
            $_add_notice = true;
        }

        if ($_arr_args && $_add_notice)
        { //both are defined, merge
            $this->current_args = $arr_args + $this->add_notice;
        }
        else if (!$_arr_args && $_add_notice)
        { //only add notice
            $this->current_args = $this->add_notice;
        }
        else if ($_arr_args && !$_add_notice)
        { //only arr args
            $this->current_args = $arr_args;
        }
        else
        { //empty
            $this->current_args = array();
        }

        $this->current_err_no = $errno;
        $this->current_err_msg = $str;
        $trace = debug_backtrace();
        $depth2 = $depth + 1;
        if ($depth >= count($trace))
        {
            $depth = count($trace) - 1;
            $depth2 = $depth;
        }
        $this->current_file = isset($trace[$depth]['file']) ? $trace[$depth]['file'] : "";
        $this->current_line = isset($trace[$depth]['line']) ? $trace[$depth]['line'] : "";
        $this->current_function = isset($trace[$depth2]['function']) ? $trace[$depth2]['function'] : "";
        $this->current_class = isset($trace[$depth2]['class']) ? $trace[$depth2]['class'] : "";
        $this->current_function_param = isset($trace[$depth2]['args']) ? $trace[$depth2]['args'] : "";

        self::$current_instance = $this;

        //get the format
        if ($log_format == null) $format = $this->getFormat($level);
        else
            $format = $log_format;
        $str = $this->getLogString($format);

        if ($this->auto_rotate)
        {
            $strLogFile .= '.' . date('YmdH');
        }

        foreach (self::$log_writers as $writer)
        {
            $writer->write($str);
        }

        return file_put_contents($strLogFile, $str, FILE_APPEND);
    }

    // added support for self define format
    private function getFormat($level)
    {
        if ($level == self::LOG_LEVEL_FATAL || $level == self::LOG_LEVEL_WARNING)
        {
            $fmtstr = $this->str_format_wf;
        }
        else
        {
            $fmtstr = $this->str_format;
        }
        return $fmtstr;
    }

    public function getLogString($format)
    {
        $md5val = md5($format);
        $func = "_Fis_log_$md5val";
        if (function_exists($func))
        {
            return $func();
        }
        $dataPath = self::getDataPath();
        $filename = $dataPath . '/log/' . $md5val . '.php';
        if (!file_exists($filename))
        {
            $tmp_filename = $filename . '.' . posix_getpid() . '.' . rand();
            if (!is_dir($dataPath . '/log'))
            {
                @mkdir($dataPath . '/log');
            }
            file_put_contents($tmp_filename, $this->parseFormat($format));
            rename($tmp_filename, $filename);
        }
        include_once($filename);
        $str = $func();

        return $str;
    }

    // parse format and generate code
    public function parseFormat($format)
    {
        $matches = array();
        $regex = '/%(?:{([^}]*)})?(.)/';
        preg_match_all($regex, $format, $matches);
        $prelim = array();
        $action = array();
        $prelim_done = array();

        $len = count($matches[0]);
        for ($i = 0; $i < $len; $i++)
        {
            $code = $matches[2][$i];
            $param = $matches[1][$i];
            switch ($code)
            {
                case 'h':
                    $action[] = "(defined('CLIENT_IP')? CLIENT_IP : Fis_Log::getClientIp())";
                    break;
                case 't':
                    $action[] = ($param == '') ? "strftime('%Y-%m-%d %H:%M:%S')" : "strftime(" . var_export($param, true) . ")";
                    break;
                case 'i':
                    $key = 'HTTP_' . str_replace('-', '_', strtoupper($param));
                    $key = var_export($key, true);
                    $action[] = "(isset(\$_SERVER[$key])? \$_SERVER[$key] : '')";
                    break;
                case 'a':
                    $action[] = "(defined('CLIENT_IP')? CLIENT_IP : Fis_Log::getClientIp())";
                    break;
                case 'A':
                    $action[] = "(isset(\$_SERVER['SERVER_ADDR'])? \$_SERVER['SERVER_ADDR'] : '')";
                    break;
                case 'C':
                    if ($param == '')
                    {
                        $action[] = "(isset(\$_SERVER['HTTP_COOKIE'])? \$_SERVER['HTTP_COOKIE'] : '')";
                    }
                    else
                    {
                        $param = var_export($param, true);
                        $action[] = "(isset(\$_COOKIE[$param])? \$_COOKIE[$param] : '')";
                    }
                    break;
                case 'D':
                    $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                    break;
                case 'e':
                    $param = var_export($param, true);
                    $action[] = "((getenv($param) !== false)? getenv($param) : '')";
                    break;
                case 'f':
                    $action[] = 'Fis_Log::$current_instance->current_file';
                    break;
                case 'H':
                    $action[] = "(isset(\$_SERVER['SERVER_PROTOCOL'])? \$_SERVER['SERVER_PROTOCOL'] : '')";
                    break;
                case 'm':
                    $action[] = "(isset(\$_SERVER['REQUEST_METHOD'])? \$_SERVER['REQUEST_METHOD'] : '')";
                    break;
                case 'p':
                    $action[] = "(isset(\$_SERVER['SERVER_PORT'])? \$_SERVER['SERVER_PORT'] : '')";
                    break;
                case 'q':
                    $action[] = "(isset(\$_SERVER['QUERY_STRING'])? \$_SERVER['QUERY_STRING'] : '')";
                    break;
                case 'T':
                    switch ($param)
                    {
                        case 'ms':
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000 - REQUEST_TIME_US/1000) : '')";
                            break;
                        case 'us':
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) * 1000000 - REQUEST_TIME_US) : '')";
                            break;
                        default:
                            $action[] = "(defined('REQUEST_TIME_US')? (microtime(true) - REQUEST_TIME_US/1000000) : '')";
                    }
                    break;
                case 'U':
                    $action[] = "(isset(\$_SERVER['REQUEST_URI'])? \$_SERVER['REQUEST_URI'] : '')";
                    break;
                case 'v':
                    $action[] = "(isset(\$_SERVER['HOSTNAME'])? \$_SERVER['HOSTNAME'] : '')";
                    break;
                case 'V':
                    $action[] = "(isset(\$_SERVER['HTTP_HOST'])? \$_SERVER['HTTP_HOST'] : '')";
                    break;

                case 'L':
                    $action[] = 'Fis_Log::$current_instance->current_log_level';
                    break;
                case 'N':
                    $action[] = 'Fis_Log::$current_instance->current_line';
                    break;
                case 'E':
                    $action[] = 'Fis_Log::$current_instance->current_err_no';
                    break;
                case 'l':
                    $action[] = "Fis_Log::genLogID()";
                    break;
                case 'u':
                    if (!isset($prelim_done['user']))
                    {
                    }
                    $action[] = "((defined('CLIENT_IP') ? CLIENT_IP: Fis_Log::getClientIp()) )";
                    break;
                case 'S':
                    if ($param == '')
                    {
                        $action[] = 'Fis_Log::$current_instance->get_str_args()';
                    }
                    else
                    {
                        $param_name = var_export($param, true);
                        if (!isset($prelim_done['S_' . $param_name]))
                        {
                            $prelim[] = "if (isset(Fis_Log::\$current_instance->current_args[$param_name])) {
                            \$____curargs____[$param_name] = Fis_Log::\$current_instance->current_args[$param_name];
                            unset(Fis_Log::\$current_instance->current_args[$param_name]);
                        } else \$____curargs____[$param_name] = '';";
                            $prelim_done['S_' . $param_name] = true;
                        }
                        $action[] = "\$____curargs____[$param_name]";
                    }
                    break;
                case 'M':
                    $action[] = 'Fis_Log::$current_instance->current_err_msg';
                    break;
                case 'x':
                    $need_urlencode = false;
                    if (substr($param, 0, 2) == 'u_')
                    {
                        $need_urlencode = true;
                        $param = substr($param, 2);
                    }
                    switch ($param)
                    {
                        case 'log_level':
                        case 'line':
                        case 'class':
                        case 'function':
                        case 'err_no':
                        case 'err_msg':
                            $action[] = 'Fis_Log::$current_instance->current_' . $param;
                            break;
                        case 'log_id':
                            $action[] = "Fis_Log::genLogID()";
                            break;
                        case 'app':
                            $action[] = "Fis_Log::getLogPrefix()";
                            break;
                        case 'function_param':
                            $action[] = 'Fis_Log::flattenArgs(Fis_Log::$current_instance->current_function_param)';
                            break;
                        case 'argv':
                            $action[] = '(isset($GLOBALS["argv"])? Fis_Log::flattenArgs($GLOBALS["argv"]) : \'\')';
                            break;
                        case 'pid':
                            $action[] = 'posix_getpid()';
                            break;
                        case 'encoded_str_array':
                            $action[] = 'Fis_Log::$current_instance->get_str_args_std()';
                            break;
                        default:
                            $action[] = "''";
                    }
                    if ($need_urlencode)
                    {
                        $action_len = count($action);
                        $action[$action_len - 1] = 'rawurlencode(' . $action[$action_len - 1] . ')';
                    }
                    break;
                case '%':
                    $action[] = "'%'";
                    break;
                default:
                    $action[] = "''";
            }
        }

        $strformat = preg_split($regex, $format);
        $code = var_export($strformat[0], true);
        for ($i = 1; $i < count($strformat); $i++)
        {
            $code = $code . ' . ' . $action[$i - 1] . ' . ' . var_export($strformat[$i], true);
        }
        $code .= ' . "\n"';
        $pre = implode("\n", $prelim);

        $cmt = "Used for app " . self::getLogPrefix() . "\n";
        $cmt .= "Original format string: " . str_replace('*/', '* /', $format);

        $md5val = md5($format);
        $func = "_Fis_log_$md5val";
        $str = "<?php \n/*\n$cmt\n*/\nfunction $func() {\n$pre\nreturn $code;\n}";
        return $str;
    }

    //helper functions for use in generated code
    public static function flattenArgs($args)
    {
        if (!is_array($args)) return '';
        $str = array();
        foreach ($args as $a)
        {
            $str[] = preg_replace('/[ \n\t]+/', " ", $a);
        }
        return implode(', ', $str);
    }

    public function get_str_args()
    {
        $strArgs = '';
        foreach ($this->current_args as $k => $v)
        {
            $strArgs .= ' ' . $k . '[' . $v . ']';
        }
        return $strArgs;
    }

    /**
     * 注册一个新的logWriter
     * @param Fis_Log_Writer $writer
     */
    private static function registerWriter(Fis_Log_Writer $writer)
    {
        self::$log_writers[] = $writer;
    }

    /**
     * 使用标准输出
     * @return boolean
     */
    public static function useLogerStdOut()
    {
        static $used = false;
        if ($used)
        {
            return true;
        }

        self::registerWriter(new Fis_Log_Writer_Std());
        $used = true;
    }
}
