<?php
/**
 * Handling all Braintree payments.
 *
 * @author Naveen Jose
 *
 */
namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Braintree\Configuration as BtConfig;
use Braintree\ClientToken as BtToken;
use Braintree\Customer;

class BraintreePaymentController extends AbstractActionController
{

  public function getTokenAction()
  {

     $btPaymentService  = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
      $token = $btPaymentService->getToken();
      return new JsonModel(array("token" => $token, "success" => 'true' ));
  }


  /**
   * Create a subscription for the customer.
   * Generates the customer id ad payment token.
   */
  public function createCustomerSubscriptionAction ()
  {
        $user = $this->ZfcUserAuthentication()->getIdentity();

        $content = $this->getRequest()->getContent();
        $postJason = \Zend\Json\Json::decode($content, \Zend\Json\Json::TYPE_OBJECT);

        try {

          $btPaymentService  = $this->getServiceLocator()->get('jimmybase_bt_payment_service');

          /**
           * Fetch user's eway customer and rebill id mail to naveen
           * Also delete the customer form eWay and db
           **/
          $user_mapper = $this->getServiceLocator()->get('jimmybase_user_mapper');
          $user_service = $this->getServiceLocator()->get('jimmybase_user_service');

          $eway_customer_id = $user_mapper->getMeta($user->getId(), 'eway_customer_id');
          $eway_rebill_id = $user_mapper->getMeta($user->getId(), 'eway_rebill_id');

          $btPaymentService->createCustomer($user,$postJason);

          if($eway_rebill_id) {
            // delete eway customer
            $payment_service = $this->getServiceLocator()->get('jimmybase_payment_service');
            $delete_response = $payment_service->cancelRecurringCustomer($user, $eway_customer_id, $eway_rebill_id);
            // remove customer_id and rebill_id from db
            $user->setKey('eway_customer_id');
            $user_service->removeMeta($user);
            $user->setKey('eway_rebill_id');
            $user_service->removeMeta($user);

            // mail to naveen
            $headers="MIME-Version: 1.0\r\nContent-type:text/html;charset=UTF-8\r\nFrom: admin@jimmydata.com\r\nReply-To: admin@jimmydata.com";
            $message = "Hello,<br/>User with user id ".$user->getId().", migrated to brain tree. His user data might have been deleted. Please check the database to make sure that his eWay recurring event have been deleted.<br/> CustomerId: ".$eway_customer_id."<br/>RebillId: ".$eway_rebill_id;
            mail('naveen@webmarketers.com.au', "A user successfully migrated to Braintree payments.", $message,$headers);
          }

          $result = array('success'=> true, 'message' => 'Customer created successfully.');

        } catch(\Exception $e) {
          $result = array('success' => false, 'message' => $e->getMessage());
        }

        return new JsonModel($result);
  }

  public function updateCustomerAction()
  {
        $user = $this->ZfcUserAuthentication()->getIdentity();
        $content = $this->getRequest()->getContent();
        $postJason = \Zend\Json\Json::decode($content);

        try {
          $btPaymentService  = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
          $result = $btPaymentService->updateCustomer($user,$postJason);
          $result = array('success'=>true, 'message'=>'Card and billing information updated.');
        } catch (\Exception $e) {
          $result = array('success' => false, 'message'=>$e->getMessage());
        }
      return new JsonModel($result);
  }

  /**
   * Add reports to the subscription.
   * Each report added is an addon.
   * This function nolonger used.
   */
  public function updateSubscriptionAction()
  { 
      $user = $this->$this->ZfcUserAuthentication()->getIdentity();
      $reports = $this->getServiceLocator()->get('jimmybase_reports_mapper')->findByAgency($current_user_id);
      $btPaymentService = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
      try {
        $result = $btPaymentService->updateSubscription($user, $reports->count());     
        $result = array('success'=> true, 'message'=>'Subsecription updated.');
      } catch (\Exception $e) {
        $result = array('success' => false, 'message'=>$e->getMessage());
      }
      return new JsonModel($result);
  }

