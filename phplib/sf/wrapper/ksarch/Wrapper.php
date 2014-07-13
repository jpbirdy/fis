<?php

    /**
     * @file Wrapper.php
     * @author liaohuiqin01
     * @date 2013/05/11 16:42:40
     * @brief ksarch wrapper基类，提供计时器;
     */

    class SF_Wrapper_KSArch_Wrapper{
        protected $timer = null;
        public function __construct($params = null){
            //精度:us;
            $this->timer = new SF_Wrapper_KSArch_Timer(true, Fis_Timer::PRECISION_US);
        }

        protected function start(){
            $this->timer->start();
        }

        protected function stop(){
            return $this->timer->stop();
        }
    }

