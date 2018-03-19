<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Entity\ClientInterface as ClientEntityInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class Client extends AbstractDbMapper implements ClientInterface
{
    protected $tableName  			= 'client';

    public function findByEmail($email)
    {	
        $select = $this->getSelect()					   
                       ->where(array('email' => $email));


        $entity = $this->select($select)->current();  

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

		return $entity;
    }
 	

    public function findById($id)
    {
        $select = $this->getSelect()
                        //->join("client_accounts", 'client_accounts.client_id = client.client_id',array('*'),'left')
                        ->where(array('client.client_id' => $id));



        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
   

    public function findByIdToArray($id)
    {   
        if(!$id)
          return false;
          
        $select = $this->getSelect()
                       ->where(array('client_id' => $id));

        $entities = $this->select($select)->toArray();
        $entity = $entities[0];
        
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findRefreshClients()
    {
        $num = new \Zend\Db\Sql\Expression('COUNT(*)');
        $select = $this->getSelect()
                       ->columns(array('parent', 'count' => $num))
                       ->group(parent)
                       ->having("`count`>25");
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
 	
    public function findByAdwordsClientId($adwords_client_id)
    {
        $select = $this->getSelect()						
                       ->where(array('adwords_client_id' => $adwords_client_id));

        $entity = $this->select($select)->current();
		
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
	
    public function findByParent($parent)
    {
          $select = $this->getSelect()						
                       ->where(array('parent' => $parent));

        $entity = $this->select($select);
		
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
        
    }


	public function fetchAll(){

	    $select = $this->getSelect();				  
        
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}


	public function fetchAllByAgency($agency_id,$limit=null){

      $select = $this->getSelect()                    
                       ->where(array('parent' => $agency_id))
                       ->order('created DESC');

        if($limit)
             $select->limit($limit);          

        $entity = $this->select($select);

        //echo $select->getSqlString();
        
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

    }

    public function countByAgency($agency_id){

      $select = $this->getSelect()        
                      ->columns(array('total'=>new \Zend\Db\Sql\Expression('COUNT(*)')), false)
                      ->where(array('parent' => $agency_id));

        $entity = $this->select($select);
        //echo $select->getSqlString();
        var_dump($entity);
        
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

    }




    public function insert($entity, $tableName = null, HydratorInterface $hydrator = null)
    {
       // var_dump($hydrator);
        $result = parent::insert($entity, $tableName, $hydrator);
        $entity->setId($result->getGeneratedValue());


        return $result;
    }

    public function update($entity, $where = null, $tableName = null, HydratorInterface $hydrator = null)
    {
        if (!$where) {
            $where = 'client_id = ' . $entity->getId();
        }


       return parent::update($entity, $where, $tableName, $hydrator);

	}


    public function delete($id,$where=null, $tableName = null)
    {

		if (!$where) {
			$where = 'client_id = ' . $id;
		}

		# Delete from clients table
		return parent::delete($where,$this->tableName);
	}
}