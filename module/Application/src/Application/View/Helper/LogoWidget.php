<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class LogoWidget extends AbstractHelper
{

    /**
     * $var string template used for view
     */
    protected $loginWidgetTemplate;


    protected $logoConfig;
    /**
     * __invoke
     *
     * @access public
     * @param array $options array of options
     * @return string
     */
    public function __invoke($options = array())
    {

        $vm = new ViewModel();
        $vm->setTemplate($this->getViewTemplate());


        $logo_url           = isset($this->getLogoConfig()['logo_url'])?$this->getLogoConfig()['logo_url']:null;
        $default_logo_url   = isset($this->getLogoConfig()['default_logo_url'])?$this->getLogoConfig()['default_logo_url']:null;
        $logo_path          = '/images/';

        if(!defined('BASE_DIR'))
            define('BASE_DIR',null);

        if(!file_exists(BASE_DIR.$logo_path.$logo_url)){
            if(!file_exists(BASE_DIR.$logo_path.$default_logo_url))
              $logo_url = 'logo1.png';
        }

        $logo = $logo_path.$logo_url;

        $logo_title = $this->getLogoConfig()['logo_title'];
        $vm->setVariable('logo',$logo);

	   return $this->getView()->render($vm);
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


    public function setLogoConfig($logoConfig){
        $this->logoConfig = $logoConfig;
        return $this;
    }

    public function getLogoConfig(){
        return $this->logoConfig;
    }




}
