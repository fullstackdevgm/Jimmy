<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ModelInterface;
use Zend\Session\Container as SessionContainer;
use JimmyBase\Validator as JimmyBaseValidator;
use JimmyBase\Entity\ClientAccounts;

class ReportController extends AbstractActionController
{

    private $session;



    public function downloadAction()
    {
        session_write_close();


        $request             = $this->getRequest();
        $response         = $this->getResponse();

        $user_mapper     = $this->getServiceLocator()->get('jimmybase_user_mapper');


        $widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');
        $report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
        $client_service  = $this->getServiceLocator()->get('jimmybase_client_service');
        $agency_service  = $this->getServiceLocator()->get('jimmybase_agency_service');

        $report_id = $this->params('report_id');

        $widgets = $widget_service->getMapper()->findByReportId($report_id);

        $report  = $report_service->getMapper()->findById($report_id);

        $client  = $client_service->getClientMapper()->findById($report->getUserId());

        $agency  = $agency_service->getMapper()->findById($client->getParent());

        $agency_logo  = $user_mapper->getMeta($client->getParent(), 'logo');

        $settings = unserialize($user_mapper->getMeta($client->getParent(), '_settings'));



        try {
            if ($widgets) {
                foreach ($widgets as $widget) {
                    if ($widget->getType() != 'notes') {
                        $fields.= $widget->getFields();
                        $widget->channel= $this->getServiceLocator()
                                                            ->get('jimmybase_clientaccounts_mapper')
                                                            ->findById($widget->getClientAccountId())
                                                            ->getChannel();

                        $response   = $this->getResponse();
                        $postParams = null;
                        $client_account = $this->getServiceLocator()
                                                               ->get('jimmybase_clientaccounts_mapper')
                                                               ->findById($widget->getClientAccountId());

                        switch ($client_account->getChannel()) {
                                                case ClientAccounts::GOOGLE_ADWORDS:

                                                        $output = $this->getServiceLocator()->get('jimmybase_adwords_service')
                                                                                            ->setRequest($request)
                                                                                            ->setReportParamsService($this->AdWordsArguments())
                                                                                            ->loadReport($widget, $client_account, true);
                                                        break;

                                                case ClientAccounts::GOOGLE_ANALYTICS:
                                                        $output = $this->getServiceLocator()->get('jimmybase_analytics_service')
                                                                                            ->setRequest($request)
                                                                                            ->setReportRenderer($rr)
                                                                                            ->setReportParamsService($this->AdWordsArguments())
                                                                                            ->loadReport($widget, $client_account, true);
                                                         
                                                        break;
                                                    
                                                case ClientAccounts::BING_ADS:
                                                        $output = $this->getServiceLocator()->get('jimmybase_bingads_service')
                                                                                            ->setRequest($request)
                                                                                            ->loadReport($widget, $client_account, true);
                                        }
                    } else {
                        $notesHtml = $this->getServiceLocator()->get('jimmybase_reportrenderer_service')
                                                      ->setViewRenderer($this->getServiceLocator()->get('viewrenderer'))
                                                      ->renderNotes($report, $widget, true);

                        $output  = $notesHtml;
                    }

                    $temp_storage[] = array('widget' => $widget,'output' => $output);
                   
                }
            }
        } catch (\ReportDownloadException $e) {
            return new JsonModel(array('success'=>false, 'message' =>$e->getMessage()));
        } catch (\OAuth2Exception $e) {
            return  new JsonModel(array('success'=>false, 'message' =>$e->getMessage()));
        } catch (\Exception $e) {
            return new JsonModel(array('success'=>false, 'message' =>$e->getMessage()));
        }


        $view = new ViewModel();

        $this->layout('layout/download');
        

        $view->setTemplate('jimmy-base/report/download.phtml')
             ->setVariables(array(
                              'report'        => $report,
                              'widgets_output'      => $temp_storage,
                              
                              'client'        => $client,
                              'agency_logo'        => $agency_logo,
                              'agency'        => $agency,
                              'footer'        => $settings['pdf_report_footer']
                            ));

        $html =  $this->getServiceLocator()
                           ->get('viewrenderer')
                           ->render($view);
                           
        /*
        print_r($html);
        die;
        */
        
        $file    = './data/tmp-reports/'.$report->getId().'.html';
        $pdfUrl  = './data/tmp-reports/'.$report->getId().'.pdf';
        unlink($file);
        unlink($pdfUrl);
        try {
            file_put_contents($file, $html);

            $config  = $this->getServiceLocator()->get('Config');
            $baseUrl = $config['jimmy-config']['baseurl'];


            ###PhantomJS PDF
            $htmlUrl     = $baseUrl.'resources/report-download/'.$report->getId().'.html';
            $command     = "./phantomjs ./public/js/rasterize.js $htmlUrl $pdfUrl A4 1.0";
            $descriptors = array(2   => array('pipe','w'), );
            $process     = proc_open($command, $descriptors, $pipes, null, null, array('bypass_shell'=>true));

            if (is_resource($process)) {
                $stderr = stream_get_contents($pipes[2]);

                fclose($pipes[2]);

                $result = proc_close($process);
            } else {
                $this->error = "Could not run command $command";
            }

            if ($this->error) {
                throw new \Exception($this->error, 1);
            }
                //print_r($result);
            ####PhantomJS PDF ENDS ####

            # HTML to PDF Conversion Service Class
            //$pdf = $this->getServiceLocator()->get('WkHtmlToPdf');
            //$pdf->addPage($baseUrl.'resources/report-download/'.$report->getId().'.html');
            //$pdf->saveAs($pdfUrl);

            //unlink($file);
            //unset($pdf);
            return new JsonModel(array('success'=>true, 'message' =>'Report Download Complete', 'file'=>$baseUrl.'reports/download-file/'.$report->getId()));
        } catch (\Exception $e) {
            return new JsonModel(array('success'=>false, 'message' =>$e->getMessage()));
        }

        exit;
    }


