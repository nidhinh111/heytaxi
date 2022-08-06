<?php

class CronController extends Zend_Controller_Action
{

    private $_tblAds, $_tblArea, $_tblConstants, $_tblDriver, $_tblDriverLocation, $_tblDriverOnline, $_tblDriverRegistration, $_tblOtp, $_tblPassenger, $_tblToken, $_tblUser, $_tblUserType;

    public function init() {

        $this->_tblAds              = new Api_Model_Tblads();
        $this->_tblArea             = new Api_Model_Tblarea();
        $this->_tblConstants        = new Api_Model_Tblconstants();
        $this->_tblDriver           = new Api_Model_Tbldriver();
        $this->_tblDriverLocation   = new Api_Model_Tbldriverlocation();
        $this->_tblDriverOnline     = new Api_Model_Tbldriveronline();
        $this->_tblDriverRegistration = new Api_Model_Tbldriverregistraion();
        $this->_tblOtp              = new Api_Model_Tblotp();
        $this->_tblPassenger        = new Api_Model_Tblpassenger();
        $this->_tblToken            = new Api_Model_Tbltoken();
        $this->_tblUser             = new Api_Model_Tbluser();
        $this->_tblUserType         = new Api_Model_Tblusertype();
        
    }
    
    public function deletedriverlocationAction(){
        $where = 'date(update_time) <= "'.date('Y-m-d',strtotime('-2 days')).'"';
        $locationArr = $this->_tblDriverLocation->selectData(array('count(*) as count'),$where);
        
        $deleteRows = $this->_tblDriverLocation->deleterows($where);
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . $locationArr[0]['count'] . PHP_EOL;
        $this->webservicesLog($string, 'cron_deletelocation');
        echo 'success';exit;
    }
    
    function webservicesLog($data, $file_initial = '') {
        $file = 'app_service_log/' . date('Y-m-d') . ".txt";
        if ($file_initial != '') {
            $file = 'app_service_log/' . date('Y-m-d') . '_' . $file_initial . ".txt";
        }

        file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
        return true;
    }


}

