<?php

namespace JimmyBase\Service\Factory;

use JimmyBase\Provider\Identity\ZfcUserZendDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CacheFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
			return  \Zend\Cache\StorageFactory::factory(array(
									'adapter' => array(
										'name'    => 'filesystem',
										'options' => array(
											'cache_dir' => __DIR__ . '/../../../../../../data/cache',
											'ttl' 		=> 60*60*3,
											'namespace' => 'jimmy'
										),
									),
									'plugins' => array('exception_handler' => array('throw_exceptions' => false)),
							));
			
	}
}

