<?php

namespace JimmyBase\Mapper;

use Zend\StdLib\Hydrator\ClassMethods;
use JimmyBase\Entity\UserCancelLogInterface as UserCancelLogEntityInterface;

class UserCancelLogHydrator extends ClassMethods {

	/**
     * Extract values from an object
     *
     * @param  object $object
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    public function extract($object)
    {
        if (!$object instanceof UserCancelLogEntityInterface) {
            throw new Exception\InvalidArgumentException('$object must be an instance of ZfcUser\Entity\UserCancelLogInterface');
        }
		
        /* @var $object UserInterface*/
        $data = parent::extract($object);
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
        if (!$object instanceof UserCancelLogEntityInterface) {
            throw new Exception\InvalidArgumentException('$object must be an instance of ZfcUser\Entity\UsercancelLogInterface');
        }
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

    protected function addField($key, $value, array $array)
    {
        $array[$key] = $value;
        return $array;
    }

}