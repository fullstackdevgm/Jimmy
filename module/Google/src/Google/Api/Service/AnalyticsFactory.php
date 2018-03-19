<?php

namespace Google\Api\Service;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class AnalyticsFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
	
		$config 		   = $serviceLocator->get('Config');
		$google_api_config = $config['google-api-config'];
		
		
		$config = array(
					'user_agent' 	  => $google_api_config['user_agent'],			  
					'developer_token' => $google_api_config['developer_token'],
					'client_id'       => $google_api_config['client_id'],
					'client_secret'   => $google_api_config['client_secret'],
					'redirect_uri'    => $google_api_config['redirect_uri']
		 );
		//var_dump($config);exit;
		$analytics_api_service = new \Google\Api\Service\Analytics($config);
        return $analytics_api_service;
    }
}

