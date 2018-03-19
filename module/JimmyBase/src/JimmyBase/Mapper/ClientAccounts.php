<?php

namespace JimmyBase\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Entity\ClientAccountsInterface as ClientAccountsEntityInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;

class ClientAccounts extends AbstractDbMapper implements ClientAccountsInterface
{
    protected $tableName  			= 'client_accounts';


    public function findById($id)
    {
        $select = $this->getSelect()
                        ->where(array('id' => $id));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    
    public function findByClientId($client_id)
    {
        $select = $this->getSelect()
                        ->where(array('client_id' => $client_id));

        $entity = $this->select($select);

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    

    public function findByChannel($client_id,$channel)
    {

        $select = $this->getSelect()
                        ->where(array('client_id' => $client_id))
                        ->where(array('channel'   => $channel));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    public function findByParent($parent) {
        $select = $this->getSelect()
                       ->columns(array('*'))
                       ->join('client', 'client_accounts.client_id = client.client_id',array('client_id'))
                       ->where(array('client.parent' => $parent));
        
        $entity = $this->select($select)->getDataSource();       
        return $entity;
        
    }
    
     public function findByParentandChannel($parent, $channel) {
        $select = $this->getSelect()
                       ->columns(array('*'))
                       ->join('client', 'client_accounts.client_id = client.client_id',array('client_id'))
                       ->where(array('client.parent' => $parent))
                       ->where(array('client_accounts.channel' => $channel));
        
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
        
    }
    /**
     * Get all the ClientAccounts which dont have a token Id for the given $patent.
     * 
     * @param integer $parent
     * @return JimmyBase\Entity\ClientAccounts
     */
    public function findUnmappedClients($parent) {
        $select = $this->getSelect()
                       ->columns(array('*'))->join('client', 'client_accounts.client_id = client.client_id',array('client_id'))
                       ->where(array('client.parent' => $parent))
                       ->where(array('client_accounts.user_token_id' => null, 'client_accounts.user_token_id' => 0));
        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }
    
    /**
     * 
     * @param type $templateId
     * @return type
     */
    public function findByTemplateId($templateId) {
        $select = $this->getSelect()
                    ->columns(array('*'))
                    ->join("client", 'client_accounts.client_id = client.client_id',array('client_id'))
		    ->join("reports", 'reports.user_id = client.client_id',array('user_id'))
                    ->where(array("reports.id" => $templateId));
        $entity = $this->select($select);

    	$this->getEventManager()->trigger('find', $this, array('entity' => $entity));
    	return $entity;
    }
 
    public function findByAccountId($id,$account_id)
    {

        $select = $this->getSelect()
                        ->where(array('client_id'  => $id))
                        ->where(array('account_id' => $account_id));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }


	public function fetchAll($enabled=1){

	    $select = $this->getSelect();				  

        $entity = $this->select($select);
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;

	}

    public function getCount() {
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


      return  parent::update($entity, $where, $tableName, $hydrator);

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