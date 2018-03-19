<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'message' => array(
                'type' => 'segment',
                'options' => array(
                    'route'    => '/message[/:msg_id]',
                    'defaults' => array(
                        'controller' => 'MessageApi',
                    ),
                ),
            ),
      ),
    ),

    'controllers' => array(
        'invokables' => array(
            'MessageApi'        => 'Chat\Controller\MessageApiController',
        ),
      ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_path_stack' => array(__DIR__ . '/../view' ),
    )
);
