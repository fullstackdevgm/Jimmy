<?php
namespace Admin\Authentication\Adapter;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Admin\Authentication\Adapter\AdapterChain;

class AdapterChainServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $chain = new AdapterChain;
        $adapter = $serviceLocator->get('Admin\Authentication\Adapter\Db');
        $chain->getEventManager()->attach('authenticate', array($adapter, 'authenticate'));
        return $chain;
    }
}
