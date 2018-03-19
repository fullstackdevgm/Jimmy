<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use ApplicationTest\Bootstrap;
use Application\Controller\DashboardController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class DashboardControllerTest extends AbstractHttpControllerTestCase
{

    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {
        $this->setApplicationConfig(
            include './config/application.config.php'
        );

        parent::setUp();

    }

    public function mockAuthenticate(){
        $user_mapper  = $this->getApplicationServiceLocator()->get('jimmybase_user_mapper');
        $current_user = $user_mapper->findById(59);

        $mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
                 ->method('hasIdentity')
                 ->will($this->returnValue(true));

        $authMock->expects($this->any())
                 ->method('getIdentity')
                 ->will($this->returnValue($current_user));

        $this->getApplicationServiceLocator()->get('Zend\Mvc\Controller\PluginManager')
             ->setService('zfcUserAuthentication', $authMock);
    }

 	public function testIndexActionCanBeAccessed()
    {
        $this->mockAuthenticate();
        $this->dispatch('/dashboard');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerClass('dashboardcontroller');
        $this->assertControllerName('dashboard');
        $this->assertMatchedRouteName('dashboard');

    }

    public function testIndexActionRedirects()
    {
        $this->dispatch('/dashboard');
        $this->assertResponseStatusCode(302);

    }

}
