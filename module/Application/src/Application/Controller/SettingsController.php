<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;



class SettingsController extends AbstractActionController
{
    public function indexAction(){

        return new JsonModel(array());
    }

	public function saveAction(){
        $request       = $this->getRequest();


        if(!$this->zfcUserAuthentication()->hasIdentity())
            return new JsonModel(array('success'=>false,'message'=>'Unauthorized!'));

        $current_user  = $this->zfcUserAuthentication()->getIdentity();

		$user_service  = $this->getServiceLocator()->get('jimmybase_user_service');
		$user_mapper   = $this->getServiceLocator()->get('jimmybase_user_mapper');


  		$settings		= unserialize($user_mapper->getMeta($current_user->getId(),'_settings'));

  		$settings       = $request->getPost()->toArray();
  		$settings['replace_app_logo'] = $_POST['replace_app_logo']=='true'?1:0;

        $current_user->setKey('_settings');

        $current_user->setValue(serialize($settings));

        if($user_service->saveMeta($current_user))
			return new JsonModel(array('success'=>true,'message'=>'Settings Saved!','settings'=>$settings));
		else
			return new JsonModel(array('success'=>false,'message'=>'Settings could not be Saved!'));

	}




}
