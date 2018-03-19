<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\BingAds;

class BingAdsFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
		$bingads = new BingAds();
		// Set the api service
		//$campaign_service->setApiService($serviceLocator->get('jimmybase_adwords_api_service'));
    
		return $bingads;
					
    }
}

