<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\UserTokenInterface as UserTokenEntityInterface;

class UserToken extends AbstractDbMapper implements UserTokenInterface
{
    protected $tableName = 'user_token';
	
	
    public function findByParent($parentId)
    {	
        $select = $this->getSelect()	
                       ->where(array('parent_id' => $parentId));

		
        $entity = $this->select($select);  
		
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
		
		return $entity;
    }
    
    
    public function findSourceExist($parentId, $name) {
         $select = $this->getSelect()	
                       ->where(array('parent_id' => $parentId))
                       ->where(array('name' => $name));

		
        $entity = $this->select($select)->current();  
		
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
		
		return $entity;
        
    }
 	
    public function findById($id)
    {
        $select = $this->getSelect()
			->where(array('id'  => $id));
        $entity = $this->select($select)->current();
	
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

 	
	
    public function fetchAll()
    {

        $select = $this->getSelect();				  
                 

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
	
    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
		
        $result = parent::insert($entity, $tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());
        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'id = ' . $entity->getId();
        }
        parent::update($entity, $where, $tableName, $hydrator);    	
    }
	
	
    public function delete($id,$where=null, $tableName = null)
    {
		
        if (!$where) {
                $where = 'id = ' . $id;
        }
        parent::delete($where,$this->tableNameClient);	    
    }
}
