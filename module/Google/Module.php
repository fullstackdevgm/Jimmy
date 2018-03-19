<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Google;
use Zend\ModuleManager\ModuleManager;

class Module
{
	public function init(ModuleManager $moduleManager){

    $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
    $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
          $config = $e->getApplication()->getConfiguration();
          $controller = $e->getTarget();
          $controller->config = $config;
    });

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

	public function getServiceConfig()
    {

		return array(
            'factories' => array(
					'JimmyBase\Provider\Identity\ZfcUserZendDb' => 'JimmyBase\Service\ZfcUserZendDbIdentityProviderServiceFactory',


			)
		);


    }
}
