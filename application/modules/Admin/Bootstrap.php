<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adminBootstrap
 *
 * @author grab
 */

   
    class Admin_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoload()
    {
      $autoloader = new Zend_Application_Module_Autoloader(array(
           'namespace' => 'Admin_',
           'basePath' => dirname(__FILE__)
      ));
      return $autoloader;
     }
}    


