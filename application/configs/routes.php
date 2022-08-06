<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

	$hostRoute1 = new Zend_Controller_Router_Route_Hostname(
	'bygo.oreolabs.com',
	      array(
		  'module'    => 'Admin',
		  'controller'=> 'index',
		  'action'    => 'index',

	      )
	);
	$plainHostRoute1 = new Zend_Controller_Router_Route_Static('');
	$router->addRoute('admin',$hostRoute1->chain($plainHostRoute1));
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger',
        array(
            'action'        => 'index',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/directionsapi',
        array(
            'action'        => 'directionsapi',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/directionsapi',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/trackdriver',
        array(
            'action'        => 'trackdriver',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/trackdriver',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/finddrivers',
        array(
            'action'        => 'finddrivers',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/finddrivers',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/verfiyotp',
        array(
            'action'        => 'verfiyotp',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/verfiyotp',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/requestotp',
        array(
            'action'        => 'requestotp',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/requestotp',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/firsttime',
        array(
            'action'        => 'firsttime',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/firsttime',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/firsttime',
        array(
            'action'        => 'firsttime',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/firsttime',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/requestotp',
        array(
            'action'        => 'requestotp',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/requestotp',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/requestotpregistraion',
        array(
            'action'        => 'requestotpregistraion',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/requestotpregistraion',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/verfiyotp',
        array(
            'action'        => 'verfiyotp',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/verfiyotp',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/setonline_offline',
        array(
            'action'        => 'setonlineoffline',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/setonline_offline',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/find_area_near',
        array(
            'action'        => 'findareanear',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/find_area_near',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/check_driver_approval',
        array(
            'action'        => 'checkdriverapproval',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/check_driver_approval',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/verfiyotp_registration',
        array(
            'action'        => 'verfiyotpregistration',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/verfiyotp_registration',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/driver_location',
        array(
            'action'        => 'driverlocation',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/driver_location',$route);
        
         $route = new Zend_Controller_Router_Route_Static(
        '/Admin/Approval',
        array(
            'action'        => 'index',
            'controller'    => 'Approval',
            'module'        =>  'Admin'
        ));
        $router->addRoute('/Admin/Approval',$route);
       
         $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/requestresendotp',
        array(
            'action'        => 'requestresendotp',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/requestresendotp',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/driver/requestresendotpregistration',
        array(
            'action'        => 'requestresendotpregistration',
            'controller'    => 'driver',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/driver/requestresendotpregistration',$route);
        
        $route = new Zend_Controller_Router_Route_Static(
        '/api/v1/passenger/requestresendotp',
        array(
            'action'        => 'requestresendotp',
            'controller'    => 'user',
            'module'        =>  'Api'
        ));
        $router->addRoute('/api/v1/passenger/requestresendotp',$route); 

        $route = new Zend_Controller_Router_Route_Static(
        '/TermsAndConditions',
        array(
            'action'        => 'terms',
            'controller'    => 'Index',
            'module'        =>  'Admin'
        ));
        $router->addRoute('/TermsAndConditions',$route);