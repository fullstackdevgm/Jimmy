<?php
/**
 * Application Module
 *
 * @category   Application
 * @package    Application_Controller
 */

namespace Application\Controller\Factory;

use Application\Controller\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class IndexControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {	
		$config 	   = $controllerManager->getServiceLocator()->get('Config');
		$google_api_config = $config['google-api-config'];
		//print_r($google_api_config);
        $controller = new IndexController();
        $controller->setOptions($google_api_config);

        return $controller;
    }
}
