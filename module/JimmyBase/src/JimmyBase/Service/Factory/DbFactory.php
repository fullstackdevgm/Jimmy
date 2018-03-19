<?php

namespace JimmyBase\Service\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;

class DbFactory implements FactoryInterface
{
    
	
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
    	$config 		 = $serviceLocator->get('Config');
		

		$db_config  = $config['db-config'];
		

		return new Adapter(array(
                    'driver'    => 'pdo',
                    'dsn'       => 'mysql:dbname='.$db_config['database'].';host='.$db_config['hostname'],
                    'database'  => $db_config['database'],
                    'username'  => $db_config['username'],
                    'password'  => $db_config['password'],
                    'hostname'  => $db_config['hostname'],
                ));
	}
}

