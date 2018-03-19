<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\CampaignApi;

class CampaignApiFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
		$campaign_service = new CampaignApi();
		// Set the api service
		$campaign_service->setApiService($serviceLocator->get('jimmybase_adwords_api_service'));
    
		return $campaign_service;
					
    }
}

