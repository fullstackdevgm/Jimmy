<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ChatTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class MessageControllerTest extends AbstractHttpControllerTestCase
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



    public function testGetListCanBeAccessed()
    {
        $this->mockAuthenticate();

        $this->dispatch('/message','GET');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('Chat');
        $this->assertControllerClass('messageapicontroller');
        $this->assertControllerName('messageapi');
        $this->assertMatchedRouteName('message');
    }

    public function testGetCanBeAccessed()
    {
        $this->mockAuthenticate();
        $this->dispatch('/message/1','GET');
        $this->assertResponseStatusCode(200);


    }

    public function testCreateCanBeAccessed()
    {
        $this->mockAuthenticate();

        $postData = array(
            'message'       => 'Hi, Foo',
            'recipient_id'  => 128,
            'widget_id'     => 902,
        );
        $this->dispatch('/message','POST',$postData);
        $this->assertResponseStatusCode(200);

    }

}
