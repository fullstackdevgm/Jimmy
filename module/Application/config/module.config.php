<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 *
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'client-login' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => 'FrontUser',
                        'action' => 'login',
                    ),
                ),
            ),
           'trial' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/trial',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'trial',
                    ),
                ),
            ),
           'upgradesuccessful' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/upgrade-successful',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'upgradeSuccess',
                    ),
                ),
            ),
           'blog' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/blog',
                    'defaults' => array(
                        'controller' => 'BlogApi',
                    ),
                ),
            ),
            'packages' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/packages[/:package_id]',
                    'constraints' => array(
                        'package_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'PackageApi',
                    ),
                ),
            ),
            'support' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/support',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'support',
                    ),
                ),
            ),
            'quote' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/quote',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'quote',
                    ),
                ),
            ),
            'auth-app' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/authapp[/:channel][/:referrer]',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'authapp',
                    ),
                    'constraints' => array(
                        'channel' => '[a-zA-Z0-9]+',
                        'referrer' => '[a-zA-Z0-9]+',
                    ),
                ),
            ),
            're-auth-app' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/re-authapp[/:client_account_id]',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'reauthapp',
                    ),
                    'constraints' => array(
                        'client_account_id' => '[a-zA-Z0-9]+',
                    ),
                ),
            ),
            're-auth-app-bulk' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/re-authapp-bulk[/:client_account_id]',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'reauthappBulk',
                    ),
                    'constraints' => array(
                        'client_account_id' => '[a-zA-Z0-9]+',
                    ),
                ),
            ),

            'wizard-auth-app' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/wizard-auth-app[/:channel]',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'wizardAuthApp',
                    ),
                    'constraints' => array(
                        'channelw' => '[a-zA-Z0-9]+',
                    ),
                ),
            ),
            'auth-callback' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/authcallback',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'authcallback',
                    ),
                ),
            ),
           'upgrade' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/upgrade',
                    'defaults' => array(
                        'controller' => 'PaymentApi',
                    ),
                ),
            ),
            'cancel' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/cancel',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'cancel',
                    ),
                ),
            ),
            'getcc' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/getccinfo',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'getccinfo',
                    ),
                ),
            ),
            'savecc' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/savecc',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'savecc',
                    ),
                ),
            ),
            'process' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/process',
                    'defaults' => array(
                        'controller' => 'Index',
                        'action' => 'process',
                    ),
                ),
            ),
            'activityLog' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/activity-log',
                    'defaults' => array(
                        'controller' => 'Dashboard',
                        'action' => 'activityLog',
                    ),
                ),
            ),
            'dashboard' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/dashboard',
                    'defaults' => array(
                        'controller' => 'Dashboard',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'info' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/info',
                            'defaults' => array(
                                'controller' => 'DashboardApi',
                            ),
                        ),
                    ),
                ),
            ),
            'info' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/info',
                            'defaults' => array(
                                'controller' => 'DashboardApi',
                            ),
                        ),
            ),
            'settings' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/settings',
                    'defaults' => array(
                        'controller' => 'FrontSettings',
                        'action' => 'index',

                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                        'save' => array(
                            'type' => 'Literal',
                            'options' => array(
                                'route' => '/save',
                                'defaults' => array(
                                    'controller' => 'FrontSettings',
                                    'action' => 'save',
                                ),
                            ),
                        ),
                ),
            ),
    'client' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/clients[/:client_id]',
                    'constraints' => array(
                        'client_id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'ClientApi',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                            'reports-list' => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/reports',
                                    'defaults' => array(
                                        'controller' => 'ReportApi',
                                    ),
                                ),
                            ),
                            'sources' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/sources[/:client_source_id]',
                                    'constraints' => array(
                                        'client_source_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'ClientSourceApi',
                                    ),
                                ),
                            ),
                            'campaigns' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/campaigns[/:client_account_id]',
                                    'defaults' => array(
                                        'controller' => 'CampaignApi',
                                    ),
                                    'constraints' => array(
                                        'client_account_id' => '[0-9]+',
                                    ),
                                ),
                            ),
                            'profiles' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/profiles[/:client_account_id]',
                                    'defaults' => array(
                                        'controller' => 'ProfileApi',
                                    ),
                                    'constraints' => array(
                                        'client_account_id' => '[0-9]+',
                                    ),
                                ),
                            ),
                             'segments' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/segments[/:client_account_id]',
                                    'defaults' => array(
                                        'controller' => 'SegmentApi',
                                    ),
                                    'constraints' => array(
                                        'client_account_id' => '[0-9]+',
                                    ),
                                ),
                            ),
                            'goals' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/goals[/:client_account_id][/:profile_id]',
                                    'defaults' => array(
                                        'controller' => 'GoalsApi',
                                    ),
                                ),
                            ),
                            'client-accounts' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/client-accounts',
                                    'defaults' => array(
                                        'controller' => 'ClientSourceApi',
                                    ),
                                ),
                            ),

                            'upload-logo' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/upload-logo',
                                    'defaults' => array(
                                        'controller' => 'Client',
                                        'action' => 'uploadLogo',
                                    ),
                                ),
                            ),

                        ),
            ),

            'source' => array(
                'type' => 'Segment',
                'options' => array(
                            'route' => '/source[/:sourceId]',
                            'constraints' => array(
                                 'sourceId' => '[0-9]+',
                            ),
                            'defaults' => array(
                                'controller' => 'SourceApi',
                            ),
                        ),

                ),
            'client-acc' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/client-source/get-clients',
                                    'defaults' => array(
                                        'controller' => 'Client',
                                        'action' => 'getClients',
                                     ),
                                ),
                            ),
            'unmapped-clients' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/client/get-unmapped-clients',
                                    'defaults' => array(
                                        'controller' => 'Client',
                                        'action' => 'getUnmappedClients',
                                     ),
                                ),
                            ),
            'migration' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/client/check-migration-status',
                                    'defaults' => array(
                                        'controller' => 'Client',
                                        'action' => 'checkMigrationStatus',
                                     ),
                                ),
                            ),
            'migration-done' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/client/migration-done',
                                    'defaults' => array(
                                        'controller' => 'Client',
                                        'action' => 'migrationDone',
                                     ),
                                ),
                            ),

            'user' => array(
                'type' => 'Literal',
                'priority' => 5000,
                'options' => array(
                    'route' => '/user',
                    'defaults' => array(
                        'controller' => 'FrontUser',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'save-title' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/save-title[/:user_id]',
                            'defaults' => array(
                                'controller' => 'FrontUser',
                                'action' => 'saveTitle',
                            ),
                            'constraints' => array(
                                'user_id' => '[0-9]+',
                            ),
                        ),
                    ),
                    'login' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/login',
                            'defaults' => array(
                            /*
                                FrontUser Login is used to override the zfcuser/login action as some customizations have been done in the login form.
                                Overriding zfcuser login action would also allow us to use the custom layout for the login page
                            */
                                'controller' => 'FrontUser',
                                'action' => 'login',
                            ),
                        ),
                        'may_terminate' => true,
                        'child_routes' => array(
                            'provider' => array(
                                'type' => 'Segment',
                                'options' => array(
                                    'route' => '/:provider',
                                    'constraints' => array(
                                        'provider' => '[a-zA-Z][a-zA-Z0-9_-]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'FrontUser',
                                        'action' => 'provider-login',
                                    ),
                                ),
                            ),
                        ),
                    ),
                    'resetpass' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/resetpass[/:code]',
                            'constraints' => array(
                                    'code' => '[a-zA-Z0-9\~\`\!\@\#\$\%\^\&\*\(\)\_\-\+\=\{\}\[\]\|\:\;\<\>\.\?\/\\\\]+',
                            ),
                            'defaults' => array(
                                'controller' => 'FrontUser',
                                'action' => 'resetpass',
                            ),
                        ),
                    ),
                    'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/logout',
                            'defaults' => array(
                                // Should use the ScnSocial User logout action to properly logout the hauth session and the zfcuser session
                                'controller' => 'ScnSocialAuth-User',
                                'action' => 'logout',
                            ),
                        ),
                    ),
                    'payments' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/payments',
                            'defaults' => array(
                                'controller' => 'FrontUser',
                                'action' => 'payments',
                            ),
                        ),
                    ),
                    'upload-thumb' =>array(
                        'type'    => 'literal',
                        'options' => array(
                            'route'   => '/upload-thumb',
                            'defaults' => array(
                                'controller' =>'FrontUser',
                                'action'     => 'uploadThumb'
                            )
                        ),
                    ),
                    'upload-logo' =>array(
                        'type'    => 'literal',
                        'options' => array(
                            'route' => '/upload-logo',
                            'defaults' => array(
                                'controller' => 'FrontUser',
                                'action' => 'uploadLogo',
                            ),
                        ),
                    ),
                    'remove-logo' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/remove-logo',
                            'defaults' => array(
                                'controller' => 'FrontUser',
                                'action'     => 'removeLogo',
                            ),
                        ),
                    ),
                    'remove-thumb'  => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/remove-thumb',
                            'defaults' => array(
                                'controller' => 'FrontUser',
                                'action'     => 'removeThumb',
                            ),
                        ),
                    ),
                ),
            ),
            'metrics-options' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/metrics-options[/:channel][/:widget_type]',
                    'defaults' => array(
                        'controller' => 'MetricsOptionsApi',
                    ),
                ),
            ),
            'changepwd' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/changepwd',
                    'defaults' => array(
                        'controller' => 'FrontUser',
                        'action' => 'changepass',
                    ),
                ),
            ),
            'coworker' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/coworker[/:coworker_id]',
                    'defaults' => array(
                        'controller' => 'CoworkerApi',
                    ),
                ),
            ),
      ),
    ),
    'service_manager' => array(
        'aliases' => array(
           'application_user_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ),
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
            'ScnSocialAuth\Authentication\Adapter\HybridAuth' => 'Application\Service\HybridAuthAdapterFactory',

        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__.'/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'MetricsOptionsApi' => 'JimmyBase\Controller\MetricsOptionsApiController',
            'ClientApi' => 'JimmyBase\Controller\ClientApiController',
            'BlogApi' => 'JimmyBase\Controller\BlogApiController',
            'CampaignApi' => 'JimmyBase\Controller\CampaignApiController',
            'PackageApi' => 'Application\Controller\PackageApiController',
            'ProfileApi' => 'JimmyBase\Controller\ProfileApiController',
            'GoalsApi' => 'JimmyBase\Controller\GoalsApiController',
            'PaymentApi' => 'Application\Controller\PaymentApiController',
            'ClientSourceApi' => 'JimmyBase\Controller\ClientSourceApiController',
            'Dashboard' => 'Application\Controller\DashboardController',
            'DashboardApi' => 'Application\Controller\DashboardApiController',
            'FrontUser' => 'Application\Controller\UserController',
            'CoworkerApi' => 'Application\Controller\CoworkerApiController',
            'zfcuser' => 'ZfcUser\Controller\UserController',
            'FrontSettings' => 'Application\Controller\SettingsController',
            'SegmentApi' => 'JimmyBase\Controller\SegmentApiController',
            'SourceApi' => 'JimmyBase\Controller\SourceApiController',

        ),
        'factories' => array(
            'Index' => 'Application\Controller\Factory\IndexControllerFactory',
        ),
     ),
     'view_helpers' => array(
        'invokables' => array(
            'socialSignInButton' => 'Application\View\Helper\SocialSignInButton',
        ),
        'factories' => array(
            'scnUserProvider' => 'ScnSocialAuth\Service\UserProviderViewHelperFactory',
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__.'/../view/layout/layout.phtml',
            'layout/download' => __DIR__.'/../view/layout/layout-download.phtml',
            'index/index' => __DIR__.'/../view/index/index.phtml',
            'error/404' => __DIR__.'/../view/error/404.phtml',
            'error/index' => __DIR__.'/../view/error/index.phtml',
            'login1' => __DIR__.'/../view/application/user/login.phtml',
            'google-login-widget' => __DIR__.'/../view/application/user/login-widgets/google.phtml',
            'login' => __DIR__.'/../view/application/index/login.phtml',
        ),
        'template_path_stack' => array(__DIR__.'/../view'),
    ),
);
