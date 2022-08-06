<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected  function _initRoutes(){
        $router = Zend_Controller_Front::getInstance()->getRouter();
        include APPLICATION_PATH . '/configs/routes.php';
    }
    
    public function _initDb() {
        $db = new Zend_Db_Adapter_Pdo_Mysql(
                    array(
                 'host' => 'sql6.freemysqlhosting.net',
                'username' => 'sql6511088',
                'password' => 'ADakpCBTcT',
                'dbname' => 'sql6511088', //live db
                        
//                          'host' => 'localhost',
//                        'username' => 'root',
//                        'password' => 'root',
//                        'dbname' => 'heytaxi'
                    )
        );
        
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        Zend_Registry::set('db', $db);
    }
    
     protected function _initConfig() {
        Zend_Registry::set('config', $this->getOptions());    
    }
    
    protected function _initTimeZone() {
        date_default_timezone_set('Asia/Calcutta');
    }
    
    
    protected function setConstant($constant){
        foreach($constant as $name=>$value){
           if(!defined($name)){
                define($name,$value);
            }
        }
    }

}

