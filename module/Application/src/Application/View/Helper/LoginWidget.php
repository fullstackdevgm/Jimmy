<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class LoginWidget extends AbstractHelper
{
    /**
     * Login Form
     * @var LoginForm
     */
    protected $loginForm;

    /**
     * $var string template used for view
     */
    protected $loginWidgetTemplate;
    /**
     * __invoke
     *
     * @access public
     * @param array $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {
	   
        $vm = new ViewModel(array(
            'loginForm' => $this->getLoginForm(),
            'widgets'   => $this->getLoginWidget(),
            'redirect'  => $redirect,
        ));
        $vm->setTemplate($this->getViewTemplate());
      
	   return $this->getView()->render($vm);
    }

    /**
     * Retrieve Login Form Object
     * @return LoginForm
     */
    public function getLoginForm()
    {
        return $this->loginForm;
    }

    /**
     * Inject Login Form Object
     * @param LoginForm $loginForm
     * @return ZfcUserLoginWidget
     */
    public function setLoginForm(LoginForm $loginForm)
    {
        $this->loginForm = $loginForm;
        return $this;
    }

    /**
     * @param string $viewTemplate
     * @return ZfcUserLoginWidget
     */
    public function setLoginWidget($loginWidgetTemplate)
    {
        $this->loginWidgetTemplate = $loginWidgetTemplate;
        return $this;
    }
    
	public function getLoginWidget()
    {
        return $this->loginWidgetTemplate;
    }
	
	
	public function setViewTemplate($viewTemplate)
    {
        $this->viewTemplate = $viewTemplate;
        return $this;
    }
	
	public function getViewTemplate()
    {
        return $this->viewTemplate;
    }


}