    public function downloadReportFileAction()
    {
        $report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');

        $file = $this->params('file_name');
        $report  = $report_service->getMapper()
                          ->findById($file);


        $pdfFile = './data/tmp-reports/'.$file.'.pdf';

        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Type: application/pdf');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($pdfFile));

        if ($pdfFile!==null) {
            header("Content-Disposition: attachment; filename=".$report->getTitle().'.pdf');
        }

        readfile($pdfFile);
        return true;
    }

    public function downloadConsole($report)
    {
        session_write_close();
        $request         = $this->getRequest();
        $response         = $this->getResponse();

        $user_mapper     = $this->getServiceLocator()->get('jimmybase_user_mapper');


        $widget_service  = $this->getServiceLocator()->get('jimmybase_widget_service');
        $report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');
        $client_service  = $this->getServiceLocator()->get('jimmybase_client_service');
        $agency_service  = $this->getServiceLocator()->get('jimmybase_agency_service');


        $widgets = $widget_service->getMapper()->findByReportId($report->getId());


        $client  = $client_service->getClientMapper()->findById($report->getUserId());

        $agency  = $agency_service->getMapper()->findById($client->getParent());

        $agency_logo  = $user_mapper->getMeta($client->getParent(), 'logo');

        $settings = unserialize($user_mapper->getMeta($client->getParent(), '_settings'));

       // $report_service->refreshTokens($client->getId());

        try {
            if ($widgets) {
                foreach ($widgets as $widget) {
                    $fields.= $widget->getFields();

                    $response   = $this->getResponse();
                    $postParams = null;

                    if ($widget->getType()!='notes') {
                        $widget->channel= $this->getServiceLocator()
                                                            ->get('jimmybase_clientaccounts_mapper')
                                                            ->findById($widget->getClientAccountId())
                                                            ->getChannel();

                        $client_account = $this->getServiceLocator()->get('jimmybase_clientaccounts_mapper')
                                                        ->findById($widget->getClientAccountId());

                        switch ($client_account->getChannel()) {
                            case ClientAccounts::GOOGLE_ADWORDS:

                                $output = $this->getServiceLocator()->get('jimmybase_adwords_service')
                                                                    ->setRequest($request)
                                                                    ->setReportParamsService($this->AdWordsArguments())
                                                                    ->loadReport($widget, $client_account, true);
                                break;

                            case ClientAccounts::GOOGLE_ANALYTICS:
                                $output = $this->getServiceLocator()->get('jimmybase_analytics_service')
                                                                    ->setRequest($request)
                                                                    ->setReportRenderer($rr)
                                                                    ->setReportParamsService($this->AdWordsArguments())
                                                                    ->loadReport($widget, $client_account, true);

                                break;
                        }
                    } else {
                        $notesHtml = $this->getServiceLocator()->get('jimmybase_reportrenderer_service')
                                              ->setViewRenderer($this->getServiceLocator()->get('viewrenderer'))
                                              ->renderNotes($report, $widget, true);

                        $output  = $notesHtml;
                    }

                    $temp_storage[] = array('widget' => $widget,'output' => $output);
                }
            }
        } catch (\ReportDownloadException $e) {
            return array('success'=>false,'message' =>$e->getMessage());
        } catch (\OAuth2Exception $e) {
            return  array('success'=>false,'message' =>$e->getMessage());
        } catch (\Exception $e) {
            return array('success'=>false,'message' =>$e->getMessage());
        }


        $view = new ViewModel();

        $this->layout('layout/download');

        $view->setTemplate('jimmy-base/report/download.phtml')
             ->setVariables(array(
                              'report'              => $report,
                              'widgets_output'      => $temp_storage,
                              'client'              => $client,
                              'agency_logo'         => $agency_logo,
                              'agency'              => $agency,
                              'footer'              => $settings['pdf_report_footer']
                            ));

        $html =  $this->getServiceLocator()
                           ->get('viewrenderer')
                           ->render($view);

        $file    = './data/tmp-reports/'.$report->getId().'.html';
        $pdfUrl  = './data/tmp-reports/'.$report->getId().'.pdf';
        unlink($file);
        unlink($pdfUrl);
        try {
            file_put_contents($file, $html);
            $config  = $this->getServiceLocator()->get('Config');
            $baseUrl = $config['jimmy-config']['baseurl'];

            $htmlUrl     = $baseUrl.'resources/report-download/'.$report->getId().'.html';
            $command     = "./phantomjs ./public/js/rasterize.js $htmlUrl $pdfUrl A4";
            $descriptors = array(2   => array('pipe','w'), );
            $process     = proc_open($command, $descriptors, $pipes, null,
                                     null, array('bypass_shell'=>true));

            if (is_resource($process)) {
                $stderr = stream_get_contents($pipes[2]);

                fclose($pipes[2]);

                $result = proc_close($process);
            } else {
                $this->error = "Could not run command $command";
            }

            if ($this->error) {
                throw new \Exception($this->error, 1);
            }


             # HTML to PDF Conversion Service Class
         //    $pdf = $this->getServiceLocator()->get('WkHtmlToPdf');

            // $pdf->addPage($baseUrl.'resources/report-download/'.$report->getId().'.html');

            // $pdf->saveAs($pdfUrl);
            // unlink($file);
            // unset($pdf);
            return true;
        } catch (\Exception $e) {
            return array('success' => false,'message' => $e->getMessage());
        }

        exit;
    }