  /**
   * To cancel a subscription
   */
  public function cancelSubscriptionAction()
  {
      $user = $this->ZfcUserAuthentication()->getIdentity();
      $btPaymentService = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
      try {
        $result = $btPaymentService->cancelSubscription($user);
        $result = array('success'=> true, 'message' => 'Subsecription cancelled.');
      } catch (\Exception $e) {
        $result = array('success' => false, 'message'=>$e->getMessage());
      }
      return new JsonModel($result);
  }

  /**
   * To get all transactions by the user;
   * */
  public function getInvoiceAction() {
    $user = $this->ZfcUserAuthentication()->getIdentity();
    $btPaymentService = $this->getServiceLocator()->get('jimmybase_bt_payment_service');    
    $result = $btPaymentService->getInvoice($user);

    return new JsonModel(array("success" => true, "data" =>$result));
  }

  /**
   * Fetch and prepare the invoice.
   **/
  public function downloadAction() {

    $user = $this->ZfcUserAuthentication()->getIdentity();

    $transaction_id = $this->params('transaction_id');

    $btPaymentService = $this->getServiceLocator()->get('jimmybase_bt_payment_service');
    $results = $btPaymentService->getInvoiceById($transaction_id);
    $item = '';
    $view = new ViewModel();

    $this->layout('layout/invoice');
    foreach ($results as $result) {
      $item = $result;
    }
    // var_dump($results);
    // echo '<br/><hr/>';
    // var_dump($item);
    // die;
    $view->setTemplate('jimmy-base/invoice/download.phtml')
         ->setVariables(array(
                          'customerFirstName'  => $item->customer['firstName'],
                          'customerLastName'  => $item->customer['lastName'],
                          'invoiceNumber' => $item->id,
                          'invoiceStatus' => $item->status,
                          'amount'        => $item->amount,
                          'paid'          => $item->status=='settled'?$item->amount:0,
                          'gst'           => $item->billing->countryCodeAlpha2=="AUS" ? 10 : 0,
                          'isAus'         => $item->billing->countryCodeAlpha2=="AUS",
                          'paymentDate'   => date_format( new \DateTime($item->planId->billingPeriodStartDate->date), 'd M o'),
                          'dueDate'       => date_format(new \DateTime($item->planId->billingPeriodEndDate->date), 'd M o'),
                          'displayAmount' => $item->billing->countryCodeAlpha2=="AUS" ? $item->amount*.9 : $item->amount
                        ));

    $html =  $this->getServiceLocator()
                       ->get('viewrenderer')
                       ->render($view);
    /*
    print_r($html);
    die;
    */
    
    $file    = './data/tmp-invoice/'.$transaction_id.'.html';
    $pdfUrl  = './data/tmp-invoice/'.$transaction_id.'.pdf';
    unlink($file);
    unlink($pdfUrl);
    try {
        if($item=='') 
          throw new \Exception("Invoice not available since the payment is not settled.");
          
        file_put_contents($file, $html);

        $config  = $this->getServiceLocator()->get('Config');
        $baseUrl = $config['jimmy-config']['baseurl'];


        ###PhantomJS PDF
        $htmlUrl     = $baseUrl.'resources/invoice-download/'.$transaction_id.'.html';
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
        return new JsonModel(array('success'=>true, 'message' =>'Invoice Download Complete', 'file'=>$baseUrl.'braintree-payment/download-invoice/'.$transaction_id));
    } catch (\Exception $e) {
        return new JsonModel(array('success'=>false, 'message' =>$e->getMessage()));
    }

    exit;

  }

  /**
   * To get a transaction for print.
   */
  public function downloadInvoiceFileAction() {

    $transaction_id = $this->params('transaction_id');

    $pdfFile = './data/tmp-invoice/'.$transaction_id.'.pdf';

    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Type: application/pdf');
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: '.filesize($pdfFile));

    if ($pdfFile!==null) {
        header("Content-Disposition: attachment; filename=JimmyData-Invoice.pdf");
    }

    readfile($pdfFile);
    return true;
  }

}
