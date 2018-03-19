<?php
namespace JimmyBase\Service;

use Zend\EventManager\EventInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\View\Renderer\PhpRenderer;
use Zend\Session\Container as SessionContainer;

use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Notification implements ListenerAggregateInterface,ServiceManagerAwareInterface
{
    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var listeners
     */

    protected $listeners = array();



    public function attach(EventManagerInterface $events)
    {

       // $this->listeners[] = $events->attach('authenticate', array($this, 'notifyUser'), 1);
    }

	public function attachShared( $events)
    {

        $this->listeners[] = $events->attach('Application\Controller\UserController','passwordResetLink', array($this, 'sendVerificationLink'), 1);
        $this->listeners[] = $events->attach('JimmyBase\Service\User','resetPassword', array($this, 'sendPasswordResetEmail'), 1);
        $this->listeners[] = $events->attach('JimmyBase\Service\Payment','userInvoice.success', array($this, 'sendInvoiceEmail'), 1);
        $this->listeners[] = $events->attach('JimmyBase\Service\Payment','userInvoice.failure', array($this, 'sendInvoiceFailureEmail'), 1);
		$this->listeners[] = $events->attach('Application\Controller\IndexController','userSignup.failure', array($this, 'sendFailureEmail'), 1);
		$this->listeners[] = $events->attach('Application\Controller\IndexController','userSignup.success', array($this, 'sendWelcomeEmail'), 1);
		$this->listeners[] = $events->attach('Application\Controller\IndexController','userUpgrade.failure', array($this, 'sendUpgradeFailureEmail'), 1);
		$this->listeners[] = $events->attach('Application\Controller\IndexController','userUpgrade.success', array($this, 'sendUpgradeWelcomeEmail'), 1);
		$this->listeners[] = $events->attach('Application\Controller\IndexController','accountCancel.success', array($this, 'sendAccountCancelEmail'), 1);
		$this->listeners[] = $events->attach('Application\Authentication\Adapter\HybridAuth','registerViaProviderTrial.post', array($this, 'sendTrialWelcomeEmail'), 1);
		$this->listeners[] = $events->attach('Application\Authentication\Adapter\HybridAuth','registerViaProviderReferrer.post', array($this, 'sendReferralWelcomeEmail'), 1);
		//$this->listeners[] = $events->attach('JimmyBase\Controller\ReportShareApiController','createUser.success', array($this, 'sendNewUserFromShareEmail'), 1);
		$this->listeners[] = $events->attach('JimmyBase\Controller\ReportShareApiController','reportShare.success', array($this, 'sendReportShareEmail'), 1);
        $this->listeners[] = $events->attach('Application\Controller\CoworkerApiController','registerCoworker', array($this, 'sendNewCoworkerEmail'), 1);

        //$this->listeners[] = $events->attach('Application\Authentication\Adapter\HybridAuth','registerViaProvider.post', array($this, 'notifyAgency'), 1);
        //$this->listeners[] = $events->attach('JimmyBase\Controller\ClientController','registerClient.post', array($this, 'notifyClient'), 1);
        $this->listeners[] = $events->attach('ScnSocialAuth\HybridAuth\Provider\Live','beforeLiveLogin', array($this, 'beforeOauthLogin'), 1);
    }

    // Work around for the bing login as bing login is not currently supporting multiple redirect_uri
    public function beforeOauthLogin($e){
    	$api    = $e->getParams()['api'];
    	$params = $e->getParams()['params'];

    	$config =  $this->getServiceManager()->get('Config');
	    $jimmy_settings  = $config['bing-api-config'];

        $params["redirect_uri"]  = $jimmy_settings['redirect_uri'];

        $api->redirect_uri       = $params["redirect_uri"] ;

        \Hybrid_Auth::storage()->set( "hauth_session.live.hauth_endpoint" , $params["redirect_uri"] );

        $provider_params = \Hybrid_Auth::storage()->get( "hauth_session.live.id_provider_params");
        $provider_params['login_done'] = $params["redirect_uri"];

        $session = new SessionContainer('Client_Auth');
        $session->offsetSet('channel','bingads');
        $session->offsetSet('binglogin',true);


        \Hybrid_Auth::storage()->set( "hauth_session.live.id_provider_params" , $provider_params );
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

	public function notifyAgency($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$content = $this->renderer->render('jimmy-base/emails/new-user-agency.phtml', array('agency' => $params['user']));

		// make a header as html
		$html = new MimePart($content);
		$html->type = "text/html";
		$body = new MimeMessage();
		$body->setParts(array($html,));

		// instance mail
		$mail = new Message();
		$mail->setBody($body); // will generate our code html from template.phtml
		$mail->setFrom('no-reply@jimmydata.com','JimmyData');
		$mail->setTo($params['user']->getEmail());
		$mail->setSubject('You have signed up');

		$transport = new Mail\Transport\Sendmail();

		try{
			$transport->send($mail);
		} catch(\Exception $e) {
		  throw new \Exception($e->getMessage());
		}
	}


	public function notifyClient($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$content = $this->renderer->render('jimmy-base/emails/new-user-client.phtml', array('client' => $params['client'],'agency'=>$params['agency'],'password_raw' => $params['password_raw']));

		// make a header as html
		$html = new MimePart($content);
		$html->type = "text/html";
		$body = new MimeMessage();
		$body->setParts(array($html,));

		// instance mail
		$mail = new Message();
		$mail->setBody($body); // will generate our code html from template.phtml
		$mail->setFrom('no-reply@jimmydata.com','JimmyData');
		$mail->setTo($params['client']->getEmail());
		$mail->setSubject('You have been added as Client');

		$transport = new Mail\Transport\Sendmail();
 		///file_put_contents('a.txt',print_r($params['user'],true));

		try{
			$transport->send($mail);
		} catch(\Exception $e) {
		  throw new \Exception($e->getMessage());
		}
	}


	public function sendNewCoworkerEmail($e){
		$params = $e->getParams();
		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

	    $content = $this->renderer->render('jimmy-base/emails/new-coworker.phtml', array('coworker' => $params['coworker'],'agency' => $params['agency']));

		// make a header as html
		$html = new MimePart($content);
		$html->type = "text/html";
		$body = new MimeMessage();
		$body->setParts(array($html,));

		// instance mail
		$mail = new Message();
		$mail->setBody($body); // will generate our code html from template.phtml
		$mail->setFrom('no-reply@jimmydata.com','JimmyData');
		$mail->setTo($params['coworker']->getEmail());
		$mail->setSubject('You have been added as Co-worker');

		$transport = new Mail\Transport\Sendmail();
 		//file_put_contents('a.txt',print_r($params['user'],true));

		$transport->send($mail);
	}

	public function sendVerificationLink($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

	    $config =  $this->getServiceManager()->get('Config');
	    $jimmy_settings  = $config['jimmy-config'];

	    $default_user_config = $config['default-user-config'];

		$from_name  = $default_user_config['from_name'];
		$from_email = $default_user_config['from_email'];




	    $content = $this->renderer->render('jimmy-base/emails/forgot-password-verification.phtml', array('baseurl'=>str_replace("login", "",$jimmy_settings['clienturl']),'resetpass_ver_code' => $params['resetpass_ver_code'], 'user' => $params['user']));

		// make a header as html
		$html = new MimePart($content);
		$html->type = "text/html";
		$body = new MimeMessage();
		$body->setParts(array($html,));

		// instance mail
		$mail = new Message();
		$mail->setBody($body); // will generate our code html from template.phtml
		$mail->setFrom($from_email,$from_name);
		$mail->setTo($params['user']->getEmail());
		$mail->setSubject('Password Reset Email');

		$transport = new Mail\Transport\Sendmail();
 		//file_put_contents('a.txt',print_r($params['user'],true));

		$transport->send($mail);
	}

	public function sendPasswordResetEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		$config =  $this->getServiceManager()->get('Config');
	    $jimmy_settings  = $config['jimmy-config'];

	    $default_user_config = $config['default-user-config'];

		$from_name  = $default_user_config['from_name'];
		$from_email = $default_user_config['from_email'];

	    $content = $this->renderer->render('jimmy-base/emails/password-reset.phtml', array('password' => $params['password'], 'user' => $params['user']));

		// make a header as html
		$html = new MimePart($content);
		$html->type = "text/html";
		$body = new MimeMessage();
		$body->setParts(array($html,));

		// instance mail
		$mail = new Message();
		$mail->setBody($body); // will generate our code html from template.phtml
		$mail->setFrom($from_email,$from_name);
		$mail->setTo($params['user']->getEmail());
		$mail->setSubject('Password has been reset');

		$transport = new Mail\Transport\Sendmail();
 		//file_put_contents('a.txt',print_r($params['user'],true));

		$transport->send($mail);
	}


	public function sendWelcomeEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{

			$content = $this->renderer->render('jimmy-base/emails/welcome.phtml', array( 'newUser'     => $params['newUser'],
																						 'rawUserData' => $params['rawUserData']));
			$subject = "Welcome to JimmyData";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['newUser']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}


	public function sendNewUserFromShareEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{

			$content = $this->renderer->render('jimmy-base/emails/new-user-from-share.phtml', array( 'user'    	   => $params['user'],
																						 		     'rawUserData' => $params['rawUserData'],
																						 		     'agency'      => $params['agency']));

			$subject =  "Welcome to Jimmy";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['user']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}


	public function sendReportShareEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_service    = $this->getServiceManager()->get('jimmybase_user_service');

		$default_user_config = $this->getServiceManager()->get('Config')['default-user-config'];
		$jimmy_config		 = $this->getServiceManager()->get('Config')['jimmy-config'];

		$_settings = unserialize($user_service->getUserMapper()->getMeta($params['agency']->getId(),'_settings'));

		$from_name  = $default_user_config['from_name'];
		$from_email = $default_user_config['from_email'];
		$email_body = $default_user_config['share_report_email_body'];

		if($_settings['from_name'])
			$from_name = $_settings['from_name'];

		if($_settings['from_email'])
			$from_email = $_settings['from_email'];

		if($_settings["share_report_email_body"])
			$email_body = $_settings["share_report_email_body"];



		try{

			if(!$email_body){

				$content = $this->renderer->render('jimmy-base/emails/report-share.phtml', array( 'report'        => $params['report'],
																							 	  'user'  		  => $params['user'],
																							 	  'agency'        => $params['agency']));
			}


			$url 	 = $params['user']->getType()=='agency'?$jimmy_config['baseurl']:$jimmy_config['clienturl'];


			$content = str_replace( array("[agency-name]","[report-title]","[url]"),
									array($params['agency']->getName(),$params['report']->getTitle(),"<a href='".$url."'>".$url."</a>"),$email_body);

		 	preg_match_all("/\[newuser\](.*?)\[\/newuser\]/s", $content,$matches) ;

			if($params['new_user'] && $params['user']->getId()){
				$newUserText = $matches[1][0]."<p>Username: ".$params['user']->getEmail()."<br/>Password: ".$params['new_user']['password']."</p>";
			} else {
				$newUserText = "";
			}

			$content =  preg_replace("/\[newuser\](.*?)\[\/newuser\]/s",$newUserText, $content);



			$subject = $params['agency']->getName()?$params['agency']->getName():$params['agency']->getEmail() ;
			$subject.= " shared a report with you";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom($from_email,$from_name);
			$mail->setTo($params['user']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}


	public function sendTrialWelcomeEmail($e){
		$params = $e->getParams();


		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{

			$content = $this->renderer->render('jimmy-base/emails/welcome-trial.phtml', array( 'user'     	  => $params['user'],
																							   'userProfile'  => $params['userProfile']));
			$subject = "Jimmy Free Trial";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['user']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}

	public function sendReferralWelcomeEmail($e){
		$params = $e->getParams();


		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{

			$content = $this->renderer->render('jimmy-base/emails/welcome-referral.phtml', array( 'user'     	 => $params['user'],
																							   	  'userProfile'  => $params['userProfile']));
			$subject = "Jimmy Referral Trial";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['user']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}



	public function sendUpgradeWelcomeEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{

			$content = $this->renderer->render('jimmy-base/emails/upgrade-welcome.phtml', array( 'user'        => $params['user'],
																						         'rawUserData' => $params['rawUserData']));
			$subject = "Welcome to JimmyData";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['user']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}

	public function sendFailureEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{


			$content = $this->renderer->render('jimmy-base/emails/signup-failure.phtml', array( 'rawUserData' => $params['rawUserData']));
			$subject = "Problem in signing up with JimmyData?";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));
			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['customerInfo']['email']);
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;

		} catch(\Exception $e) {
		  return false;
		}
	}

	public function sendAccountCancelEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{


			$content = $this->renderer->render('jimmy-base/emails/account-cancel.phtml', array( 'user' => $params['user']));
			$subject = "Your account has been cancelled";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));
			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['user']->getEmail());
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;

		} catch(\Exception $e) {
		  return false;
		}
	}

	public function sendUpgradeFailureEmail($e){
		$params = $e->getParams();

		$this->renderer = $this->getServiceManager()->get('ViewRenderer');
		$user_mapper    = $this->getServiceManager()->get('jimmybase_user_service');

		try{


			$content = $this->renderer->render('jimmy-base/emails/upgrade-failure.phtml', array( 'rawUserData' => $params['rawUserData']));
			$subject = "Problem in signing up with JimmyData?";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));
			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');
			$mail->setTo($params['customerInfo']['email']);
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;

		} catch(\Exception $e) {
		  return false;
		}
	}

	public function sendInvoiceEmail($e){
		$params = $e->getParams();

		$renderer = new PhpRenderer();

		$basePath = realpath(__DIR__.'/../../../view/jimmy-base/emails/');

	    $renderer->resolver()->addPath($basePath);

		try{
			$content = $renderer->render('invoice.phtml', array('user'     	   		=> $params['user'],
															 	'rawUserData'     	=> $params['rawUserData'],
															 	'paymentResponse' 	=> $params['paymentResponse'],
																'currency'		    => $params['currency']));
			$subject = "Your Invoice to JimmyData";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');

			if($params['rawUserData']['email'])
			   $email = $params['rawUserData']['email'];
			else
			   $email = $params['user']->getEmail();


			$mail->setTo($email);
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem while sending the email");

			return true;

		} catch(\Exception $e) {
		   return  false;
		}

	}


	public function sendInvoiceFailureEmail($e){
		$params = $e->getParams();
  		$renderer = new PhpRenderer();

		$basePath = realpath(__DIR__.'/../../../view/jimmy-base/emails/');

	    $renderer->resolver()->addPath($basePath);

		try{

			$content = $renderer->render('invoice-failure', array(   'user'     	   => $params['user'],
															 		 'rawUserData'     => $params['rawUserData'],
															 		 'paymentResponse' => $params['paymentResponse'],
															 		 'currency'		   => $params['currency']));


			$subject = "Your Payment to JimmyData has failed";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom('no-reply@jimmydata.com','JimmyData');

			if($params['rawUserData']['email'])
			   $email = $params['rawUserData']['email'];
			else
			   $email = $params['user']->getEmail();


			$mail->setTo($email);
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();



			if(!$transport->send($mail))
			   throw new \Exception("A problem while sending the email");

			return true;

		} catch(\Exception $e) {
			// Log Exception Here

			//return false;
			//throw new \Exception($e->getMessage());
		}

	}

	//Statically Invoked from controller and not attached to any event
	public static function sendSupportEmail($support,$user,$to){


		self::$renderer = self::getServiceManager()->get('ViewRenderer');
		$user_mapper    = self::$serviceManager->get('jimmybase_user_service');

		try{

			$content = $this->renderer->render('jimmy-base/emails/support.phtml', array( 'support'     => $support));
			$subject = "Support Request";

			// make a header as html
			$html = new MimePart($content);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->setParts(array($html,));

			// instance mail
			$mail = new Message();
			$mail->setBody($body); // will generate our code html from template.phtml
			$mail->setFrom($user->getEmail(),$user->getName());
			$mail->setTo($to);
			$mail->setSubject($subject);

			$transport = new Mail\Transport\Sendmail();

			if(!$transport->send($mail))
			   throw new \Exception("A problem occurred while sending the email");

			return true;
		} catch(\Exception $e) {
		  	return false;
		}
	}

    public function onVisiteChangeStatut(EventInterface $event)
    {
        $logger = new \Zend\Log\Logger();
        $logger->addWriter(new \Zend\Log\Writer\Stream(__DIR__.'/log.txt'));

        $logger->log(\Zend\Log\Logger::INFO, 'A user with the ID: ' . json_encode($event->getParams()) . ' has registered');
    }
 	/**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
        return $this;
    }
}