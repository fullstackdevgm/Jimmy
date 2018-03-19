<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\VisitedTourInterface as VisitedTourEntityInterface;

class VisitedTour extends AbstractDbMapper  implements VisitedTourInterface
{
    protected $tableName      = 'visited_tour';    
    public function findVisited($tourId, $userId)
    {	
        $select = $this->getSelect()					   
                       ->where(array('tour_id' => $tourId, 'user_id' => $userId));
        $entity = $this->select($select)->current();  
      
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

		return $entity;
    }
    
    public function fetchAll(){

	    $select = $this->getSelect()->order('order ASC');
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
 	

}