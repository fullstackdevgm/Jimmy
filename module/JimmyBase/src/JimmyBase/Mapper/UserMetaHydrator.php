<?php

namespace JimmyBase\Mapper;

use Zend\Stdlib\Hydrator\ClassMethods;
use JimmyBase\Entity\UserInterface as UserEntityInterface;

class UserMetaHydrator extends ClassMethods
{
    /**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    public function extract($object)
    {
        if (!$object instanceof UserEntityInterface) {
            throw new Exception\InvalidArgumentException('$object must be an instance of JimmyBase\Entity\UserInterface');
        }	
		
        /* @var $object UserInterface*/
        $data = parent::extract($object);
		
        $data = $this->mapField('id', 'user_id', $data);
        $data = $this->removeField('username', $data);
        $data = $this->removeField('name', $data);
        $data = $this->removeField('password', $data);
        $data = $this->removeField('email', $data);
        $data = $this->removeField('display_name', $data);
        $data = $this->removeField('state', $data);
        $data = $this->removeField('type', $data);
        $data = $this->removeField('created', $data);
        $data = $this->removeField('updated', $data);
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  object $object
     * @return UserInterface
     * @throws Exception\InvalidArgumentException
     */
    public function hydrate(array $data, $object)
    {

        if (!$object instanceof UserEntityInterface) {
            throw new Exception\InvalidArgumentException('$object must be an instance of JimmyBase\Entity\UserInterface');
        }
		
        $data = $this->mapField('user_id', 'id', $data);
		
        return parent::hydrate($data, $object);
    }
   
  
    protected function mapField($keyFrom, $keyTo, array $array)
    {
        $array[$keyTo] = $array[$keyFrom];
        unset($array[$keyFrom]);
        return $array;
    } 
	
	protected function removeField($key, array $array)
    {
        unset($array[$key]);
        return $array;
    }
}