    public function sendScheduleReportsAction()
    {
        echo '<pre>';
        echo "\n";

        echo gmdate('Y-m-d h:i:s')." Schedule Reports Check Script Start\n";

        $date = gmdate('Y-m-d h:i:s A');


        $reports_to_be_sent    = $this->getServiceLocator()
                                               ->get('jimmybase_reportschedule_mapper')
                                               ->findReportsToBeSentToday($date);

//		 $reports_to_be_sent  	= $this->getServiceLocator()
//                                               ->get('jimmybase_reportschedule_mapper')
//                                               ->findByParentId(91);


         echo "Total Reports:".count($reports_to_be_sent)."\n";

        if (!$reports_to_be_sent) {
            return false;
        }

        $reportschedule_service = $this->getServiceLocator()
                                               ->get('jimmybase_reportschedule_service');
        $report_service         = $this->getServiceLocator()
                                               ->get('jimmybase_reports_service');

        echo "=========Start Sending=========\n";
        foreach ($reports_to_be_sent as $reportschedule) {
            $report          = $report_service->getMapper()
                                                          ->findById($reportschedule['report_id']);

            $ret = $this->downloadConsole($report);

            if (is_array($ret)) {
                echo "Error sending ".$reportschedule['report_id'].' to '.$reportschedule['email']."\n";
                echo "----Details of Error----\n";
                echo $ret['message'];
                echo "------------------------\n";
                continue;
            }

            $pdfUrl  = './data/tmp-reports/'.$reportschedule['report_id'].'.pdf';

            if ($ret = $reportschedule_service->send($reportschedule, $report, $pdfUrl)) {
                switch ($reportschedule['frequency']) {

                    case 'daily':
                        $period = 1;
                        $freq    = 'days';
                        break;

                    case 'weekly':
                        $period = 1;
                        $freq    = 'weeks';
                        break;

                    case 'fortnightly':
                        $period = 2;
                        $freq    = 'weeks';
                        break;

                    case 'monthly':
                        $period = 1;
                        $freq    = 'months';
                        break;
                    default:
                        $period = null;
                }
                if ($period) {
                    $next_schedule_date = date('Y-m-d H:i', strtotime("{$reportschedule['next_schedule_date']}  +$period {$freq}"));
                    $reportschedule['next_schedule_date'] = $next_schedule_date;
                    $reportschedule_service->save($reportschedule);
                }

                echo $reportschedule['report_id'].' sent successfully to '.$reportschedule['email']."\n";
            } else {
                echo "Error sending ".$reportschedule['report_id'].' to '.$reportschedule['email']."\n";
                echo "----Details of Error----\n";
                echo $ret;
                echo "------------------------\n";
            }

            //if(unlink($pdfUrl)){
            //	echo 1;
            //} else echo 0;
        }

        echo "=========End Sending=========\n";

        echo date('Y-m-d h:i:s')." Recurring Check Script End\n";
        echo "================================================\n";


        exit;
    }


