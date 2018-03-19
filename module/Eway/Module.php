<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Eway;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Cache\StorageFactory as CacheStorageFactory;
use Zend\Session\Container as SessionContainer;


class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{ 

	public function init(ModuleManager $moduleManager)
    {
		
		
		

    }
	
	
    public function onBootstrap(MvcEvent $e)
    {
	 }
	 
	 
	
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
		
        return array(
			 'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	public function getServiceConfig(){
	
        return array(
            'factories' => array(
				//Factory Classes
				'eway_recurring_api'     => 'Eway\Service\Factory\RecurringFactory',
                'eway_directpayment_api' => 'Eway\Service\Factory\DirectPaymentFactory',
				'eway_tokenpayment_api' => 'Eway\Service\Factory\TokenPaymentFactory',
				
            ),
        );
    }
	
	
	

}
