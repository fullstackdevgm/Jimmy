<?php

/**
 * Insights Controller.
 *
 * @author Naveen Jose
 *
 */
namespace JimmyBase\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class InsightController extends AbstractActionController
{
    /**
     * Returns all the insight Types.
     * 
     * @return JsonModel
     */
   public function getInsightListAction() {
    $content = $this->getRequest()->getContent();
    $postJson = \Zend\Json\Json::decode($content);
    $channel = $postJson->channel;
    if ($channel =="googleanalytics") {
        $insightService =  $this->getServiceLocator()->get('jimmybase_analytics_insights_service');
        $insights = $insightService->getInsightType();
        
        
    }
    //This is to match the Chosen directive in the frontend!
    $insightList = array();
    foreach ($insights as $i) {
        $insightList[] = array("id" => $i, "label" => $i);
    }
    return new JsonModel(array("success" => true, "insights" => $insightList));
   }
  
  /**
   * Get the raw form of the insight from the widget table. 
   * This is for editing purposes.
   * 
   * @return JsonModel
   */ 
  public function getWidgetInsightRawAction() {
      $content = $this->getRequest()->getContent();
      $postJson = \Zend\Json\Json::decode($content);
      $widgetId = $postJson->id;
      $widgetService = $this->getServiceLocator()->get('jimmybase_widget_service');
      $widgetInsight = $widgetService->getMapper()->findById($widgetId)->getInsight();
      return new JsonModel(array("success" => true, "insightRaw" => $widgetInsight));
  }

  /**
   * Finds all the insight Types based on the selected insights.
   * 
   * @return JsonModel
   */
  public function getInsightOptionsAction() {
      
    $content = $this->getRequest()->getContent();
    $postJson = \Zend\Json\Json::decode($content);
    $channel = $postJson->channel;
    $selectedTypes = $postJson->insights;
    if ($channel =="googleanalytics") {
        $insightService =  $this->getServiceLocator()->get('jimmybase_analytics_insights_service');
        $insightOptions = $insightService->getInsightOptions($selectedTypes);
    }
    return new JsonModel(array("success" => true, "insightOptions" => $insightOptions));
  }
  
  public function saveInsightAction() {
    $content = $this->getRequest()->getContent();
    $postJson = \Zend\Json\Json::decode($content);
    $widgetId = $postJson->widgetId;
    $insightData = $postJson->insightData;
    $widgetService = $this->getServiceLocator()->get('jimmybase_widget_service');
    $widget = $widgetService->getMapper()->findById($widgetId);
    $widget->setStatus(1);
    $widget->setInsight($insightData);
    $widgetService->getMapper()->update($widget);
    return new JsonModel(array("success" => true, "message" => "insight Updated"));
    
      
  }
}