<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\Payment;

class PaymentFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {		
		$config 		 = $serviceLocator->get('Config');
		$jimmy_settings  = $config['jimmy-config'];
		
		$payment_service = new Payment($jimmy_settings);
			
		return $payment_service;
    }
}

