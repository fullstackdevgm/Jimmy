<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\DataGaApi;

class DataGaApiFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
		$dataga_api = new DataGaApi();
		// Set the api service
		$dataga_api->setApiService($serviceLocator->get('jimmybase_ananlytics_api_service'));
    
		return $dataga_api;
    }
}

