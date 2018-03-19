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
use Application\Controller\SettingsController;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as HttpRouter;
use PHPUnit_Framework_TestCase;
use Zend\ServiceManager\ServiceManager;

class SettingsControllerTest extends PHPUnit_Framework_TestCase
{

    protected $controller;
    protected $request;
    protected $response;
    protected $routeMatch;
    protected $event;

    protected function setUp()
    {
        $serviceManager   = Bootstrap::getServiceManager();
        $this->controller = new SettingsController();
        $this->request    = new Request();
        $this->routeMatch = new RouteMatch(array('controller' => 'FrontSettings'));
        $this->event      = new MvcEvent();
        $config = $serviceManager->get('Config');
        $routerConfig = isset($config['router']) ? $config['router'] : array();
        $router       = HttpRouter::factory($routerConfig);
        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);

        $this->controller->setEvent($this->event);
        $this->controller->setServiceLocator($serviceManager);


    }
    public function mockAuthenticate(){
        $user_mapper  = $this->controller->getServiceLocator()->get('jimmybase_user_mapper');
        $current_user = $user_mapper->findById(59);

        $mockAuth = $this->getMock('ZfcUser\Entity\UserInterface');

        $authMock = $this->getMock('ZfcUser\Controller\Plugin\ZfcUserAuthentication');

        $authMock->expects($this->any())
                 ->method('hasIdentity')
                 ->will($this->returnValue(true));

        $authMock->expects($this->any())
                 ->method('getIdentity')
                 ->will($this->returnValue($current_user));

        $this->controller->getPluginManager()
             ->setService('zfcUserAuthentication', $authMock);
    }

 	public function testSaveActionCanBeAccessed()
    {
        $this->mockAuthenticate();

        $this->routeMatch->setParam('action', 'save');
        $this->request->getPost()->set('replace_app_logo', 0);
        $this->request->getPost()->set('from_email', 'test@webmarketers.com.au');
        $this->request->getPost()->set('from_name', 'Sagar Bhandari');
        $this->request->getPost()->set('share_report_email_body', "<p>Hi There,</p>

<p><strong>[agency-name]</strong>&nbsp;has shared&nbsp;a report with you.</p>

<p>Report: <strong>[report-title]</strong></p>

<p>[newuser]<strong>Your username and password</strong>[/newuser]</p>

<p>To access your new reporting dashboard you can login to [url]</p>

<p>The report you will be viewing will be real time data. Remember you can also change the date range if you want to view your data in another timeframe.</p>

<p>Love using Jimmy to view your reports? Then why go backwards? Get your Agency to use Jimmy and access all your real time data in the one place!</p>");
        $this->request->getPost()->set('pdf_report_footer', '');

        $result   = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }


}
