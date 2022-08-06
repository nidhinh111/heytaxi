<?php
class Admin_AreaController extends Zend_Controller_Action
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
        $this->view->title = "Bygo - Area Listing";
        
        $where = 'id > 0';
        $areaArr = $this->_tblArea->selectData(array('*'),$where,'id'.' '.'desc');
        $data['area_listing'] = $areaArr;
        $this->view->data = $data;
        $this->render('index');
    }
    
    public function changestatusAction(){
        $id = $this->_getParam('id');
        $status = $this->_getParam('status');
         $responseData = array('respo' => 0,'msg' => 'Something went wrong');
        if($id > 0 && $status != ''){
            $areaArr = $this->_tblArea->selectData(array('id'),'id='.$id);
            if(count($areaArr) > 0){
                $updateStatus = array(
                    'status'        => ($status == '1') ? '0' : '1',
                    'modified_time'   => date('Y-m-d H:i:s')
                );
                $update = $this->_tblArea->updateRow($updateStatus,array('id' => $id));
                $responseData = array('respo' => 1,'msg' => 'Area status changed successfully');
            }
        }
        echo json_encode($responseData);
        exit;
    }
    
    public function newareaAction(){
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if (trim($formData['area_name']) == '' || trim($formData['area_town']) == '' || trim($formData['area_district']) == '' || trim($formData['area_state']) == '' || trim($formData['latlong']) == '') {
                $this->view->messages = 'Please fill out all the required fields';
            } else {
                $latlong_arr = explode(",", $formData['latlong']);
                if($latlong_arr[0] != '' && $latlong_arr[1] != ''){
                    $whereDuplicate = 'name = "'.$formData['area_name'].'"';
                    $duplicateArr   = $this->_tblArea->selectData(array('id'),$whereDuplicate);
                    if(count($duplicateArr) > 0){
                        $this->view->messages = 'Area name already exists';
                    }else{
                        $insertArr   = array(
                            'name'   => trim($formData['area_name']),
                            'lat'    => (($latlong_arr[0] != '') ? $latlong_arr[0] : '0.0'),
                            'lng'    => (($latlong_arr[1] != '') ? $latlong_arr[1] : '0.0'),
                            'status' => '1',
                            'town'   => trim($formData['area_town']),
                            'district' => trim($formData['area_district']),
                            'state'  => trim($formData['area_state']),
                        );
                        $insert = $this->_tblArea->insertRow($insertArr,true);
                        if($insert > 0){
                            $this->redirect('/Admin/Area');
                        }
                    }
                }else{
                    $this->view->messages = 'Click on map marker to select lat long';
                }
            }
        }
        $this->render('newarea');
    }
    
    public function editareaAction(){
        $id = $this->_request->getParam('id', null);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if (trim($formData['area_name']) == '' || trim($formData['area_town']) == '' || trim($formData['area_district']) == '' || trim($formData['area_state']) == '' || trim($formData['latlong']) == '') {
                $this->view->messages = 'Please fill out all the required fields';
                $this->redirect('/Admin/Area/editarea/id/'.$formData['id']);
            } else {
                if($formData['id'] > 0){
                    $latlong_arr = explode(",", $formData['latlong']);
                    if($latlong_arr[0] != '' && $latlong_arr[1] != ''){
                        $whereDuplicate = 'name = "'.$formData['area_name'].'" AND id != "'.$formData['id'].'"';
                        $duplicateArr   = $this->_tblArea->selectData(array('id'),$whereDuplicate);
                        if(count($duplicateArr) > 0){
                            $this->view->messages = 'Area name already exists';
                            $this->redirect('/Admin/Area/editarea/id/'.$formData['id']);
                        }else{
                            $updateArr   = array(
                                'name'   => trim($formData['area_name']),
                                'lat'    => (($latlong_arr[0] != '') ? $latlong_arr[0] : '0.0'),
                                'lng'    => (($latlong_arr[1] != '') ? $latlong_arr[1] : '0.0'),
                                'status' => '1',
                                'town'   => trim($formData['area_town']),
                                'district' => trim($formData['area_district']),
                                'state'  => trim($formData['area_state']),
                            );
                            $update = $this->_tblArea->updateRow($updateArr,array('id' => $formData['id']));
                            if($update){
                                $this->redirect('/Admin/Area');
                            }
                        }
                    }else{
                        $this->view->messages = 'Click on map marker to select lat long';
                    }
                }else{
                    $this->redirect('/Admin/Area');
                }   
            }
        }else{
            if($id > 0){
                $areaArr = $this->_tblArea->selectData(array('*'),'id='.$id);
                if(count($areaArr) > 0){
                    $data['area_arr'] = $areaArr[0];
                    $this->view->data = $data;
                    $this->render('editarea');
                }else{
                    $this->redirect('/Admin/Area');
                }
            }else{
                $this->redirect('/Admin/Area');
            }
        }
    }
    
    public function getmapAction() {
        
        $postData = $this->_request->getPost();
        if(isset($postData['lat']) && $postData['lat']!= ''){
            $lat = $postData['lat'];
        }else{
            $lat = '11.258753';
        }
        if(isset($postData['long']) && $postData['long']!= ''){
            $long = $postData['long'];
        }else{
            $long = '75.78041';
        }
        $data['lat'] = $lat;
        $data['long'] = $long;
        
        $this->view->data = $data;                   
        $this->render('map');                
    }
    
    public function updatemapaddressAction() {
        
        $postData = array();
        if($this->_request->isPost()){
            $postData = $this->_request->getPost();
        }
        
        $address = $postData['address_from_map'];
        $lat_lon = $postData['lat_lon_from_map'];

        $lat_lon = str_replace('(','', $lat_lon);

        $lat_lon = str_replace(')','', $lat_lon);

        $lat_lon = str_replace(' ','', $lat_lon);       

        $data['address_from_map'] = $address;
        $data['lat_lon_from_map'] = $lat_lon;
         
        echo json_encode($data);exit();
       // print "<pre>";print_r($data);exit;
    } 

}
?>
