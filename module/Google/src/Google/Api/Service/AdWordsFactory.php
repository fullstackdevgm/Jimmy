<?php

namespace Google\Api\Service;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class AdWordsFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
	
		$config 		   = $serviceLocator->get('Config');
		$google_api_config = $config['google-api-config'];
		
		
		$config = array(
					'user_agent' 		    => $google_api_config['user_agent'],			  
					'developer_token'	    => $google_api_config['developer_token'],
					'oauth2_info'			=> array('client_id'     => $google_api_config['client_id'],
													 'client_secret' => $google_api_config['client_secret'])
		 );
		 
		$adwords_api_service = new \Google\Api\Service\AdWords($config);




        return $adwords_api_service;
    }
}

