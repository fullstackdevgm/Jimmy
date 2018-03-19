<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Cache\StorageFactory as CacheStorageFactory;
use Zend\Session\Container as SessionContainer;
use Zend\EventManager\StaticEventManager;


class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
	{

	public function init(ModuleManager $moduleManager)
    {
		$sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function($e) {
            // This event will only be fired when an ActionController under the MyModule namespace is dispatched.
            $controller = $e->getTarget();
			$routeMatch = $e->getRouteMatch();
			list($route,)	= explode('/',$routeMatch->getMatchedRouteName());

			if($route == 'admin' )
            	$controller->layout('layout/admin');

        }, 100);


    }


    public function onBootstrap(MvcEvent $e)
    {
		$eventManager        = $e->getApplication()->getEventManager();

		// Exception and Error Handling

        /*$eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, function($event){
            $exception = $event->getResult()->exception;
            if (!$exception)
                 return;

                $sm = $event->getApplication()->getServiceManager();
                $service = $sm->get('error_handler');
                $service->logException($exception);
        },1);
		*/

		// Exception and Error Handling
	    $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, function($event){
            $exception = $event->getResult()->exception;
			if (!$exception)
       			 return;

                $sm = $event->getApplication()->getServiceManager();
                $service = $sm->get('error_handler');
                $service->logException($exception);

        },1);



        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

		//$sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
        $eventManager->attach('route',array($this,'loadConfiguration'),2);
	 }

     public function bootstrap(MvcEvent $e)
     {

         $app = $e->getApplication();
         $sm = $app->getServiceManager();

         $config = $sm->get('Config');
         $jimmy_settings  = $config['jimmy-config'];
         $router = $app->getRouter();
         $router->setBaseUrl($jimmy_settings['baseurl']);
     }

	 public function auth(MvcEvent $e){

		 $eventManager        = $e->getApplication()->getEventManager();

		 $app = $e->getApplication();
		 $routeMatch = $e->getRouteMatch();

		 $sm = $app->getServiceManager();
		 $auth = $sm->get('zfcuser_auth_service');

		 $route =  $routeMatch->getMatchedRouteName();
		 $routes = @explode('/',$route);

		if (!$auth->hasIdentity()) {
			$response = $e->getResponse();
			$response->getHeaders()->addHeaderLine(
				'Location',
				$e->getRouter()->assemble(
						array(),
						array('name' => 'home')
				)
			);
			$response->setStatusCode(302);
			return $response;
		}

		 //$viewModel = $app->getMvcEvent()->getViewModel();
		 //$viewModel->has_api_access = true;
		 $user_auth  = new \Application\Controller\Plugin\UserAuthentication();
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

	public function loadConfiguration(MvcEvent $e)
    {

		$application   = $e->getApplication();
		$sm            = $application->getServiceManager();
		$sharedManager = $application->getEventManager()->getSharedManager();

		$router  = $sm->get('router');
		$request = $sm->get('request');

	    $matchedRoute = $router->match($request);


		if ($matchedRoute== null) {
            // Redirect if route match not found
            $auth = $sm->get('zfcuser_auth_service');

            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine(
                'Location',
                $e->getRouter()->assemble(
                        array(),
                        array('name' => !$auth->hasIdentity()?'home':'dashboard')
                )
            );
           // $response->setStatusCode(302);
            //return $response;
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
                'jimmybase_user_service'  			=> 'JimmyBase\Service\User',
                'jimmybase_agency_service'  			=> 'JimmyBase\Service\Agency',
                'jimmybase_widget_service'  			=> 'JimmyBase\Service\Widget',
                'jimmybase_client_service'  			=> 'JimmyBase\Service\Client',
                'jimmybase_report_service'  			=> 'JimmyBase\Service\Report',
                'jimmybase_metrics_service' 			=> 'JimmyBase\Service\Metrics',
                'jimmybase_reportsapi_service'  		=> 'JimmyBase\Service\ReportsApi',
                'jimmybase_api_googleadwords' 		    => 'JimmyBase\Service\CampaignApi',
                'jimmybase_campaign_service' 		    => 'JimmyBase\Service\Campaign',
		        'jimmybase_reports_service'  			=> 'JimmyBase\Service\Reports',
                'jimmybase_reportshare_service'         => 'JimmyBase\Service\ReportShare',
                'jimmybase_reportschedule_service'      => 'JimmyBase\Service\ReportSchedule',
		        'jimmybase_notification_service'		=> 'JimmyBase\Service\Notification',
                'jimmybase_adwords_service'             => 'JimmyBase\Service\Adwords',
                'jimmybase_analytics_service'           => 'JimmyBase\Service\Analytics',
                'jimmybase_bingads_service'             => 'JimmyBase\Service\BingAds',
                'jimmybase_reportrenderer_service'      => 'JimmyBase\Service\ReportRenderer',
                'jimmybase_metricsformat_service'       => 'JimmyBase\Service\MetricsFormat',
                'jimmybase_tour_service'               => 'JimmyBase\Service\GuidedTour',
                'jimmybase_template_service'           => 'JimmyBase\Service\Template',
                'jimmybase_source_service'             => 'JimmyBase\Service\Source',
                'jimmybase_activity_log_service'       => 'JimmyBase\Service\ActivityLog',
                'jimmybase_analytics_insights_service' => 'JimmyBase\Service\AnalyticsInsights'
                ),
            'factories' => array(

				//Factory Classes
                'jimmybase_adwords_api_service'     => 'Google\Api\Service\AdWordsFactory',
                'jimmybase_ananlytics_api_service'  => 'Google\Api\Service\AnalyticsFactory',
                'jimmybase_dataga_api_service'  	=> 'JimmyBase\Service\Factory\DataGaApiFactory',
                'jimmybase_goalsapi_service'        => 'JimmyBase\Service\Factory\GoalsApiFactory',
                'jimmybase_campaignapi_service'     => 'JimmyBase\Service\Factory\CampaignApiFactory',
                'jimmybase_clientapi_service'	    => 'JimmyBase\Service\Factory\ClientApiFactory',
                'jimmybase_payment_service'		    => 'JimmyBase\Service\Factory\PaymentFactory',                
                'jimmybase_bt_payment_service'  => 'JimmyBase\Service\Factory\BtPaymentFactory',
                'cache'							    => 'JimmyBase\Service\Factory\CacheFactory',
                'WkHtmlToPdf'                       => 'JimmyBase\Service\Factory\WkHtmlToPdfFactory',
                
                'jimmybase_activity_log_mapper' => function ($sm) {
                    $mapper = new Mapper\ActivityLog();
                    
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\ActivityLog";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\ActivityLogHydrator());

                    return $mapper;
                },   
                'jimmybase_client_mapper' => function ($sm) {
                    $mapper = new Mapper\Client();

                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Client";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\ClientHydrator());

                    return $mapper;
                },                        
                'jimmybase_clientaccounts_mapper' => function ($sm) {
                    $mapper = new Mapper\ClientAccounts();

                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\ClientAccounts";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\ClientAccountsHydrator());

                    return $mapper;
                },
                'jimmybase_agency_mapper' => function ($sm) {
                    $mapper = new Mapper\Agency();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Agency";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\AgencyHydrator());

                    return $mapper;
                },
		'jimmybase_user_mapper' => function ($sm) {
                    $mapper = new Mapper\User();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\User";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\UserHydrator());

                    return $mapper;
                },
                'jimmybase_usertoken_mapper' => function ($sm) {
                    $mapper = new Mapper\UserToken();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\UserToken";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\UserTokenHydrator());

                    return $mapper;
                },
                'jimmybase_tour_mapper' => function ($sm) {
                    $mapper = new Mapper\Tour();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Tour";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\TourHydrator());
                    return $mapper;
                },
                'jimmybase_migration_mapper' => function ($sm) {
                    $mapper = new Mapper\Migration();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Migration";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\MigrationHydrator());
                    return $mapper;
                },
                'jimmybase_visited_tour_mapper' => function ($sm) {
                    $mapper = new Mapper\VisitedTour();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\VisitedTour";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\VisitedTourHydrator());
                    return $mapper;
                },
                'jimmybase_template_mapper' => function ($sm) {
                    $mapper = new Mapper\Template();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Template";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\TemplateHydrator());
                    return $mapper;
                },
                        
                 'jimmybase_braintree_payment_mapper' => function ($sm) {
                    $mapper = new Mapper\BraintreePayment();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\BraintreePayment";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\BraintreePaymentHydrator());
                    return $mapper;
                },
                 'jimmybase_template_widget_mapper' => function ($sm) {
                    $mapper = new Mapper\TemplateWidget();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\TemplateWidget";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\TemplateWidgetHydrator());
                    return $mapper;
                },                        
		'jimmybase_reports_mapper' => function ($sm) {
                    $mapper = new Mapper\Reports();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Reports";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\ReportsHydrator());

                    return $mapper;
                },
				'jimmybase_reportshare_mapper' => function ($sm) {
                    $mapper = new Mapper\ReportShare();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\ReportShare";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\ReportShareHydrator());

                    return $mapper;
                },
                'jimmybase_reportschedule_mapper' => function ($sm) {
                    $mapper = new Mapper\ReportSchedule();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\ReportSchedule";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\ReportScheduleHydrator());

                    return $mapper;
                },
		'jimmybase_widget_mapper' => function ($sm) {
                    $mapper = new Mapper\Widget();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Widget";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\WidgetHydrator());

                    return $mapper;
                },
		'jimmybase_package_mapper' => function ($sm) {
                    $mapper = new Mapper\Package();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\Package";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\PackageHydrator());

                    return $mapper;
                },
		'jimmybase_userpayments_mapper' => function ($sm) {
                    $mapper = new Mapper\UserPayments();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\UserPayments";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\UserPaymentsHydrator());

                    return $mapper;
                },
        'jimmybase_usercancellog_mapper' => function ($sm) {
                    $mapper = new Mapper\UserCancelLog();
                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\JimmyBase\\Entity\\UserCancelLog";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\UserCancelLogHydrator());

                    return $mapper;
                },
				'error_handler' =>  function($sm) {
                    $logger  = $sm->get('logger');
                    $service = new Service\ErrorHandler($logger);
                    return $service;
                },
				'logger' => function($sm){
       				$writer = new \Zend\Log\Writer\Stream('./data/log/'.date('Y-m-d').'-error.log');

					$log = new \Zend\Log\Logger();
					$log->addWriter($writer);


					\Zend\Log\Logger::registerErrorHandler($log);
					\Zend\Log\Logger::registerExceptionHandler($log);

					return $log;
				},


            ),
            'shared' => array('WkHtmlToPdf'=>false)
        );
    }



	public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'app' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $controllerPlugin = new Controller\Plugin\App($serviceLocator);

                    return $controllerPlugin;
                },
				'AclPlugin' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $controllerPlugin = new Controller\Plugin\AclPlugin($serviceLocator);

                    return $controllerPlugin;
                },
				'UserAuthentication' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $authService = $serviceLocator->get('zfcuser_auth_service');
                    $authAdapter = $serviceLocator->get('ZfcUser\Authentication\Adapter\AdapterChain');
                    $controllerPlugin = new \ZfcUser\Controller\Plugin\ZfcUserAuthentication;
                    $controllerPlugin->setAuthService($authService);
                    $controllerPlugin->setAuthAdapter($authAdapter);
                    return $controllerPlugin;
                },
				'AdWordsArguments' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();
                    $metricsService    = $serviceLocator->get('jimmybase_metrics_service');
                    $widgetService    = $serviceLocator->get('jimmybase_widget_service');

                    $controllerPlugin = new Controller\Plugin\AdWordsArguments;
                    $controllerPlugin->setMetricsService($metricsService);
                    $controllerPlugin->setWidgetService($widgetService);

                    return $controllerPlugin;
                },
				'ReportRenderer' => function ($sm) {
                    $serviceLocator = $sm->getServiceLocator();

                    $controllerPlugin = new Controller\Plugin\ReportRenderer($serviceLocator);
                    $controllerPlugin->setMetricsService($metricsService);
                    $controllerPlugin->setServiceManager($serviceLocator);
                    return $controllerPlugin;
                },

            ),
        );
    }

	public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'isAdmin' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\IsAdmin($locator);
                    return $viewHelper;
                },
                'zfcUserDisplayName' => function ($sm) {
                    $locator = $sm->getServiceLocator();
                    $viewHelper = new View\Helper\ZfcUserDisplayName;
                    $viewHelper->setAuthService($locator->get('zfcuser_auth_service'));
                    return $viewHelper;
                },
				'flashMessages' => function($sm) {
                    $flashmessenger = $sm->getServiceLocator()
                        ->get('ControllerPluginManager')
                        ->get('flashmessenger');
                    $messages = new View\Helper\FlashMessages();
                    $messages->setFlashMessenger($flashmessenger);


                    return $messages;
                }

            ),
        );
	}

}
