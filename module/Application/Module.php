<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Session\Container as SessionContainer;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ServiceProviderInterface {


    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        $eventManager = $e->getApplication()->getEventManager();


        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        #Authentication Check
        $sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
       
		// Notification Calls
        $notificationListener = $sm->get('jimmybase_notification_service');
        $notificationListener->attach($eventManager);
        $notificationListener->attachShared($sharedManager);


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
				 'zfcuser_user_mapper' => function ($sm) {
					$options = $sm->get('zfcuser_module_options');
					$mapper = new \JimmyBase\Mapper\User();
					$mapper->setUserType(array('agency','user'));
					$mapper->setDbAdapter($sm->get('zfcuser_zend_db_adapter'));

					$entityClass = $options->getUserEntityClass();

					$mapper->setEntityPrototype(new $entityClass);
					$mapper->setHydrator(new \JimmyBase\Mapper\UserHydrator());

					return $mapper;
				},
            ),
        );
    }

	public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
				'LoginWidget' => function ($sm) {
                    $locator    = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\LoginWidget;
                    $viewHelper->setViewTemplate('login');
                    $viewHelper->setLoginWidget(array('google-login-widget'));
                    return $viewHelper;
                },
                'LogoWidget' => function ($sm) {
                    $locator    = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\LogoWidget;
                    $viewHelper->setViewTemplate('application/index/logo.phtml');
                    $config = $locator->get('Config');

                    $viewHelper->setLogoConfig($config['logo-config']);
                    return $viewHelper;
                },
                'SidebarLogo' => function ($sm) {
                    $locator    = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\LogoWidget;
                    $viewHelper->setViewTemplate('application/index/sidebar-logo.phtml');
                    $config = $locator->get('Config');

                    $viewHelper->setLogoConfig($config['logo-config']);
                    return $viewHelper;
                },        
                
                
                'UserLogoWidget' => function ($sm) {
                    $locator    = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserLogoWidget;
                    $config = $locator->get('Config');
                    $viewHelper->setLogoConfig($config['logo-config']);
                    return $viewHelper;
                },
            ),
        );
	}

}
