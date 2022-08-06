<?php
class Admin_IndexController extends Zend_Controller_Action
{
    
    
    public function indexAction()
    { echo '<head><title>BYGO</title></head><h3><center>BYGO... Coming Soon..</center><h3>';exit;}
    
    public function termsAction(){
        $this->render('terms');
    }
}
?>
