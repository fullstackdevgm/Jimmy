<?php
namespace Chat;

class Module
{
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
         return array(
             'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }


    public function getServiceConfig(){

        return array(
            'invokables' => array(
                'message_service'                => 'Chat\Service\Message',
            ),
            'factories' => array(

                'message_chat_mapper' => function ($sm) {
                    $mapper = new Mapper\Message();

                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\Chat\\Entity\\ChatMessage";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\MessageHydrator());

                    return $mapper;
                },
                'message_mapper' => function ($sm) {
                    $mapper = new Mapper\Message();

                    $mapper->setDbAdapter($sm->get('jimmybase_zend_db_adapter'));
                    $entityClass = "\\Chat\\Entity\\Message";
                    $mapper->setEntityPrototype(new $entityClass);
                    $mapper->setHydrator(new Mapper\MessageHydrator());

                    return $mapper;
                }
            ),
        );
    }


}
