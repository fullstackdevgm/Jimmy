<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\GoalsApi;

class GoalsApiFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
		$goalsapi_service = new GoalsApi();
		// Set the api service
		$goalsapi_service->setApiService($serviceLocator->get('jimmybase_ananlytics_api_service'));
    
		return $goalsapi_service;
    }
}

