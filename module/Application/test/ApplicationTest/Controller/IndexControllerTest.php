<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    protected $traceError = true;


    protected function setUp()
    {   @session_start();
        $this->setApplicationConfig(
            include './config/application.config.php'
        );

        parent::setUp();
    }

    public function mockAuthenticate(){
        $user_mapper  = $this->getApplicationServiceLocator()->setAllowOverride(true)->get('jimmybase_user_mapper');
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

        $HybridAuth = $this->getMockBuilder('ScnSocialAuth\Service\HybridAuthFactory')
                            ->disableOriginalConstructor()
                            ->getMock();
       //$HybridAuth= $this->getApplicationServiceLocator()->get('HybridAuth');

        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('ScnSocialAuth\Service\HybridAuthFactory', $HybridAuth);


        $result   = $this->dispatch('/');

        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Application');
        $this->assertControllerClass('indexcontroller');
        $this->assertControllerName('index');
        $this->assertMatchedRouteName('home');

    }

    public function testIndexActionRedirectsOnAuthentication()
    {
        $this->mockAuthenticate();
        $this->dispatch('/');
        $this->assertResponseStatusCode(302);

    }

}
