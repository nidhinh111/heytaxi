<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of adminBootstrap
 *
 * @author Nidhin
 */

   
    class Api_Bootstrap extends Zend_Application_Module_Bootstrap {

    protected function _initAutoload()
    {
      $autoloader = new Zend_Application_Module_Autoloader(array(
           'namespace' => 'Api_',
           'basePath' => dirname(__FILE__)
      ));
      return $autoloader;
     }
}    


