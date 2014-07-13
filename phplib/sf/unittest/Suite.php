<?php
/**
 * @desc 要让单测运行通，要注意修改两处的conf，
 *    一处是odp/php/etc/ext/init.ini下的include_path，其值需要包含phptest的路径，形如".:/home/users/liumingjun/odp/php/lib/php:/home/users/liumingjun/odp/php/phplib/phptest"
 *    另一处是odp/conf/phptest.conf下UnitTest节点的code_path和phplib_path，其值形如“/home/users/liumingjun/odp/php/app/”和“/home/users/liumingjun/odp/php/phplib/“
 *
 *    使用方法：odp/php/bin/phpunit Suite.php
 *    会自动遍历code_path和phplib_path中的文件找出继承自Testcase_PTest的类来执行。
 *
 * @author liumingjun@baidu.com
 */

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');
require_once 'UnitTest/init/PTestInit.php';
/** @noinspection PhpUndefinedClassInspection */
PTestInit::setTest_App('Common');

/**
 * Class SF_UnitTest_Suite
 */
class SF_UnitTest_Suite
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('SIRIUS FRAMEWORK');

        $auto = new SF_UnitTest_Helper(dirname(__FILE__), $suite);

        $auto->addSuite();
        return $suite;
    }
}

class SF_UnitTest_Helper {
    private $_path;
    private $_files;
    private $_directory;
    private $_recurse;
    private $_testSuite;
    private $_prefix;
    private $_tmpPath;
    private $_needGC;

    /**
     * @param $classname
     */
    private function _fileFilter($classname)
    {
        $obj=new ReflectionClass($classname);
        $file=$obj->getFileName();
        PHPUnit_Util_Filter::addDirectoryToFilter(dirname($file));
    }

    /**
     * @param string $path
     * @param PHPUnit_Framework_TestSuite $testSuite
     */
    public function __construct($path,$testSuite)
    {
        ini_set("memory_limit","2048M");
        $this->_path=$path;
        $this->_recurse=true;
        $this->_testSuite=$testSuite;
        $this->_prefix="";
        $this->_needGC=true;
        $this->_fileFilter("Bd_Init");
        $this->_fileFilter("Bd_Conf");
    }

    public function __destruct()
    {
        if(!$this->_needGC) return;
        if(is_dir($this->_tmpPath))
        {
            //析构在脚本执行完成时候发生，应该没有bug
            $path=$this->_tmpPath;
            passthru("rm -rf $path");
        }
    }

    /**
     * @param $gc
     */
    public function setNeedGC($gc)
    {
        $this->_needGC=$gc;
    }
    public function addSuite()
    {
        if(!is_dir($this->_tmpPath))
        {
            #mkdir($this->_tmpPath);
        }
        $files=scandir($this->_path);
        self::_filter($files);
        self::_addDirectorySuite();
        if($this->_recurse==true)
        {
            self::_addSubDirectorySuite();
        }
    }

    /**
     * @param $prefix
     */
    public function addPrefix($prefix)
    {
        $this->_prefix.=$prefix."_";
    }

    /**
     * @param $tmpPath
     */
    public function setTmpPath($tmpPath)
    {
        $this->_tmpPath=$tmpPath;
    }
    private function _addDirectorySuite()
    {
        foreach($this->_files as $file)
        {
            $className=self::_changeName($file);
            if($className!==null){
                $this->_testSuite->addTestSuite($className);
            }
        }
    }

    /**
     * @param $file
     * @return null
     */
    private function _changeName($file)
    {
        $input_PHPstr=file_get_contents($this->_path."/".$file);
        require_once $this->_path."/".$file;
        $className=null;
        if(preg_match("/class ([A-Za-z0-9_]+)[\t| ]+(extends)/",$input_PHPstr,$match)){
            $className=$match[1];
        }
        return $className;


    }
    private function _addSubDirectorySuite()
    {
        foreach($this->_directory as $directory)
        {
            $path=$this->_path."/".$directory;
            $suiteHelper=new SF_UnitTest_Helper($path,$this->_testSuite);
            $suiteHelper->addPrefix($directory);
            $suiteHelper->setTmpPath($this->_tmpPath);
            $suiteHelper->setNeedGC(false);
            $suiteHelper->addSuite();
        }
    }

    /**
     * @param $files
     */
    private function _filter($files)
    {
        $this->_files=array();
        $this->_directory=array();
        foreach($files as $file)
        {
            if(is_dir($this->_path."/".$file)&&$file!=="."&&$file!==".."&&$file!=='.svn'&&$file!="st")
            {
                $this->_directory[]=$file;
            }
            else if(preg_match("/.+\.php$/",$file)&&!preg_match("/.*suite.*\.php$/",$file))
            {
                $this->_files[]=$file;
            }
        }
    }

}


