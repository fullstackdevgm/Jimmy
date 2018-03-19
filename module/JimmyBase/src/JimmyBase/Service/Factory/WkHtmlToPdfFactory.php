<?php

namespace JimmyBase\Service\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use JimmyBase\Service\WkHtmlToPdf;

class WkHtmlToPdfFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
			return new WkHtmlToPdf();
		
	}
}

