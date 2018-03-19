<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBaseTest\Controller;

use JimmyBaseTest\Bootstrap;
use JimmyBase\Controller\ClientApiController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class ClientApiControllerTest extends PHPUnit_Framework_TestCase
{
	
    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller = new ClientApiController();
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'index'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router = HttpRouter::factory($routerConfig);
        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);
        $mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');

		$ZfcUserMock = $this->getMock('JimmyBase\Entity\User');  

		$ZfcUserMock->expects($this->any())
		            ->method('getId')
		            ->will($this->returnValue('1'));

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');
        $aclMock = $this->getMock('JimmyBase\Controller\Plugin\AclPlugin');

		$authMock->expects($this->any())
		         ->method('hasIdentity')
		            -> will($this->returnValue(true));  

		$authMock->expects($this->any())
		         ->method('getIdentity')
		         ->will($this->returnValue($ZfcUserMock));

        $this->controller->getPluginManager()
             ->setService('zfcUserAuthentication', $authMock);
        
        $this->controller->getPluginManager()
             ->setService('AclPlugin', $aclMock);
    }


    public function testGetListCanBeAccessed()
    {
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
 
        $this->assertEquals(200, $response->getStatusCode());
    }
 	
 	public function testGetCanBeAccessed()
    {

    	$this->routeMatch->setParam('id', '1');
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
 
        $this->assertEquals(200, $response->getStatusCode());
    }
 	/*
    public function testCreateCanBeAccessed()
    {
    	$this->request->setMethod('post');
        $this->request->getPost()->set('name', 'bar');
        $this->request->getPost()->set('account_name','Test');
        $this->request->getPost()->set('channel','googleadwords');
        $this->request->getPost()->set('account',11);


      
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
 
        $this->assertEquals(200, $response->getStatusCode());
    }

*/
    public function testDeleteCanBeAccessed()
    {
        $this->request->setMethod('delete');
        $this->routeMatch->setParam('client_id', '202');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();


 
        $this->assertEquals(200, $response->getStatusCode());
    }
 	
 	 public function testUpdateCanBeAccessed()
    { 	    

        $this->routeMatch->setParam('client_id', '202');
        $this->request->setMethod('put');

 	    $this->request->getPost()->set('action', 'update-name');
        $this->request->getPost()->set('name', 'Sagar');
 
        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
 
        $this->assertEquals(200, $response->getStatusCode());
    }

}
