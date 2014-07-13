<?php
/**
 * @desc 基础的DAO类，提供了简单的加载方法
 * @author liumingjun@baidu.com
 */

abstract class SF_Model_Dao_Base {
    /**
     * @var SF_Interface_ICURDE
     */
    private $_dataSourceEngine = null;

    /**
     * @desc 获取对应的表名
     * @return string
     */
    abstract function getTableName();

    /**
     * @desc 返回默认的数据源引擎，如要使用，请使用_getDataSourceEngine方法
     * @see getDataSourceEngine
     * @return SF_Interface_ICURDE
     */
    abstract protected function _getDefaultDataSourceEngine();


    /**
     * @return SF_Interface_ILog
     */
    protected function logger()
    {
        return SF_Log_Manager::getBDLogger(SF_Log_Manager::LOGGER_DEPTH);
    }

    /**
     * @desc 根据传入的entity对象类型获取对应的单条数据
     * @param array $conds
     * @param SF_Entity_DataSourceEntity $entity 将传入的entity根据条件赋值完成初始化
     * @param array|null $option
     * @param array|null $append
     * @param SF_Interface_ICURDE $dataSourceEngine 如果不传将使用默认的dataSourceEngine
     * @param bool $isFromMaster 是否从主库取
     * @return SF_Entity_DataSourceEntity
     */
    protected function _loadData($conds, $entity, $option = null, $append = null, SF_Interface_ICURDE $dataSourceEngine = null, $isFromMaster = false)
    {
        $entity->clear();
        $fields = $entity->getAllFields();
        if ($isFromMaster)
        {
            $data = $this->getDataSourceEngine($dataSourceEngine)->selectFromMaster($this->getTableName(), $fields, $conds, $option, $append);
        }
        else
        {
            $data = $this->getDataSourceEngine($dataSourceEngine)->select($this->getTableName(), $fields, $conds, $option, $append);
        }
        if (!empty($data) && isset($data[0])) {
            $entity->instantiate($data[0], true, false);
        }
        return $entity;
    }


    /**
     * @desc 从主库获取数据
     *
     * @param array $conds
     * @param SF_Entity_DataSourceEntity $entity DataSourceEntity对象的实例，主要的作用是提供类名和getTable等的方法
     * @param array|null $option
     * @param array|null $append
     * @param SF_Interface_ICURDE $dataSourceEngine
     * @return SF_Entity_DataSourceEntity
     */
    protected function _loadDataFromMaster($conds, $entity, $option = null, $append = null, SF_Interface_ICURDE $dataSourceEngine = null)
    {
        return $this->_loadData($conds,$entity,$option,$append,$dataSourceEngine, true);
    }

    /**
     * @desc 根据传入的entity对象类型获取对应的集合数据
     *
     * @param array $conds
     * @param SF_Entity_DataSourceEntity $entity DataSourceEntity对象的实例，主要的作用是提供类名和getTable等的方法
     * @param array|null $option
     * @param array|null $append
     * @param SF_Interface_ICURDE $dataSourceEngine
     * @param bool $isFromMaster 是否从主库取
     * @throws SF_Exception_InternalException
     * @return SF_Entity_Collection
     */
    protected function _loadCollectionData($conds, $entity, $option = null, $append = null, SF_Interface_ICURDE $dataSourceEngine = null, $isFromMaster = false)
    {
        $entryClassName = get_class($entity);

        $newEntity = new $entryClassName();

        if (!$newEntity instanceof SF_Entity_DataSourceEntity) {

            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::DATASOURCE_LOAD_ERROR, '要加载的类不是继承自SF_Entity_DataSourceEntity', array(
                'initClassName' => $entryClassName
            ));
        }

        $fields = $newEntity->getAllFields();
        if ($isFromMaster)
        {
            $data = $this->getDataSourceEngine($dataSourceEngine)->selectFromMaster($this->getTableName(), $fields, $conds,$option, $append);
        }
        else{
            $data = $this->getDataSourceEngine($dataSourceEngine)->select($this->getTableName(), $fields, $conds,$option, $append);
        }

        $collection = new SF_Entity_Collection();

        if (!empty($data) && count($data) > 0) {
            foreach ($data as $entry) {
                $entity = new $entryClassName($entry);
                $collection->append($entity);
            }
        }
        return $collection;
    }

    /**
     * @desc 从主库获取数据
     *
     * @param array $conds
     * @param SF_Entity_DataSourceEntity $entity DataSourceEntity对象的实例，主要的作用是提供类名和getTable等的方法
     * @param array|null $option
     * @param array|null $append
     * @param SF_Interface_ICURDE $dataSourceEngine
     * @return SF_Entity_Collection
     */
    protected function _loadCollectionDataFromMaster($conds, $entity, $option = null, $append = null, SF_Interface_ICURDE $dataSourceEngine = null)
    {
        return $this->_loadCollectionData($conds,$entity,$option,$append,$dataSourceEngine, true);
    }


    /**
     * @param SF_Entity_DataSourceEntity $dsEntity
     * @param SF_Interface_ICURDE|null $dataSourceEngine 当为空时使用默认datasourceengine
     * @return bool|int
     */
    protected function _create($dsEntity, SF_Interface_ICURDE $dataSourceEngine = null){
        //校验，如果不符合规范不可保存
        $dsEntity->validate();
        return $this->getDataSourceEngine($dataSourceEngine)->create($this->getTableName(), $dsEntity->transToArr());
    }


    /**
     * @param array|SF_Entity_Collection $collection
     * @param SF_Interface_ICURDE $dataSourceEngine
     * @return int
     */
    protected function _createBatch($collection, SF_Interface_ICURDE $dataSourceEngine = null)
    {
        $entrys = $collection;
        if ($collection instanceof SF_Entity_Collection)
        {
            $entrys = $collection->toArray(true);
        }
        return $this->getDataSourceEngine($dataSourceEngine)->createBatch($this->getTableName(), $entrys);
    }

    #region 内部使用方法
    /**
     * @desc 设置dataSourceEngine；
     * @param SF_Interface_ICURDE $dataSourceEngine
     * @throws SF_Exception_InternalException
     */
    public function setDataSourceEngine(SF_Interface_ICURDE $dataSourceEngine)
    {
        if (!$dataSourceEngine instanceof SF_Interface_ICURDE) {
            throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_COLLECTION_CONSTRUCT_ERROR);
        }
        $this->_dataSourceEngine = $dataSourceEngine;
    }

    /**
     * @desc 获取适用的dataSourceEngine，当外部有效时，有限使用外部传入的
     *
     * @param SF_Interface_ICURDE $dataSourceEngine
     * @return SF_Interface_ICURDE|SF_Interface_ITranscation|SF_Interface_IDSToolOperate
     * @throws SF_Exception_InternalException
     */
    public function getDataSourceEngine(SF_Interface_ICURDE $dataSourceEngine = null)
    {
        if (!is_null($dataSourceEngine) && $dataSourceEngine instanceof SF_Interface_ICURDE) {
            return $dataSourceEngine;
        }
        else {
            if (empty($this->_dataSourceEngine))
            {
                $this->_dataSourceEngine = $this->_getDefaultDataSourceEngine();
            }

            if (!$this->_dataSourceEngine instanceof SF_Interface_ICURDE) {
                throw new SF_Exception_InternalException(SF_Exception_ErrCodeMapping::ENTITY_COLLECTION_CONSTRUCT_ERROR);
            }
            return $this->_dataSourceEngine;
        }
    }
}