<?php

namespace JimmyBase\Service;

use Zend\Authentication\AuthenticationService;
use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Crypt\Password\Bcrypt;
use Zend\Mail;
use Zend\Mail\Message;
use Zend\Mime;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\View\Model\ViewModel;

use Aws\Ses\SesClient;
use SimpleEmailServiceMessage;
use SimpleEmailService;

use ZfcBase\EventManager\EventProvider;
use JimmyBase\Mapper\ReportScheduleInterface as ReportScheduleMapperInterface;

class ReportSchedule extends EventProvider   implements ServiceManagerAwareInterface
{
    /**
     * @var ReportShareMapperInterface
     */
    protected $reportScheduleMapper;


    /**
     * @var ServiceManager
     */
    protected $serviceManager;


    public function save(array $data)
    {



		 //try{
			$reportschedule  = new \JimmyBase\Entity\ReportSchedule();
            $reportschedule->setReportId($data['report_id']);
            $reportschedule->setFrequency($data['frequency']);

            $start_date = $data['start_date'];

            if($data['time'])
               $start_date = $data['start_date'] . ' ' .$data['time'];

           $schedule_report   = $this->getMapper()->findById($data['id']);



            if($data['id']){
                $schedule_report   = $this->getMapper()->findById($data['id']);

                if(strtotime($schedule_report->getStartDate()) == strtotime($start_date) and $schedule_report->getFrequency()!=$data['frequency']){
                   $start_date = date("Y-m-d"). ' ' .$data['time'];
                   $data['next_schedule_date'] = null;
                } else if(strtotime($schedule_report->getStartDate()) != strtotime($start_date) or $schedule_report->getFrequency()!=$data['frequency']){
                   $data['next_schedule_date'] = null;
                }
            }

            $reportschedule->setStartDate($start_date);
            $reportschedule->setSubject($data['subject']);
            $reportschedule->setBody($data['body']);


            if($data['next_schedule_date'])
                 $reportschedule->setNextScheduleDate($data['next_schedule_date']);
            else
                 $reportschedule->setNextScheduleDate($start_date);

            $reportschedule->setTimezone($data['timezone']);
            $reportschedule->setEmail($data['email']);
            $reportschedule->setCcMe($data['ccme']?1:0);
            $reportschedule->setFromName($data['from_name']);
            $reportschedule->setFromEmail($data['from_email']);

            $reportschedule->setUpdated(date('Y-m-d h:i:s'));

			$this->getEventManager()->trigger(__FUNCTION__, $this, array('reportschedule' => $reportschedule));
			if(!$data['id']){
                $reportschedule->setCreated(date('Y-m-d h:i:s'));
			    $this->getMapper()->insert($reportschedule);
            } else {
                $reportschedule->setCreated($data['created']);
                $reportschedule->setId($data['id']);
                $this->getMapper()->update($reportschedule);
            }

			$this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('reportschedule' => $reportschedule));

			return $reportschedule;
		 //} catch(Exception $e){
		//	 print_r($e);

