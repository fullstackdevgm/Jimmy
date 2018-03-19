<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\ActivityLogInterface as ActivityLogEntityInterface;

class ActivityLog extends AbstractDbMapper implements ActivityLogInterface
{
    protected $tableName  			= 'activity_log';
    
    public function fetchAll(){

	    $select = $this->getSelect()->order('created DESC');
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    public function findByParent($parentId, $limit)
    {
        $select = $this->getSelect()
                       ->where( array('parent_id' => $parentId))
                       ->order('created DESC')
                       ->limit($limit);  
                       
        $entity = $this->select($select);       
        $this->getEventManager()->trigger( 'find', $this, array('entity' => $entity));
        return $entity;
    }
    
    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
      
        $entity->setCreated(date('Y-m-d G:i:s'));
        $result = parent::insert($entity, $this->tableName, $hydrator);

        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }

        if(!$tableName) {
           $tableName = $this->tableName;
        }
        return parent::update($entity, $where, $this->tableName, $hydrator);
    }

  public function delete($id,$where=null, $tableName = null)
    {

		if (!$where) {
			$where = 'id = ' . $id;
		}

		# Delete from clients table
		return parent::delete($where,$this->tableName);
	}
}
