<?php
namespace Eway\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Eway\Service\Recurring;

class RecurringFactory  implements FactoryInterface 
{	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {	
		$config 		 = $serviceLocator->get('Config');
		$eway_api_config = $config['eway-api-config'];
		
		$isTestMode  = $eway_api_config['test_mode'];
		
		if($eway_api_config['test_mode']) // Test Mode
		   $eway_api_config = $eway_api_config['sandbox'];
		 else							  // Live Mode
		   $eway_api_config = $eway_api_config['live'];
		   
		   $eway_api_config['test_mode'] = $isTestMode;

		
		$recurring_api_service = new Recurring($eway_api_config);

		return $recurring_api_service;
    }
}

