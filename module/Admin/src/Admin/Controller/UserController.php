<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\ModelInterface;

class UserController extends AbstractActionController
{
	 /**
     * @todo Make this dynamic / translation-friendly
     * @var string
     */
    protected $failedLoginMessage = 'Authentication failed. Please try again.';
	
    public function indexAction(){
	    
		$viewModel = new ViewModel();

		// AdminUserAuthentication is only required while login , rest of the time UserAuthentication Plugin from ZfcUser should be used
		if (!$this->adminUserAuthentication()->hasAdminIdentity()) {
			 $userLogin = $this->forward()->dispatch('user', array('action' => 'login'));
        }
		
		        
		if (!$userLogin instanceof ModelInterface) {
            return $userLogin;
        }
		
        $viewModel->addChild($userLogin, 'userLogin');

        return $viewModel;
	} 
	
	/**
     * Login form
     */
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }
		

        if (!$request->isPost()) {
            return array(
                'loginForm'          => $form,
                'redirect'           => $redirect,
                'enableRegistration' => $this->getOptions()->getEnableRegistration(),
            );
        }

        $form->setData($request->getPost());

        if (!$form->isValid()) {
            $this->flashMessenger()->setNamespace('admin-login-form')->addMessage($this->failedLoginMessage);
            return $this->redirect()->toUrl($this->url()->fromRoute('admin').($redirect ? '?redirect='.$redirect : ''));
        }
        // clear adapters

        return $this->forward()->dispatch('AdminUser', array('action' => 'authenticate'));
    }

    /**
     * Logout and clear the identity
     */
    public function logoutAction()
    {
        $this->adminUserAuthentication()->getAuthAdapter()->resetAdapters();
        $this->adminUserAuthentication()->getAuthService()->clearIdentity();

        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }

        return $this->redirect()->toRoute('admin');
    }

    /**
     * General-purpose authentication action
     */
    public function authenticateAction()
    {
       if ($this->adminUserAuthentication()->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute($this->getOptions()->getLoginRedirectRoute());
        }
        $adapter  = $this->adminUserAuthentication()->getAuthAdapter();
        $redirect = $this->params()->fromPost('redirect', $this->params()->fromQuery('redirect', false));

        $result = $adapter->prepareForAuthentication($this->getRequest());
		$result = null;
        // Return early if an adapter returned a response
        if ($result instanceof Response) {
            return $result;
        }
		
        $auth = $this->adminUserAuthentication()->getAuthService()->authenticate($adapter);
			
        if (!$auth->isValid()) {
            $this->flashMessenger()->setNamespace('admin-login-form')->addMessage($this->failedLoginMessage);
            $adapter->resetAdapters();
            return $this->redirect()->toUrl($this->url()->fromRoute('admin').($redirect ? '?redirect='.$redirect : ''));
        }

        if ($this->getOptions()->getUseRedirectParameterIfPresent() && $redirect) {
            return $this->redirect()->toUrl($redirect);
        }
		
        return $this->redirect()->toRoute('admin');
    }
 	

	
	public function getLoginForm()
    {
        if (!$this->loginForm) {
            $this->setLoginForm($this->getServiceLocator()->get('zfcuser_login_form'));
        }
        return $this->loginForm;
    }
  
   public function setLoginForm(\ZfcUser\Form\Login $loginForm)
    {
        $this->loginForm = $loginForm;
        $fm = $this->flashMessenger()->setNamespace('admin-login-form')->getMessages();
        if (isset($fm[0])) {
            $this->loginForm->setMessages(
                array('identity' => array($fm[0]))
            );
        }
        return $this;
    }
 	
	/**
     * set options
     *
     * @param UserControllerOptionsInterface $options
     * @return UserController
     */
    public function setOptions(\ZfcUser\Options\UserControllerOptionsInterface $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * get options
     *
     * @return UserControllerOptionsInterface
     */
    public function getOptions()
    {
        if (!$this->options instanceof UserControllerOptionsInterface) {
            $this->setOptions($this->getServiceLocator()->get('zfcuser_module_options'));
        }
        return $this->options;
    }

	
}
