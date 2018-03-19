<?php

namespace JimmyBase\Mapper;

use Zend\Stdlib\Hydrator\HydratorInterface;

use ZfcBase\Mapper\AbstractDbMapper;
use JimmyBase\Entity\UserInterface as UserEntityInterface;
use JimmyBase\Mapper\UserCancelLogHydrator;
use ZfcUser\Mapper\User as ZfcUserMapper;

class User extends ZfcUserMapper implements UserInterface
{
    protected $tableName      = 'user';
	protected $tableNameMeta  = 'user_meta';
	protected $tableNameProvider  = 'user_provider';
    protected $tableNameCancelLog = 'user_cancel_log';
	protected $user_type      = array();


    public function findByEmail($email)
    {
    	//echo $email;exit;
		$select = $this->getSelect()
                       //->where(array('type'  => $this->getUserType()))
                       ->where(array('email' => strtolower(trim($email))));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));

		return $entity;
    }

    public function search($q){

		$select = $this->getSelect()
                       ->where("email like '{$q}%' ");

        $entity = $this->select($select);//->toArray();
        $this->getEventManager()->trigger('search', $this, array('entity' => $entity));

		return $entity;
    }


    public function findByUsername($username)
    {
        $select = $this->getSelect()
                        ->where(array('username' => $username));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }




    public function findById($id)
    {
        $select = $this->getSelect()
                        ->where(array('user.user_id' => $id));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

    public function findByIdToArray($id)
    {
        if(!$id)
          return false;

        $select = $this->getSelect()
                        ->where(array('user.user_id' => $id));

        $entities = $this->select($select)->toArray();
        $entity = $entities[0];

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
    }

	public function findByMeta($value){

		$select = $this->getSelect()
						//->columns(array('user_id'))
						->join("user_meta", 'user.user_id = user_meta.user_id')
						->where(array('user_meta.value'    => $value));

        $entity = $this->select($select)->current();

        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity?$entity:null;
	}

	// Find users to be billed
	public function findToBeInvoicedToday($date){

		$select = $this->getSelect()
						//->columns(array('user_id'))
						->join("user_meta", 'user.user_id = user_meta.user_id',array('key','value'),'left')
						->where(array('user_meta.key'   => 'next_payment_date'))
						->where(array('user_meta.value' => $date));

        $entity = $this->select($select);
       // echo $select->getSqlString();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity;
	}

	public function getMeta($id,$key){
		$select = $this->getSelect()
						->columns(array('user_id'))
						->join("user_meta", 'user.user_id = user_meta.user_id',array('key','value'),'left')
						->where(array('user.user_id' => $id))
						->where(array('user_meta.key' => $key));

        $entity = $this->select($select)->current();
        $this->getEventManager()->trigger('find', $this, array('entity' => $entity));
        return $entity?$entity->getValue():null;
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
            $where = 'user_id = ' . $entity->getId();
        }

        return parent::update($entity, $where, $tableName, $hydrator);
    }

	public function delete($id,$where=null, $tableName = null)
    {

		if (!$where) {
			$where = 'user_id = ' . $id;
		}



		# Delete from widget
		if(parent::delete($where,$tableName))
		   return true;
		else
		   return false;

	}

    public function deleteSchedules($userId) {

        $result = parent::getDbAdapter()->query('DELETE rs FROM report_schedule rs JOIN reports ON rs.report_id = reports.id WHERE reports.parent = '.$userId.' ;')->execute();

        return true;

    }

	public function setUserType($user_type){
		$this->user_type = $user_type;
	return $this;
	}

	public function getUserType(){
	 return $this->user_type;
	}

    /**
    * Removes agency logo
    * @param $user_id string Id of the user.
    * @return boolean;
    **/
    public function removeLogo($user_id) {
        $key = 'logo';
        
        $where = '`user_id` = '.$user_id.' AND `key` = \'logo\'';

        # Delete from widget
        if(parent::delete($where,$this->tableNameMeta))
           return true;
        else
           return false;
    }

    /**
    * Removes user logo
    * @param $user_id string Id of the user.
    * @return boolean;
    **/
    public function removeThumb($user_id) {
        $key = 'thumb';
        
        $where = '`user_id` = '.$user_id.' AND `key` = \'thumb\'';

        # Delete from widget
        if(parent::delete($where,$this->tableNameMeta))
           return true;
        else
           return false;
    }


}