        //send scheduled reports by one agency;
        //This can be called from console to send all the reports that are scheduled by the agency
        //regardless to the time and date.
        /*$parent Id = int
         */
        public function sendReportsToParentAction()
        {
            echo '<pre>';
            echo "\n";

            echo gmdate('Y-m-d h:i:s')." Schedule Reports Check Script Start\n";

            $date = gmdate('Y-m-d h:i:s A');
            $request = $this->getRequest();
            $parentId = $request->getParam('parentId', false);

            $reports_to_be_sent    = $this->getServiceLocator()
                                               ->get('jimmybase_reportschedule_mapper')
                                               ->findByParentId($parentId);



            echo "Total Reports:".count($reports_to_be_sent)."\n";

            if (!$reports_to_be_sent) {
                return false;
            }

            $reportschedule_service = $this->getServiceLocator()
                                               ->get('jimmybase_reportschedule_service');
            $report_service         = $this->getServiceLocator()
                                               ->get('jimmybase_reports_service');

            echo "=========Start Sending=========\n";
            foreach ($reports_to_be_sent as $reportschedule) {
                $report          = $report_service->getMapper()
                                                          ->findById($reportschedule['report_id']);

                $ret = $this->downloadConsole($report);

                if (is_array($ret)) {
                    echo "Error sending ".$reportschedule['report_id'].' to '.$reportschedule['email']."\n";
                    echo "----Details of Error----\n";
                    echo $ret['message'];
                    echo "------------------------\n";
                    continue;
                }

                $pdfUrl  = './data/tmp-reports/'.$reportschedule['report_id'].'.pdf';

                if ($ret = $reportschedule_service->send($reportschedule, $report, $pdfUrl)) {
                    switch ($reportschedule['frequency']) {

                    case 'daily':
                        $period = 1;
                        $freq    = 'days';
                        break;

                    case 'weekly':
                        $period = 1;
                        $freq    = 'weeks';
                        break;

                    case 'fortnightly':
                        $period = 2;
                        $freq    = 'weeks';
                        break;

                    case 'monthly':
                        $period = 1;
                        $freq    = 'months';
                        break;
                    default:
                        $period = null;
                }
                    if ($period) {
                        $next_schedule_date = date('Y-m-d H:i', strtotime("{$reportschedule['next_schedule_date']}  +$period {$freq}"));
                        $reportschedule['next_schedule_date'] = $next_schedule_date;
                        $reportschedule_service->save($reportschedule);
                    }

                    echo $reportschedule['report_id'].' sent successfully to '.$reportschedule['email']."\n";
                } else {
                    echo "Error sending ".$reportschedule['report_id'].' to '.$reportschedule['email']."\n";
                    echo "----Details of Error----\n";
                    echo $ret;
                    echo "------------------------\n";
                }

            //if(unlink($pdfUrl)){
            //	echo 1;
            //} else echo 0;
            }

            echo "=========End Sending=========\n";

            echo date('Y-m-d h:i:s')." Recurring Check Script End\n";
            echo "================================================\n";


            exit;
        }




