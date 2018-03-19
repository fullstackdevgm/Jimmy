<?php

namespace Chat\Mapper;

use Zend\Stdlib\Hydrator\ClassMethods;
use Chat\Entity\MessageInterface as MessageEntityInterface;

class MessageHydrator extends ClassMethods
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

        /* @var $object MessageInterface*/
        $data = parent::extract($object);
        return $data;
    }

    /**
     * Hydrate $object with the provided $data.
     *
     * @param  array $data
     * @param  object $object
     * @return MessageInterface
     * @throws Exception\InvalidArgumentException
     */
    public function hydrate(array $data, $object)
    {

        return parent::hydrate($data, $object);
    }

    protected function mapField($keyFrom, $keyTo, array $array)
    {
        $array[$keyTo] = $array[$keyFrom];
        unset($array[$keyFrom]);
        return $array;
    }
}
