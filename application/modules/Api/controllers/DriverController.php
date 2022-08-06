<?php
class Api_DriverController extends Zend_Controller_Action
{
    private $_tblAds, $_tblArea, $_tblConstants, $_tblDriver, $_tblDriverLocation, $_tblDriverOnline, $_tblDriverRegistration, $_tblOtp, $_tblPassenger, $_tblToken, $_tblUser, $_tblUserType, $_commonM;

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
    
    public function requestotpAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_requestotp');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        
        $sql = "select * from user where phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $responseData = '{"code":185,"msg":"Driver registration needed"}';
        } else {
            
            $sql = "select * from user where usertype!=1 and phone=:phone";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
            
            if($stmt->rowCount() == 0){
                $responseData = '{"code":184,"msg":"Already registered as passenger"}';
            }else{
                $sql = "update otp SET expired=1 where phone=:phone";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->execute();
            
                $otp=rand(100000, 999999);
            
//                $otp = '1234';
                $sql = "insert into otp(phone,otp) values (:phone,:otp)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':otp', $otp);
                $stmt->execute();
            
                $this->requestOtpServer($phone,$otp);

                $responseData = '{"code":179,"msg":"OTP request success"}';
            }
        }
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'driver_requestotp');
    
        echo $responseData;exit;
    }
    
    public function requestOtpServer($phone,$otp){
        if($phone != '' && $otp != ''){
            $msg = 'BYGO DRIVER OTP code is '.$otp;
            $result = $this->_commonM->sendSms($phone, $msg);
            $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'mobile' . ' <-- ' . $phone . '  msg --> ' . $msg .' result -->'.$result. PHP_EOL;
            $this->webservicesLog($string, 'driver_smslog');
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
        $this->webservicesLog($string, 'driver_firsttime');
    
        echo $responseData;exit;
    }
    
    public function requestotpregistraionAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData). PHP_EOL;
        $this->webservicesLog($string, 'driver_requestotpregistraion');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        
        $sql = "select id from user where  phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
             $sql = "select id from user where usertype!=1 AND phone=:phone";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $responseData = '{"code":186,"msg":"Number already registered"}';
            }else{
                $responseData = '{"code":184,"msg":"Already registered as passenger"}';
            }
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
        $this->webservicesLog($string, 'driver_requestotpregistraion');
    
        echo $responseData;exit;
    }
    
    public function verfiyotpAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_verfiyotp');
    
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        $name = $this->_getParam('name');
        $otpCode = $this->_getParam('otp');
        
        $sql = "select * from user where usertype!=1 and phone=:phone";

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        
        if($stmt->rowCount() > 0){
            
            //checkOTP first
            $sql = "select id from otp where otp=:otp and phone=:phone and expired=0";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otpCode);

            $stmt->execute();
            
            if($stmt->rowCount() > 0){
                //update name
                $sql = "update user SET name=:name where phone=:phone";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':name', $name);
                $stmt->execute();
                
                $token = $this->generateToken(8, 4);
                
                $sql = "select id from user where usertype != 1 and phone=:phone";
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
                
                $responseData =  '{"code":191,"token":"' . $token . '","msg":"OTP verfication success","name":"' . $name . '"}';

            }else{
                $responseData = '{"code":193,"msg":"OTP verification failed.Invalid OTP code"}';
            }
        }else{
            
            $sql = "select * from user where usertype = 1 and phone=:phone";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            
            if($stmt->rowCount() > 0){
                $responseData = '{"code":194,"msg":"OTP verification failed.Already registered as a user"}';
            }else{
                $responseData = '{"code":190,"msg":"Please register"}';
            }
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData). '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'driver_verfiyotp');

        echo $responseData;exit;
    }
    
    public function setonlineofflineAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_setonline_offline');
    
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $token = $this->_getParam('token');
        $status = $this->_getParam('online');
    
        $responseData = '{"code":180,"msg":"status change failed"}';
        
        $sql = "select userid from token where expire = 0 and token=:token";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $row = $stmt->fetchObject();
        
        if(count($row) == 1){
            $userid = $row->userid;
            $sql = "select id from driver where userid=:userid";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':userid', $userid);
            $stmt->execute();
            $driveridRow = $stmt->fetchObject();
            
            if(count($driveridRow) == 1){
                $driverId = $driveridRow->id;
                
                $sql = "select * from driver_online where driver_id=:driver_id";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':driver_id',$driverId);
                $stmt->execute();
                $onlineData = $stmt->fetchObject();
                
                if(count($onlineData) == 1){
                    if($status == 1){
                        $msg='online';
                    }else{
                        $msg = 'offline';
                    }
                       
                    if($status != $onlineData->status){
                        $sql = "update driver_online SET status=:status,update_time = '".date('Y-m-d H:i:s')."' where driver_id='".$onlineData->driver_id."'";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(':status', $status);
                        $stmt->execute();
                        
                        
                        $responseData = '{"code":271,"msg":"change '.$msg.' sucess","current_state":'.$status.'}';
                    }else{
                        $responseData = '{"code":272,"msg":"change '.$msg.' failed","current_state":'.$onlineData->status.'}';
                    }
                }
            }
        }else{
            $responseData = $this->echoInvalidToken();
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'driver_setonline_offline');
    
        echo $responseData;exit;
        
    }
    
    public function findareanearAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_find_area_near');
    
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $lat = $this->_getParam('lat');
        $lng = $this->_getParam('lng');
        $token = $this->_getParam('token');
        
        $sql = "select userid from token where expire = 0 and token=:token";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $row = $stmt->fetchObject();
        
        if(count($row) == 1){
        
            $sql = "SELECT * ,( 6371* acos( cos( radians(:lat) ) * cos( radians( lat ) ) 
                * cos( radians( lng ) - radians(:lng) ) + sin( radians(:lat) ) 
                * sin( radians( lat ) ) ) ) AS distance FROM area where status=1 
                HAVING distance < 10 ORDER BY distance";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':lat', $lat);
            $stmt->bindParam(':lng', $lng);
            $stmt->execute();
            $areas = $stmt->fetchAll(PDO::FETCH_OBJ);

            if ($stmt->rowCount() > 0) {
                $responseData = '{"code":250,"msg":"near area fetch success","area":'.json_encode($areas).'}';
            }else{
                $responseData = '{"code":252,"msg":"no near area found"}';
            }
         }else{
             $responseData = $this->echoInvalidToken();
         }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'driver_find_area_near');
    
        echo $responseData;exit;
        
    }
    
    public function checkdriverapprovalAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'check_driver_approval');
    
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $token = $this->_getParam('token');
        
        $sql = "select userid,verify_status from driver_registration where temp_token=:temp_token";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':temp_token', $token);
        $stmt->execute();
        $row = $stmt->fetchObject();
        
        if(count($row) == 1){
            $verifyStatus = $row->verify_status;
            $userid       = $row->userid;
            if($verifyStatus == 0){
                $responseData = '{"code":262,"msg":"Driver approval pending"}';
            }else if($verifyStatus == 1){
                $sql = "select token from token where expire = 0 and userid='".$userid."'";
                $stmt = $db->prepare($sql);
                $stmt->execute();
                $row = $stmt->fetchObject();
                $rider_token = $row->token;
                $responseData = '{"code":263,"token":"'.$rider_token.'","msg":"Driver approval sucess"}';
            }else{
                $responseData = '{"code":265,"msg":"Driver approval rejected"}';
            }
            
        }else{
            $responseData = '{"code":261,"msg":"driver approval failed"}';
        }
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'check_driver_approval');
    
        echo $responseData;exit;
    }
    
    public function verfiyotpregistrationAction() {
        $otp = $this->_getParam('otp');
        $phone = $this->_getParam('phone');
        $name = $this->_getParam('name');
        $area_id = $this->_getParam('area_id');
        $licenseFront = $_FILES['image_license_front'];
        $licenseBack = $_FILES['image_license_back'];
        $vehicleType = $this->_getParam('vehicle_type');
        $vehicleNumber = $this->_getParam('vehicle_number');
        $fcm_id        = $this->_getParam('fcmid');

        $requestData = array(
            'otp' => $otp,
            'phone' => $phone,
            'name' => $name,
            'area_id' => $area_id,
            'license_front' => $licenseFront,
            'license_back' => $licenseBack,
            'vehicle_type' => $vehicleType,
            'vehicle_number' => $vehicleNumber,
            'fcm_id' => $fcm_id
        );


        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_verfiyotp_registration');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sql = "select id from user where phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if($stmt->rowCount() == 0){
            //checkOTP first
            $sql = "select id from otp where otp=:otp and phone=:phone and expired=0";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':otp', $otp);
            $stmt->execute();
        
            if($stmt->rowCount() > 0){
                
                $sql = "select id from driver_registration where phone=:phone and verify_status IN (0,1)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->execute();
                
                if($stmt->rowCount() == 0){
                    $responseData = '{"code":264,"msg":"Soemthing went wrong. Please try again"}';
                    
                    $Imageextensions = array("jpeg", "jpg", "png"); //valid image extentions
                
                    if(isset($licenseFront['name']) && isset($licenseBack['name'])){
                        $frontImageExt = strtolower(pathinfo($licenseFront['name'], PATHINFO_EXTENSION));
                        $backImageExt  = strtolower(pathinfo($licenseBack['name'], PATHINFO_EXTENSION));
                    
                        if(in_array($frontImageExt, $Imageextensions) && in_array($backImageExt, $Imageextensions)){
                            $uploadPath = realpath(dirname(dirname(dirname(dirname(dirname(__FILE__)))))). DIRECTORY_SEPARATOR ."public". DIRECTORY_SEPARATOR . "driver_license" . DIRECTORY_SEPARATOR . $phone .DIRECTORY_SEPARATOR;
                        
                            if(!file_exists($uploadPath)) {
                                mkdir($uploadPath, 0777, true);
                            }
                            $licenseFrontName = preg_replace('/\s+/', '', $licenseFront['name']);
                            $licenseBackName = preg_replace('/\s+/', '', $licenseBack['name']);
                        
                            if (move_uploaded_file($licenseFront['tmp_name'], $uploadPath .date('YmdHis'). $licenseFrontName)) {
                                if(move_uploaded_file($licenseBack['tmp_name'], $uploadPath .date('YmdHis'). $licenseBackName)) {
                                    $licenseFrontUrl = "driver_license" . DIRECTORY_SEPARATOR . $phone .DIRECTORY_SEPARATOR. date('YmdHis').$licenseFrontName;
                                    $licenseBackUrl = "driver_license" . DIRECTORY_SEPARATOR . $phone .DIRECTORY_SEPARATOR. date('YmdHis').$licenseBackName;
                                
                                    $tempToken = $this->generateToken(8, 4);
                                    $sql = "insert into driver_registration(name,phone,usertype,areaid,temp_token,fcm_id,license_front,license_back,vehicle_number) values (:name,:phone,:usertype,:areaid,:temp_token,:fcm_id,:license_front,:license_back,:vehicle_number)";
                                    $stmt = $db->prepare($sql);
                                    $stmt->bindParam(':name', $name);
                                    $stmt->bindParam(':phone', $phone);
                                    $stmt->bindParam(':usertype', $vehicleType);
                                    $stmt->bindParam(':areaid', $area_id);
                                    $stmt->bindParam(':temp_token', $tempToken);
                                    $stmt->bindParam(':fcm_id',$fcm_id);
                                    $stmt->bindParam(':license_front', $licenseFrontUrl);
                                    $stmt->bindParam(':license_back', $licenseBackUrl);
                                    $stmt->bindParam(':vehicle_number', $vehicleNumber);
                                    $stmt->execute();
                                
                                    $sql = "update otp SET expired=1 where phone=:phone";
                                    $stmt = $db->prepare($sql);
                                    $stmt->bindParam(':phone', $phone);
                                    $stmt->execute();
                                
                                    $responseData = '{"code":191,"token":"'.$tempToken.'","msg":"OTP verfication success","name":"'.$name.'"}';
                                }
                            }
                        }else{
                            $responseData = '{"code":264,"msg":"Please select jpeg,jpg or png format image"}';
                        }
                    }
                }else{
                    $responseData = '{"code":194,"msg":"OTP verification failed.Already registered"}';
                }
            }else{
                $responseData = '{"code":193,"msg":"OTP verification failed.Invalid OTP code"}';
            }
        }else{
            $responseData = '{"code":194,"msg":"OTP verification failed.Already registered"}';
        }
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'driver_verfiyotp_registration');
    
        echo $responseData;exit;
    }
    
    public function driverlocationAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'update_driver_location');
    
        $token  = $this->_getParam('token');
        $lat    = $this->_getParam('lat');
        $lng    = $this->_getParam('lng');
        $acc    = $this->_getParam('acc');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        
        $sql = "select userid from token where expire = 0 and token=:token";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $row = $stmt->fetchObject();
        
        if(count($row) == 1){
            $userid = $row->userid;
            $sql = "select id from driver where userid=:userid";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':userid', $userid);
            $stmt->execute();
            $driveridRow = $stmt->fetchObject();
            
            if(count($driveridRow) == 1){
                $driverId = $driveridRow->id;
                //update driver lat long and accuracy
                $sql = "insert into driver_location(driver_id,lat,lng,accuracy) values (:driver_id,:lat,:lng,:accuracy)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':driver_id',$driverId);
                $stmt->bindParam(':lat', $lat);
                $stmt->bindParam(':lng', $lng);
                $stmt->bindParam(':accuracy', $acc);
                $stmt->execute();
            
                $responseData = '{"code":282,"msg":"driver location success"}';
            }
        }else{
            $responseData = $this->echoInvalidToken();
        }
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'update_driver_location');
    
        echo $responseData;exit;
    }
    
    public function requestresendotpAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_requestresendotp');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        
        $sql = "select * from user where phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $responseData = '{"code":185,"msg":"Driver registration needed"}';
        } else {
            
            $sql = "select * from user where usertype!=1 and phone=:phone";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
            
            if($stmt->rowCount() == 0){
                $responseData = '{"code":184,"msg":"Already registered as passenger"}';
            }else{
                $sql = "update otp SET expired=1 where phone=:phone";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->execute();
            
                $otp=rand(100000, 999999);
            
//                $otp = '1234';
                $sql = "insert into otp(phone,otp) values (:phone,:otp)";
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':otp', $otp);
                $stmt->execute();
            
                $this->requestOtpServer($phone,$otp);

                $responseData = '{"code":310,"msg":"OTP request success"}';
            }
        }
        
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . '  response --> ' . $responseData . PHP_EOL;
        $this->webservicesLog($string, 'driver_requestresendotp');
    
        echo $responseData;exit;
    }

    public function requestresendotpregistrationAction(){
        $requestData = $this->getAllParams();
        $string = PHP_EOL . '[' . date('Y-m-d H:i:s') . '] ' . 'request' . ' <-- ' . json_encode($requestData) . PHP_EOL;
        $this->webservicesLog($string, 'driver_requestresendotpregistration');
        
        $db = Zend_Db_Table_Abstract::getDefaultAdapter();
        $phone = $this->_getParam('phone');
        
        $sql = "select id from user where  phone=:phone";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
             $sql = "select id from user where usertype!=1 AND phone=:phone";
            $stmt = $db->prepare($sql);
            $stmt->bindParam(':phone', $phone);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $responseData = '{"code":186,"msg":"Number already registered"}';
            }else{
                $responseData = '{"code":184,"msg":"Already registered as passenger"}';
            }
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
        $this->webservicesLog($string, 'driver_requestresendotpregistration');
    
        echo $responseData;exit;
    }
}
?>
