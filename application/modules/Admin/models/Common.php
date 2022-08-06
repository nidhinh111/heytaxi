<?php


class Admin_Model_Common extends Zend_Db_Table_Abstract
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
    
    public function getUserType(){
        $userArr = $this->_tblUserType->selectData(array('id','name'));
        
        foreach($userArr as $val){
            $userData[$val['id']] = $val['name'];
        }
        
        return $userData;
    }
    
    public function getAreaName(){
        $where = 'status = 1';
        $areaArr = $this->_tblArea->selectData(array('id','name'),$where);
        
        foreach($areaArr as $val){
            $areaData[$val['id']] = $val['name'];
        }
        
        return $areaData;
    }
    
    public function sendfcmnotification($registrationIds ,$GetData ) {       
     
        $fields = array (
                'to'                    => $registrationIds,//array('c8LxJ5FUGeU:APA91bFYxEocdmgV2QXfQL0axx42LG-FF1AXVCDeJ4OcsZ9mS-NDJVzaLi_lAoafHqzgYeAWxWltxtncTLQwrNxn52HunBZ3Qur6OGvXK1v5aajQkW_D9-dTw6sMoimKjcJsHSV1HVl4'),
                'notification'		=> $GetData,
                'priority'              => 'high',
//                'time_to_live'          => '7200000',
        );
        $headers = array
        (
//                'Authorization: key=AAAAGdHn0sg:APA91bGQJ6OdY0clU28mo3C4silFVxM0kwTE73UYIYz2ADb9T0FQT5YQv4pDNW7COHU4h5p5TvCkvV_iD1z14CZmnPwEY28MlH-SyEVY0Su3rGsNlO8mnBDEXm_wgInl6SuYaTTbXtrQ',
                'Authorization: key=AAAAToslW1Y:APA91bH2R03e7zPaUEwMNN9K4TjUhM-cjm7BrdUAAVfhQUNPNFijjIiNmK666YmxqaJS7nL3zrn7fq5tnwqUEeWvKjLsc0Ld2Zzd66SZO9sT9nZI5AkXLCWyzOuvRPq8Axe_b5znBpvi',
                'Content-Type: application/json'
        );
         //to create log 
        $string = PHP_EOL.'['.date('Y-m-d H:i:s').'] '.'FCM_Rider_Approval'.' Fiedls ->> '.json_encode($fields).'  Headers ->> '.json_encode($headers).PHP_EOL;
        $this->webservicesLog($string,'FCM_Rider_Approval');   
        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch );
        curl_close( $ch );
        $string = PHP_EOL.'['.date('Y-m-d H:i:s').'] '.'FCM_Rider_Approval'.' Fiedls ->> '.json_encode($fields).'  Headers ->> '.json_encode($headers).' POST <<- '. $result.PHP_EOL;
        $this->webservicesLog($string,'FCM_Rider_Approval');   
      //  print_r($result);
        return $result;
    }
    
    public function webservicesLog($data, $file_initial = '') {
        $file = 'app_service_log/' . date('Y-m-d') . ".txt";
        if ($file_initial != '') {
            $file = 'app_service_log/' . date('Y-m-d') . '_' . $file_initial . ".txt";
        }

        file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
        return true;
    }
    
    public function sendSms($toNumber, $msg) {

        if ($toNumber != '' && $msg != '') {
//            $msg = urlencode($msg);
            //sms url
            //$url = "http://smsapi.24x7sms.com/api_2.0/SendSMS.aspx?APIKEY=JYvQyohVsdm&MobileNo=" . $toNumber . "&SenderID=GRABDB&Message=" . $msg . "&ServiceName=TEMPLATE_BASED"; 
            $url = "http://api.msg91.com/api/sendhttp.php?country=91&sender=BGOREO&route=4&mobiles=".$toNumber."&authkey=244194AT0r7iPn5bcf59f0&message=".$msg;
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $output = curl_exec($ch);
            curl_close($ch);
            return $output;
        }else{
            return 'Mandatory field missing';
        }
    }
    
}