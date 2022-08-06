<?php
class Api_UserController extends Zend_Controller_Action
{
    private $_tblAds, $_tblArea, $_tblConstants, $_tblDriver, $_tblDriverLocation, $_tblDriverOnline, $_tblDriverRegistration, $_tblOtp, $_tblPassenger, $_tblToken, $_tblUser, $_tblUserType,$_commonM;

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
        $this->_commonM             = new Admin_Model_Common();
        
    }
    
    public function indexAction(){ 
        echo 'hi';exit;
    }
    
    function echoInvalidToken(){
        return '{"code":200,"msg":"Invalid token"}';
    }
    
    function webservicesLog($data, $file_initial = '') {
        $file = 'app_service_log/' . date('Y-m-d') . ".txt";
        if ($file_initial != '') {
            $file = 'app_service_log/' . date('Y-m-d') . '_' . $file_initial . ".txt";
        }

        file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
        return true;
    }
    
    function generateToken($length, $strength) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }
    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}
    
    public function directionsapiAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'passenger_directionsapi');
    
        $token              = $this->_getParam('token');
        $origin_latitude    = $this->_getParam('origin_latitude');
        $origin_longitude   = $this->_getParam('origin_longitude');
        $dest_latitude      = $this->_getParam('dest_latitude');
        $dest_longitude     = $this->_getParam('dest_longitude');
    
        $where = 'token = "'.$token.'" AND expire = 0';
        $userArr = $this->_tblToken->selectData(array('userid'),$where);
        
        if(count($userArr) > 0){
            $str_origin = "origin=" . $origin_latitude . "," . $origin_longitude;

            // Destination of route
            $str_dest   = "destination=" . $dest_latitude . "," . $dest_longitude;
            // Building the parameters to the web service
            $parameters = $str_origin . "&" . $str_dest . "&" . "key=".DIRECTION_API_KEY;
            // Output format
            $output     = "json";
            // Building the url to the web service
            $url_directions = "https://maps.googleapis.com/maps/api/directions/" . $output . "?" . $parameters;


            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_directions);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $geoLocations = json_encode(curl_exec($ch), true);
            $responseData = '{"code":220,"msg":"google direction success","directionsresult":' . $geoLocations . '}';
        }else{
            $responseData = $this->echoInvalidToken();
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_directionsapi');

        echo $responseData;exit;
    }
    
    public function trackdriverAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData). PHP_EOL;
        $this->webservicesLog($string, 'passenger_trackdriver');

        $token = $this->_getParam('token');
        $driver_id = $this->_getParam('driver_id');
        
        $where = 'token = "'.$token.'" AND expire = 0';
        $userArr = $this->_tblToken->selectData(array('userid'),$where);
        
        if(count($userArr) > 0){
            $updateTime = date('Y-m-d H:i:s', strtotime('-'.TRACK_TIME.' minutes'));
            $where = 'driver_id = "'.$driver_id.'" AND update_time >= "'.$updateTime.'" AND accuracy < '.ACCURACY_TRACK;
            $driverArr = $this->_tblDriverLocation->selectData(array('*'),$where,'id'.' '.'desc',0,1);
            
            if(count($driverArr) > 0){
                $responseData = '{"code":211,"msg":"driver location fetch success","drivers":' . json_encode($driverArr) . '}';
            }else{
                $responseData = '{"code":212,"msg":"no driver location found","drivers":' . json_encode($driverArr) . '}';
            }
        }else{
            $responseData = $this->echoInvalidToken();
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_trackdriver');
        
         echo $responseData;exit;
    }
    
    public function finddriversAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'passenger_finddrivers');

        $token          = $this->_getParam('token');
        $vehicleType    = $this->_getParam('vehicletype');
        $lat            = $this->_getParam('lat');
        $lng            = $this->_getParam('lng');
        $versionCode    = $this->_getParam('versionCode');
        
        $versionCheck = $this->_tblConstants->selectData(array('*'),'name="LAST_APP_VERSION"');
        
        if($versionCode < $versionCheck[0]['values']){
            $responseData = '{"code":205,"msg":"Force update needed"}';
        }else{
            $cols1Arr = array('id',"( 6371 * acos( cos( radians('" . $lat . "') ) * cos( radians( `lat` ) ) * cos( radians( `lng` ) - radians('" . $lng . "') ) + sin( radians('" . $lat . "') ) * sin( radians( `lat` ) ) ) ) AS distance");
            $whereArea = 'status = 1';
            $orderby1Arr = array('distance');
            $areaArr = $this->_tblArea->selectData($cols1Arr, $whereArea, $orderby1Arr, '', '', "distance < 15");
            
            if(count($areaArr) > 0){
                $where = 'token = "'.$token.'" AND expire = 0';
                $userArr = $this->_tblToken->selectData(array('userid'),$where);
                if(count($userArr) > 0){
                    
                    $db = Zend_Db_Table_Abstract::getDefaultAdapter();
                    $updateTime = date('Y-m-d H:i:s', strtotime('-'.TRACK_TIME.' minutes'));
                    $query = "SELECT driver_location.driver_id,driver_location.lat,driver_location.lng,driver_location.accuracy,
                            user.name,user.phone,( 6371* acos( cos( radians(:lat) ) * cos( radians( lat ) ) 
                            * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) 
                            * sin( radians( lat ) ) ) ) AS distance FROM driver_location inner join driver
                            on driver_location.driver_id=driver.id INNER join user on driver.userid=user.id inner join driver_online
                            on driver_online.driver_id=driver_location.driver_id
                            where user.usertype=:typevehicle and driver_online.status=1 and driver_location.accuracy < '".ACCURACY."' AND driver_location.update_time >= '".$updateTime."' 
                            HAVING distance < 10 ORDER BY driver_location.id DESC";
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':typevehicle', $vehicleType);
                    $stmt->bindParam(':lat', $lat);
                    $stmt->bindParam(':lng', $lng);
                    $stmt->execute();
                    $drivers = $stmt->fetchAll(PDO::FETCH_OBJ);
                    if(count($drivers) > 0){
                        $driversData = (array) $drivers;
                        $driversArr = $driversList = array();
                        foreach($driversData as $val){
                            $val     = get_object_vars($val);
                            if(!isset($driversList[$val['driver_id']])){
                                $driversArr[] = $val;
                                $driversList[$val['driver_id']] = $val['driver_id'];
                            }
                        }
                        $responseData = '{"code":202,"msg":"driver fetch success","drivers":' . json_encode($driversArr) . '}';
                    }else{
                        $responseData = '{"code":201,"msg":"no driver found","drivers":' . json_encode($drivers) . '}';
                    }
                    
                }else{
                    $responseData = $this->echoInvalidToken();
                }
            }else{
                 $responseData = '{"code":203,"msg":"service not provided in your area"}';
            }
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_finddrivers');

        echo $responseData;exit;
        
    }
    
    Public function verfiyotpAction(){
        
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'passenger_verfiyotp');
    
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $phone = $this->_getParam('phone');
        $name = $this->_getParam('name');
        $otpCode = $this->_getParam('otp');
        
        $sql = "select * from user where usertype!=1 and phone=:phone";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {

            //checkOTP first
            $sql = "select id from otp where otp=:otp and phone=:phone and expired=0";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otpCode);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $sql = "select * from user where usertype=1 and phone=:phone";

                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    //update name
                    $sql = "update user SET name=:name where phone=:phone";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':name', $name);
                    $stmt->execute();

                    $token = $this->generateToken(8, 4);

                    $sql = "select id from user where usertype=1 and phone=:phone";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->execute();
                    $row = $stmt->fetchObject();
                    $id = $row->id;

                    $sql = "update token SET expire=1 where userid=:userid";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':userid', $id);
                    $stmt->execute();

                    $sql = "update otp SET expired=1 where phone=:phone";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->execute();

                    $sql = "insert into token(userid,token) values (:userid,:token)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':userid', $id);
                    $stmt->bindParam(':token', $token);
                    $stmt->execute();

                    $sql = "select * from constants where name='AD_FREEQUENCY'";
                    $stmt = $db->query($sql);
                    $stmt->execute();
                    $row = $stmt->fetchObject();
                    $adFreeq = $row->values;

                    $responseData =  '{"code":191,"token":"' . $token . '","msg":"OTP verfication success","name":"' . $name . '","ad_freeq":' . $adFreeq . '}';
                } else {

                    //insert phone and name
                    $sql = "insert into user(phone,name,usertype) values (:phone,:name,1)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->bindParam(':name', $name);

                    $stmt->execute();

                    $sql = "select id from user where usertype=1 and phone=:phone";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->execute();
                    $row = $stmt->fetchObject();
                    $id = $row->id;

                    $sql = "update token SET expire=1 where userid=:userid";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':userid', $id);
                    $stmt->execute();

                    $sql = "update otp SET expired=1 where phone=:phone";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':phone', $phone);
                    $stmt->execute();

                    $token = $this->generateToken(8, 4);

                    $sql = "insert into token(userid,token) values (:userid,:token)";
                    $stmt = $db->prepare($sql);
                    $stmt->bindParam(':userid', $id);
                    $stmt->bindParam(':token', $token);

                    $stmt->execute();


                    $sql = "select * from constants where name='AD_FREEQUENCY'";
                    $stmt = $db->query($sql);
                    $stmt->execute();
                    $row = $stmt->fetchObject();
                    $adFreeq = $row->values;


                    $responseData = '{"code":191,"token":"' . $token . '","msg":"OTP verfication success","name":"' . $name . '","ad_freeq":' . $adFreeq . '}';
                }
            } else {
                $responseData = '{"code":193,"msg":"OTP verification failed.Invalid OTP code"}';
            }
        } else {
            $responseData = '{"code":192,"msg":"OTP verification failed.Already registered as a driver"}';
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_verfiyotp');

        echo $responseData;exit;
    }
    
    public function requestotpAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'passenger_requestotp');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        
        $sql = "select * from user where usertype!=1 and phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $responseData = '{"code":183,"msg":"User registered as driver"}';
        } else {
            
            $sql = "update otp SET expired=1 where phone=:phone";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            
            $otp=rand(100000, 999999);
//            $otp = '1234';
            $sql = "insert into otp(phone,otp) values (:phone,:otp)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otp);
            $stmt->execute();
            
            $this->requestOtpServer($phone,$otp);

            $responseData = '{"code":179,"msg":"OTP request success"}';
        }
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_requestotp');
    
        echo $responseData;exit;
    }
    
    public function requestOtpServer($phone,$otp){
        if($phone != '' && $otp != ''){
            $msg = 'BYGO OTP code is '.$otp;
            $result = $this->_commonM->sendSms($phone, $msg);
            $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'mobile' . ' <-- ' . $phone . '  msg --> ' . $msg .' result -->'.$result. PHP_EOL;
            $this->webservicesLog($string, 'passenger_smslog');
        }
        return true;
    }
    
    public function firsttimeAction(){
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sqlArea = "select id,name from area LIMIT 0,1";
        $stmtArea = $db->query($sqlArea);
        $area = $stmtArea->fetchAll(PDO::FETCH_OBJ);
        $responseData = '{"code":182,"msg":"firsttime success","area":' . json_encode($area) . '}';
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . ' response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_firsttime');
    
        echo $responseData;exit;
    }
    
    public function requestresendotpAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'passenger_requestresendotp');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        
        $sql = "select * from user where usertype!=1 and phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $responseData = '{"code":183,"msg":"User registered as driver"}';
        } else {
            
            $sql = "update otp SET expired=1 where phone=:phone";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            
            $otp=rand(100000, 999999);
             //$otp = '1234';
            $sql = "insert into otp(phone,otp) values (:phone,:otp)";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otp);
            $stmt->execute();
            
            $this->requestOtpServer($phone,$otp);

            $responseData = '{"code":300,"msg":"OTP request success"}';
        }
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'passenger_requestresendotp');
    
        echo $responseData;exit;
    }
    
    
}
?>