        // console function created to update the table when there
        //  was some issue with the schedule!
        public function changeScheduleAction()
        {
            $reports_to_be_sent    = $this->getServiceLocator()
                                               ->get('jimmybase_reportschedule_mapper')
                                               ->findReportsMissed();
            $reportschedule_service = $this->getServiceLocator()
                                               ->get('jimmybase_reportschedule_service');
            $c=0;
            foreach ($reports_to_be_sent as $reportschedule) {
                switch ($reportschedule['frequency']) {

                    case 'daily':
                        $period = 1;
                        $freq    = 'days';
                        break;

                    case 'weekly':
                        $period = 1;
                        $freq    = 'weeks';
                        break;

                    case 'fortnightly':
                        $period = 2;
                        $freq    = 'weeks';
                        break;

                    case 'monthly':
                        $period = 1;
                        $freq    = 'months';
                        break;
                    default:
                        $period = null;
                }
                $next_schedule_date = $reportschedule['next_schedule_date'];
                while (strtotime($next_schedule_date) < strtotime('-30 minutes')) {
                    $next_schedule_date = date('Y-m-d H:i', strtotime("{$next_schedule_date}  +$period {$freq}"));
                }
                echo $reportschedule['id']. " ";
                echo $next_schedule_date."\n";

                $reportschedule['next_schedule_date'] = $next_schedule_date;
                $reportschedule_service->save($reportschedule);

                $c++;
            }
            echo "total reports rescheduled: ".$c."\n";
        }

    public function visitTourAction()
    {
        $tourName = $this->params()->fromQuery("tourName");
        $userId = $this->params()->fromQuery("userId");
        $tourService  = $this->getServiceLocator()->get('jimmybase_tour_service');
        $visited = $tourService->visitTour($tourName, $userId);
        return new JsonModel($visited);
    }

    public function refreshTokensAction()
    {
        $reportService = $this->getServiceLocator()->get('jimmybase_reports_service');
       // $reportService->refreshTokens(null, true); This function no longer in use
    }

    public function copyTokenAction()
    {
        $reportService = $this->getServiceLocator()->get('jimmybase_reports_service');
        $reportService->copyToken(1786, 'googleanalytics');
    }

