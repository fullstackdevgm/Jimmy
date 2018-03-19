<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\BraintreePayment;

class BtPaymentFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {		
		$config 		 = $serviceLocator->get('Config');
                $env = $config['jimmy-config']['jimmy-env'];               
		$btSettings  = $config['braintree-api-config'];	               
		$btPaymentService = new BraintreePayment($btSettings, $env);
			
		return $btPaymentService;
    }
}

