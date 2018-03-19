<?php
namespace Eway\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Eway\Service\Token;

class TokenPaymentFactory  implements FactoryInterface 
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

		
		$token_api_service = new Token($eway_api_config);

		return $token_api_service;
    }
}