    public function imageUploadAction()
    {
        $upload_dir = '/upload';

        // HERE PERMISSIONS FOR IMAGE
        $imgsets = array(
         'maxsize' => 4000,          // maximum file size, in KiloBytes (2 MB)
         'maxwidth' => 2500,          // maximum allowed width, in pixels
         'maxheight' => 2500,         // maximum allowed height, in pixels
         'minwidth' => 10,           // minimum allowed width, in pixels
         'minheight' => 10,          // minimum allowed height, in pixels
         'type' => array('bmp', 'gif', 'jpg', 'jpe', 'png')        // allowed extensions
        );

        $re = '';


        if (isset($_FILES['upload']) && strlen($_FILES['upload']['name']) > 1) {
            $upload_dir = trim($upload_dir, '/') .'/';
            $rand = rand(100000, 200000);
            $img_name = basename($rand."-".$_FILES['upload']['name']);

          // get protocol and host name to send the absolute image path to CKEditor
          $protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $site = $protocol. $_SERVER['SERVER_NAME'] .'/';

            $uploadpath =$_SERVER['DOCUMENT_ROOT'] .'/'.$upload_dir.'/'. $img_name;       // full file path
          $sepext = explode('.', strtolower($_FILES['upload']['name']));
            $type = end($sepext);       // gets extension
          list($width, $height) = getimagesize($_FILES['upload']['tmp_name']);     // gets image width and height
          $err = '';         // to store the errors

          // Checks if the file has allowed type, size, width and height (for images)
          if (!in_array($type, $imgsets['type'])) {
              $err .= 'The file: '. $_FILES['upload']['name']. ' has not the allowed extension type.';
          }
            if ($_FILES['upload']['size'] > $imgsets['maxsize']*1000) {
                $err .= '\\n Maximum file size must be: '. $imgsets['maxsize']. ' KB.';
            }
            if (isset($width) && isset($height)) {
                if ($width > $imgsets['maxwidth'] || $height > $imgsets['maxheight']) {
                    $err .= '\\n Width x Height = '. $width .' x '. $height .' \\n The maximum Width x Height must be: '. $imgsets['maxwidth']. ' x '. $imgsets['maxheight'];
                }
                if ($width < $imgsets['minwidth'] || $height < $imgsets['minheight']) {
                    $err .= '\\n Width x Height = '. $width .' x '. $height .'\\n The minimum Width x Height must be: '. $imgsets['minwidth']. ' x '. $imgsets['minheight'];
                }
            }

          // If no errors, upload the image, else, output the errors
          if ($err == '') {
              if (move_uploaded_file($_FILES['upload']['tmp_name'], $uploadpath)) {
                  $CKEditorFuncNum = $_GET['CKEditorFuncNum'];
                  $url = $site. $upload_dir . $img_name;
                  $message = $img_name .' successfully uploaded: \\n- Size: '. number_format($_FILES['upload']['size']/1024, 3, '.', '') .' KB \\n- Image Width x Height: '. $width. ' x '. $height;
                  $re = "window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$url', '$message')";
              } else {
                  $re = 'alert("Unable to upload the file")';
              }
          } else {
              $re = 'alert("'. $err .'")';
          }
        }
        echo "<script>$re;</script>";

        return false;  
	
        }

        /**
         * Upgrades a free report to a paid one.
         **/
        public function upgradeAction() {

        	$request         = $this->getRequest();
			$response  	     = $this->getResponse();

			$user_mapper     = $this->getServiceLocator()->get('jimmybase_user_mapper');


			$report_service  = $this->getServiceLocator()->get('jimmybase_reports_service');

			$report_id = $this->params('report_id');

			$report  = $report_service->getMapper()->findById($report_id);

			try {
				if(!$this->AclPlugin()->canUpgradeReport($report))
					throw new \Exception('You dont have sufficient privilege to upgrade the report.');

				$user = $this->zfcUserAuthentication()->getIdentity();

				if(!$user_mapper->getMeta($user->getId(), 'eway_token_id'))
					throw new \Exception('Billing informaton not found. Please add credit card and other billing information before you can upgrade the report.');

				$report->setPaid(1);
				$report = $report_service->save($report);

				return new JsonModel(array('success'=>true,'message' =>'Report upgraded. All report options are now available for you.'));
			} catch (\Exception $e) {
				return new JsonModel(array('success'=>false,'message' =>$e->getMessage()));
			}

        }
}