		// }
    }


    public function send($reportschedule,$report,$report_path){


        if(!$reportschedule) return false;

        $user_mapper     = $this->getServiceManager()->get('jimmybase_agency_mapper');

        $agency          = $user_mapper->findByClientId($report->getUserId());

        try{

                $viewModel  = new ViewModel();

                $viewModel->setVariable('report' , $report)
                          ->setVariable('agency',$agency)
                          ->setTemplate('jimmy-base/emails/report-schedule.phtml');

                $htmlOutput = $this->getServiceManager()->get('viewrenderer')
                                                        ->render($viewModel);

                $subject = "A report has been sent to you";

                if($reportschedule['subject'])
                   $subject = $reportschedule['subject'];

                if($reportschedule['body'])
                   $htmlOutput = $reportschedule['body'];

                // instance mail
                // $mail = new Message();

                   $from_email = "no-reply@jimmydata.com";
                   $reply_to_email = $agency->getEmail();
                   // $from_name  = $agency->getName();

                if($reportschedule['from_name'])
                    $from_name = $reportschedule['from_name'];

                if($reportschedule['from_email']) {
                    $reply_to_email = $reportschedule['from_email'];
                    $from_email = $reportschedule['from_email'];
                }

                // $mail->setFrom("no-reply@jimmydata.com",$from_name); 
                $recipents = preg_split( "/(,|;)/", $reportschedule['email'] );

                // $attachment = $report_path;
                // $attachment_size = filesize($attachment);
                // $handle = fopen($attachment, 'r');

                // $attachment_content = fread($handle, $attachment_size);

                //Split the attachment content with base64 encoding.
                // $attachment_content = chunk_split(base64_encode($attachment_content));

               // $recipents = explode(',',$reportschedule['email']);
              
                // foreach ($recipents as $r) {
                  
                //     $mail->addTo(trim($r));
                // }

                // $recipents_array = $recipents;
                // $recipents = implode(',', $recipents_array);
                

		//$mail->addReplyTo($from_email,$from_name);
                $email_cc_array = array();
                if($reportschedule['ccme']) {
                  if ($reply_to_email != $agency->getEmail()) {
                      array_push($email_cc_array, $reply_to_email);
                      array_push($email_cc_array, $agency->getEmail());
                       // $mail->setCc($reply_to_email);
                       // $mail->addCc($agency->getEmail());
                  } else {
                      array_push($email_cc_array, $agency->getEmail());
                       // $mail->setCc($agency->getEmail());
                  }
                }

                //$mail->setBcc(array('sagar@webmarketers.com.au'));
                // $mail->setSubject($subject);

                //dan-zahariev mailer
                $m = new SimpleEmailServiceMessage();
                $m->addTo($recipents);
                $m->setFrom($from_name.' '.$from_email);
                $m->addReplyTo($reply_to_email);
                $m->addCC($email_cc_array);
                $m->setSubject($subject);
                $m->setMessageFromString('',$htmlOutput);
                $m->addAttachmentFromFile($report->getTitle().'.pdf', $report_path, 'application/pdf');
                $ses = new SimpleEmailService('AKIAI63ZMYYH55XKITBQ', 'XJc9RguaAOQIiD0NqJwIS8xyrQyb5cugJwITmWSC', SimpleEmailService::AWS_EU_WEST1);

                $result = $ses->sendEmail($m, $use_raw_request=true);

                // var_dump($result); die;


              // make a header as html
                // $html = new Mime\Part($htmlOutput);
                // $html->type = "text/html";


                // $fileContent = fopen($report_path, 'r');

                // $attachment = new Mime\Part($fileContent);
                // $attachment->type = 'application/pdf';
                // $attachment->filename = $report->getTitle().'.pdf';
                // $attachment->disposition = Mime\Mime::DISPOSITION_ATTACHMENT;
                // $attachment->encoding    = Mime\Mime::ENCODING_BASE64;

                // $body = new Mime\Message();

                // $body->setParts(array($html,$attachment));

                // $mail->setBody($body); // will generate our code html from template.phtml

                // $transport = new Mail\Transport\Sendmail();
                // $transport->send($mail);

                return true;
            } catch(\Exception $e) {
                return $e->getMessage();
            }
    }


    /**
     * getUserMapper
     *
     * @return ReportShareMapperInterface
     */
    public function getMapper()
    {
        if (null === $this->reportScheduleMapper) {
            $this->reportScheduleMapper = $this->getServiceManager()->get('jimmybase_reportschedule_mapper');
        }
        return $this->reportScheduleMapper;
    }

    /**
     * setUserMapper
     *
     * @param ReportShareMapperInterface $userMapper
     * @return User
     */
    public function setMapper(ReportScheduleMapperInterface $reportScheduleMapper)
    {
        $this->reportScheduleMapper = $reportScheduleMapper;
        return $this;
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
