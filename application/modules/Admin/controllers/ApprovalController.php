<?php
class Admin_ApprovalController extends Zend_Controller_Action
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
        $this->view->title = "Bygo - Approval Listing";
        
        $where = 'id > 0';
        $registerArr = $this->_tblDriverRegistration->selectData(array('*'),$where,'id'.' '.'desc');
      
        $data['area_arr']  = $this->_commonM->getAreaName();
        $data['user_arr']  = $this->_commonM->getUserType();
        $data['approval_listing'] = $registerArr;
        $this->view->data = $data;
        $this->render('index');
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
    
    public function approvedriverAction(){
        $id = $this->_getParam('id');
        if($id > 0){
            $driverArr = $this->_tblDriverRegistration->selectData(array('*'),'id='.$id);
            if(count($driverArr) > 0){
                $whereUser = 'phone = "'.$driverArr[0]['phone'].'"';
                $userData = $this->_tblUser->selectData(array('id','usertype'),$whereUser);
                
                if(count($userData) == 0){
                    $updateUser = array(
                        'name'      => $driverArr[0]['name'],
                        'phone'     => $driverArr[0]['phone'],
                        'usertype'  => $driverArr[0]['usertype'],
                        'update_time' => date('Y-m-d H:i:s')
                    );
                    
                    $userId = $this->_tblUser->insertRow($updateUser,TRUE);
                    
                    if($userId){
                        $token = $this->generateToken(8,4);
                        $insertToken = array(
                            'userid' => $userId,
                            'token'  => $token,
                            'expire' => 0
                        );
                        
                        $tokenInsert = $this->_tblToken->insertRow($insertToken);
                        
                        $driverInsert = array(
                            'userid'  => $userId,
                            'area_id' => $driverArr[0]['areaid'] 
                        );
                        $driverid = $this->_tblDriver->insertRow($driverInsert,TRUE);
                        
                        if($driverid){
                            $onlineInsert = array(
                                'driver_id' => $driverid,
                                'status'    => 0,
                                'update_time' => date('Y-m-d H:i:s')
                            );
                            
                            $insertOnline = $this->_tblDriverOnline->insertRow($onlineInsert);
                            
                            $locationinsert = array(
                                'driver_id' => $driverid,
                                'lat'       => 0,
                                'lng'       => 0,
                                'accuracy'  => 0,
                                'update_time' => date('Y-m-d H:i:s')
                            );
                            
                            $locationArr = $this->_tblDriverLocation->insertRow($locationinsert);
                            $updateRegistr = $this->_tblDriverRegistration->updateRow(array('userid' => $userId,'verify_status' => 1, 'modified_time' => date('Y-m-d H:i:s')),array('id' => $id));
                            
                            if($driverArr[0]['fcm_id'] !='' && $driverArr[0]['fcm_id'] != null){
                                $getData = array(
                                    'title' => 'Approved',
                                    'body'  => 'Successfully Approved.'
                                );
                                $resultfcm = $this->_commonM->sendfcmnotification($driverArr[0]['fcm_id'], $getData);
                            }
                            $responseData = array('respo' => 1,'msg' => 'Driver Approved Successfully');
                        }else{
                            $responseData = array('respo' => 0,'msg' => 'Something went wrong. Please try again');
                        }
                    }else{
                        $responseData = array('respo' => 0,'msg' => 'Something went wrong. Please try again');
                    }
                }else{
                    if($userData[0]['usertype'] == 1){
                        $responseData = array('respo' => 0,'msg' => 'Already registered as passenger');
                    }else{
                        $responseData = array('respo' => 0,'msg' => 'Already registered as driver');
                    }
                }
            }else{
                $responseData = array('respo' => 0,'msg' => 'Something went wrong. Please try again');
            }
        }else{
            $responseData = array('respo' => 0,'msg' => 'Something went wrong. Please try again');
        }
        echo json_encode($responseData);
        exit;
    }
    
    
    

}
?>
