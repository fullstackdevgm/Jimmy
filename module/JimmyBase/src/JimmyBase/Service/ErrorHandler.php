<?php
namespace JimmyBase\Service;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class ErrorHandler  implements ServiceManagerAwareInterface
{
    protected $logger;

    function __construct($logger)
    {
        $this->logger = $logger;

        $this->mailExceptions();

    }

    public function  mailExceptions(){
        $subject = "Welcome to JimmyData";

        // make a header as html
        $html = new MimePart($content);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array($html,));

        // instance mail
        $mail = new Message();
        $mail->setBody("error"); // will generate our code html from template.phtml
        $mail->setFrom('no-reply@jimmydata.com','JimmyData');
        $mail->setTo("naveen@webmarketers.com.au");
        $mail->setSubject("Exception Occurrred");

        $mail_writer = new \Zend\Log\Writer\Mail($mail);
        $this->logger->addWriter($mail_writer);

    }

    public function logException(\Exception $e)
    {
        $auth = $this->getServiceManager()->get('ControllerPluginManager')->get('zfcUserAuthentication');

        $current_user = $auth->getIdentity();

        $user = null;

        if($current_user){
          ob_start();
            //echo "<pre>";
            print_r($current_user);
            //echo "</pre>";
          $user = ob_get_clean();
        }

        $trace = $e->getTraceAsString();
        $i = 1;
        do {
            $messages[] = $i++ . ": " . $e->getMessage();
        } while ($e = $e->getPrevious());

        $log  = "Exception:" . implode("\n", $messages);
        $log .= "\nTrace:\n" . $trace;
        $log .= "\n\nUser Information\n";
        $log .= $user."\n";
        $log .= "\n=============================================================================================\n";
        $this->logger->err($log);
    }


    public function log($msg)
    {

        $log  = print_r($msg,true);
        $log .= "\n=============================================================================================\n";
        $this->logger->err($log);
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