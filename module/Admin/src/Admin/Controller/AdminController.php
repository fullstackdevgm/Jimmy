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
use Zend\Session\Container as SessionContainer;


use Google\AdWords\Service\AdWords;


class AdminController extends AbstractActionController
{
/*	private $clientId     = '877944901534-qkd8vith0i1ti5g4kdpj0q64b5lios3b.apps.googleusercontent.com';
	private	$clientSecret = 'LyrGtOcYbOuZ6XLC1SP5ElTs';
	private	$redirectUri  = 'http://localhost/admin/oauthcallback';
*/	private $session;

    public function indexAction(){
		
		  $viewModel = new ViewModel();
		  
		if (!$this->zfcUserAuthentication()->hasIdentity()  or $this->zfcUserAuthentication()->getIdentity()->getType()!='admin') {
			 $userLogin = $this->forward()->dispatch('AdminUser', array('action' => 'login'));
			 $viewModel->addChild($userLogin, 'userLogin');
        }
        
        return $viewModel;
	}
	
	public function oauthloginAction(){
		$api_service = $this->getServiceLocator()->get('jimmybase_campaignapi_service');
	    $authorizationUrl = $api_service->getApiService()->getAdWordsUser()->GetOAuth2AuthorizationUrl($this->redirectUri, TRUE);
		 
		$url_parts =  parse_url($authorizationUrl);
		$queries = explode('&',$url_parts['query']);
		$queries[3]= $queries[3].' '.'email profile';
		$url_parts['query'] = implode('&',$queries);
	    $url =  $url_parts['scheme'].'://'.$url_parts['host'].$url_parts['path'].'?'.$url_parts['query'];

 		header("Location:$url");
		exit;
	}
	
	public function oauthcallbackAction(){
	  $api_service = $this->getServiceLocator()->get('jimmybase_campaignapi_service');

	  $code =  $this->params()->fromQuery('code');
	  
	  // Get the access token using the authorization code. Ensure you use the same
	  // redirect URL used when requesting authorization.
	  $api_service->getApiService()->getAdWordsUser()->GetOAuth2AccessToken($code, $this->redirectUri);
	
	  // The access token expires but the refresh token obtained for offline use
	  // doesn't, and should be stored for later use.
	  $oauth2Info = $api_service->getApiService()->getAdWordsUser()->GetOAuth2Info();
	  
	  if(is_array($oauth2Info)){
	  	$this->session = new SessionContainer('ADMINAPICREDENTIALS');
	    $this->session->oauth2_info = $oauth2Info;
	  }
	  
	  
	  
      return $this->redirect()->toRoute('admin');

	}
	
	
	
}
