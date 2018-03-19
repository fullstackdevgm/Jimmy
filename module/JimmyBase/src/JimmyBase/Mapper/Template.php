<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use Zend\Stdlib\Hydrator\HydratorInterface;
use JimmyBase\Entity\TemplateInterface as TemplateEntityInterface;

class Template extends AbstractDbMapper  implements TemplateInterface
{
    protected $tableName      = 'template';    
    public function findByTemplateName($templateName,$userId)
    {	
        $select = $this->getSelect()					   
                       ->where( array('template_name' => $templateName,'user_id' => $userId) );
        $entity = $this->select($select)->current();  
      
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

		return $entity;
    }
    public function findById($id)
    {	
        $select = $this->getSelect()					   
                       ->where( array('id' => $id) );
        $entity = $this->select($select)->current();  
      
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

		return $entity;
    }
    
    public function findByUser($userId)
    {
        $select = $this->getSelect()
                       ->where( array('user_id' => $userId));
        $entity = $this->select($select);
        $this->getEventManager()->trigger( 'find', $this, array('entity' => $entity));
        return $entity;
    }
    
     public function findByType($type)
    {
        $select = $this->getSelect()
                       ->where( array('type' => $type));
        $entity = $this->select($select);
        $this->getEventManager()->trigger( 'find', $this, array('entity' => $entity));
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