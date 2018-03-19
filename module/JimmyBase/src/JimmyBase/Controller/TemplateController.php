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

class TemplateController extends AbstractActionController
{
    /**
     * Save Template Action.
     * 
     * @return JsonModel
     */
   public function saveTemplateAction() 
   {
        $content = $this->getRequest()->getContent();
        $contentArr = \Zend\Json\Json::decode($content); 
        $templateName = $contentArr->templateName;
        $user = $contentArr->userId;
        $reportId = $contentArr->reportId;   
        
        $templateService = $this->getTemplateService();
        $saveTemplate = $templateService->saveTemplate( $reportId, $templateName, $user);
        return new JsonModel($saveTemplate);
    }
    /**
     * List all the template.
     * 
     * @return JsonModel
     */
    public function listTemplatesAction()
    {
        $content = $this->getRequest()->getContent();
        $contentArr = \Zend\Json\Json::decode($content); 
        $user = $contentArr->userId;
        $userTemplates = $this->getTemplateService()->getUserTemplates($user);  
        return new JsonModel($userTemplates);
    }
    /**
     * Use Template.
     * 
     * @return JsonModel
     */
    public function useTemplateAction()
    {   
        $content = $this->getRequest()->getContent();
        $contentArr = \Zend\Json\Json::decode($content); 
        $reportId =  $contentArr->reportId;
        $templateId =  $contentArr->templateId;
        $campaign = $contentArr->campaign;
        $profile = $contentArr->profile;
        $clients = $contentArr->clients;
        $reportName = $contentArr->reportName;
        $clientId = $contentArr->clientAccId;
     
        if ($reportName) {  
            
            if (!$this->AclPlugin()->canCreateReport()) {
                    return new JsonModel(array("success"=>false,'message'=>'Cannot create new reports. The reports limit has been reached. Please upgrade your package.'));
            }
            $report_service   = $this->getServiceLocator()->get('jimmybase_reports_service');
            $client_service   = $this->getServiceLocator()->get('jimmybase_client_service');
            $client = $client_service->getClientMapper()->findById($clientId);
            if (!$client) {
                    return new JsonModel(array("success" => false,'message' => 'Client not found!'));
            }
            
            $report_data = array('title' => $reportName, 'client_id' => $clientId);
            $report = $report_service->save($report_data);
          
            $reportId = $report->getId();
        }
                        
        $json = $this->getTemplateService()->useTemplate($templateId, $reportId, $campaign, $profile, $clients);
         
        return new JsonModel($json);
    }
    
    /**
     * Delete Template.
     * 
     * @return JsonModel
     */
    public function deleteTemplateAction() {
        $content = $this->getRequest()->getContent();
        $contentArr = \Zend\Json\Json::decode($content); 
        $templateId = $contentArr->templateId;
        $json = $this->getTemplateService()->deleteTemplate($templateId);
        return new JsonModel($json);
    }
    
    /**
     * Get Template Service.
     * 
     * @return TemplateService
     */
    public function getTemplateService()
    {
        return  $this->getServiceLocator()->get('jimmybase_template_service');
    }
}
