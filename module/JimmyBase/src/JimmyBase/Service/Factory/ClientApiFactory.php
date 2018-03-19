<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\ClientApi;

class ClientApiFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {		
			$plugins = $serviceLocator->get('ControllerPluginManager');
			$plugin  = $plugins->get('UserAuthentication');
	
			$current_user = $plugin->getIdentity();
	
			$client_api_service = new ClientApi();
			
			$client_api_service->setApiService($serviceLocator->get('jimmybase_adwords_api_service'));
			
		return $client_api_service;
					
    }
}

