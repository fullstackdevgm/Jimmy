<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class UserLogoWidget extends AbstractHelper
{


    protected $user;


    protected $logoConfig;
    /**
     * __invoke
     *
     * @access public
     * @param array $options array of options
     * @return string
     */
    public function __invoke($settings,$logo)
    {

        $logo_url           = isset($this->getLogoConfig()['logo_url'])?$this->getLogoConfig()['logo_url']:null;
        $default_logo_url   = isset($this->getLogoConfig()['default_logo_url'])?$this->getLogoConfig()['default_logo_url']:null;
        $logo_path          = '/images/';

        if(!defined('BASE_DIR'))
            define('BASE_DIR',null);

        if(!file_exists(BASE_DIR.$logo_path.$logo_url)){
            if(!file_exists(BASE_DIR.$logo_path.$default_logo_url))
              $logo_url = 'logo1.png';
        }


       if($settings['replace_app_logo']) {
         $logo = '/resources/logos/agencies/'.$logo;
         return "style=\"background:url('$logo') no-repeat  -30px ;background-size:  100%\"";
       } else {
         $logo = $logo_path.$logo_url;
         return "style=\"background:url('$logo') no-repeat  -30px; background-size:  100%\"";
       }
    }


	public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

	public function getUser()
    {
        return $this->user;
    }


    public function setLogoConfig($logoConfig){
        $this->logoConfig = $logoConfig;
        return $this;
    }

    public function getLogoConfig(){
        return $this->logoConfig;
    }




}
