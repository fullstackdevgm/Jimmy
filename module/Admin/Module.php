<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Cache\StorageFactory as CacheStorageFactory;
use Zend\Session\Container as SessionContainer;

use JimmyBase\Form as JimmyBaseForm;

class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{ 

	public function init(ModuleManager $moduleManager)
    {
	   
        
		$sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            // This event will only be fired when an ActionController under the Admin namespace is dispatched.
            $controller = $e->getTarget();
            $controller->layout('layout/admin');
        }, 100);
    }
	
    public function onBootstrap(MvcEvent $e)
    {
		$eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
		
		# Authentication Check 
		$sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
        //$sharedManager->attach(__NAMESPACE__, 'dispatch',  array($this, 'auth'), 101);
        //$sharedManager->attach(__NAMESPACE__, 'dispatch',  array($this, 'verify_api_access'), 99);
		
	 }
	 
	 
	public function verify_api_access(MvcEvent $e){
	     $eventManager   = $e->getApplication()->getEventManager();
		 $app			 = $e->getApplication();
	     $sm  			 = $app->getServiceManager();
		 $routeMatch     = $e->getRouteMatch();
		 $api_service    = $sm->get('jimmybase_adwords_api_service');
		 $auth           = $sm->get('zfcuser_auth_service');

	    if($routeMatch->getMatchedRouteName() == 'admin' ) {

			if ($auth->hasIdentity() ){
				 $userMapper  = $sm->get('zfcuser_user_mapper');
				 
				 $oauth2Info  = unserialize($userMapper->getMeta($auth->getIdentity()->getId(),'api_access'));
				
				
				  if(!$api_service->verifyApiAccess($oauth2Info)){
				   
						$response = $e->getResponse();
						$response->getHeaders()->addHeaderLine(
							'Location',
							$e->getRouter()->assemble(
									array(),
									array('name' => 'admin/oauthlogin')
							)
						);
						//$response->setStatusCode(302);
						//header('location:/admin/oauthlogin');
				  		//exit;
				  }
			 }
		}
	}
	
	 
	public function auth(MvcEvent $e){
		 $eventManager        = $e->getApplication()->getEventManager();
			echo 3;exit;
		 $app = $e->getApplication();
		 $routeMatch = $e->getRouteMatch();
		
		 $sm = $app->getServiceManager();
		 $auth = $sm->get('zfcuser_auth_service');
		 
		 $isAdmin = false;
		 
		 if($auth->hasIdentity())
	        $isAdmin = $auth->getIdentity()->getType() == 'admin';

		if ((!$isAdmin && $routeMatch->getMatchedRouteName() != 'admin/login' &&  $routeMatch->getMatchedRouteName() != 'admin' )) {
		
			$response = $e->getResponse();
			$response->getHeaders()->addHeaderLine(
				'Location',
				$e->getRouter()->assemble(
						array(),
						array('name' => 'admin')
				)
			);
			
			$response->setStatusCode(302);
			return $response;
		}

		
		 $user_auth  = new \ZfcUser\Controller\Plugin\ZfcUserAuthentication();
		 $identity   = $user_auth->setAuthService($auth);

		 
		if($identity->hasIdentity()){
			if($identity->getIdentity()->getType()=='agency'){
					
				 $api_service = $sm->get('jimmybase_adwords_api_service');
				 $userMapper  = $sm->get('jimmybase_user_mapper');
				 $oauth2Info  = unserialize($userMapper->getMeta($identity->getIdentity()->getId(),'api_access'));
	
				 $viewModel = $app->getMvcEvent()->getViewModel();
				 $viewModel->has_api_access = false;
	
				 if($api_service->verifyApiAccess($oauth2Info)){
					$viewModel = $app->getMvcEvent()->getViewModel();
					$viewModel->has_api_access = true;
				 }
			}
		}
			
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
            'invokables' => array(              
			    'Admin\Authentication\Adapter\Db' 	=> 'Admin\Authentication\Adapter\Db',
                'Admin\Authentication\Storage\Db' 	=> 'Admin\Authentication\Storage\Db', 				
            ),
            'factories' => array(
			 	'admin_auth_service' => function ($sm) {
                    return new \Zend\Authentication\AuthenticationService(
                        $sm->get('Admin\Authentication\Storage\Db'),
                        $sm->get('Admin\Authentication\Adapter\AdapterChain')
                    );
                },
                'Admin\Authentication\Adapter\AdapterChain' => 'Admin\Authentication\Adapter\AdapterChainServiceFactory',
               	'AdminNavigation' => 'Admin\Navigation\AdminNavigationFactory',
                'admin_client_register_form' => function ($sm) {
                    $form = new JimmyBaseForm\Client();
                   
                    return $form;
                },
				'admin_report_form' => function ($sm) {
                    $reportForm = new JimmyBaseForm\Report(null,$sm);
					$reportForm->setInputFilter(new JimmyBaseForm\ReportFilter());
                   return $reportForm;
                },		
				'admin_user_mapper' => function ($sm) {
                    $mapper = new \JimmyBase\Mapper\User();
					$mapper->setUserType('admin');
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = 'JimmyBase\Entity\User';
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\AdminUserHydrator());
                    return $mapper;
                },

				
            ),
        );
    }
	
	public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'adminUserAuthentication' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $authService = $serviceLocator->get('admin_auth_service');
                    $authAdapter = $serviceLocator->get('Admin\Authentication\Adapter\AdapterChain');
                    $controllerPlugin = new Controller\Plugin\AdminUserAuthentication;
                    	
					$controllerPlugin->setAuthService($authService);
                    $controllerPlugin->setAuthAdapter($authAdapter);
					
                    return $controllerPlugin;
                }, 
            ),
        );
    }
	
	public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'HasAdminUserIdentity' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\HasAdminUserIdentity;
                    $viewHelper->setAuthService($locator->get('admin_auth_service'));
                    return $viewHelper;
                },
                'UserDisplayName' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\UserDisplayName;
                    $viewHelper->setAuthService($locator->get('admin_auth_service'));
                    return $viewHelper;
                },
            ),
        );
	}

}
