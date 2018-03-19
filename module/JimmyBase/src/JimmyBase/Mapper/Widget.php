<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\WidgetInterface as WidgetEntityInterface;

class Widget extends AbstractDbMapper implements WidgetInterface
{
    protected $tableName  = 'widget';

    public function findById($id)
    {
		if(!$id)
		  return false;

        $select = $this->getSelect()
                       ->where(array('id' => $id));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
      
        return $entity;
 
    }



	public function findByReportId($report_id)
    {
        $select = $this->getSelect()
                       ->where(array('report_id' => $report_id))
                       ->order('order ASC')
                       ->order('created ASC');

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findByClientAccountId($client_account_id)
    {
        $select = $this->getSelect()
                       ->where(array('client_account_id' => $client_account_id))
                       ->order('order ASC');
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findByAgency($agency_id,$widget_id) {
    	$select = $this->getSelect()
	     				->columns(array('*'))
						->join("reports", 'widget.report_id = reports.id',array('id'),'left')
						->join("client",  'reports.user_id = client.client_id',array('client_id'),'left')
						->where(array('client.parent' => $agency_id));

        if($widget_id){
            $select->where(array('widget.id' => $widget_id));
            //echo $select->getSqlString();
            $entity = $this->select($select)->current();
        } else
		    $entity = $this->select($select);

    	$this->getEventManager()->trigger('find', $this, array('entity' => $entity));
    	return $entity;
    }


	public function fetchAll(){

	    $select = $this->getSelect()->order('order ASC');;
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

	}


    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {

        $result = parent::insert($entity, $this->tableName, $hydrator);

        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }

		if(!$tableName)
		   $tableName = $this->tableName;

        return parent::update($entity, $where, $this->tableName, $hydrator);
    }

	public function delete($id,$where=null, $tableName = null)
    {

        if (!$where) {
            $where = 'id = ' . $id;
        }

        if(!$tableName)
           $tableName = $this->tableName;

        # Delete from widget
        if(parent::delete($where,$tableName))
           return true;
        else
           return false;

    }


    public function deleteByClientAccount($client_account_id,$where=null, $tableName = null)
    {
        if (!$where) {
            $where = 'client_account_id = ' . $client_account_id;
        }

        if(!$tableName)
           $tableName = $this->tableName;

        # Delete from widget
        if(parent::delete($where,$tableName))
           return true;
        else
           return false;
    }

    public function deleteByReport($report_id,$where=null, $tableName = null)
    {

        if (!$where) {
            $where = 'report_id = ' . $report_id;
        }

        if(!$tableName)
           $tableName = $this->tableName;

        # Delete from widget
        if(parent::delete($where,$tableName))
           return true;
        else
           return false;

    }
}
