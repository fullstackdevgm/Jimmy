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
                            'jimmy-report' => array(
                            'type' => 'Segment',
                                'options' => array(
                                    'route' => '/reports[/:report_id]',
                                    'constraints' => array(
                                            'report_id' => '[0-9]+',
                                    ),
                                    'defaults' => array(
                                        'controller' => 'ReportApi',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' =>array(
                                        'recent'  => array(
                                                'type' => 'Segment',
                                                'options' => array(
                                                        'route' => '/recent[/:agency_id]',
                                                        'constraints' => array(
                                                                            'agency_id' => '[0-9]+',
                                                                        ),
                                                        'defaults' => array(
                                                                            'controller' => 'ReportApi',
                                                                        ),
                                ),
                    ),
                    'widgets'  => array(
                                                        'type' => 'Literal',
                                                        'options' => array(
                                                                'route' => '/widgets',
                                                                'defaults' => array(
                                                                        'controller' => 'WidgetApi',
                                                                ),
                                                        ),
                                        ),
                                        'create'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/create[/:client_id]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'create',
                                                                'constraints' => array(
                                                                        'client_id'    => '[0-9]+',
                                                                ),
                                                        ),
                                                ),
                                        ),

                                        'wizard-create'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/wizard-create[/:client_id]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'createFromWizard',
                                                                'constraints' => array(
                                                                        'client_id'    => '[0-9]+',
                                                                ),
                                                        ),
                                                ),
                                        ),
                                        'edit'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/edit[/:report_id]',
                                                        'constraints' => array(
                                                                        'report_id'    => '[0-9]+',
                                                        ),
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'edit',
                                                        ),
                                                ),
                                        ),
                                        'delete'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/delete[/:report_id]',
                                                        'constraints' => array(
                                                                        'report_id'    => '[0-9]+',
                                                        ),
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'delete',
                                                        ),
                                                ),
                                        ),
                                        'getclients'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/getclients[/:agency_id]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'getclients',
                                                                'agency_id'     => '[0-9]+'
                                                        ),
                                                ),
                                        ),
                                        'save'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/save',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'save'
                                                        ),
                                                ),
                                        ),
                                        'view'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/view[/:report_id]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'view',
                                                                'report_id'     => '[0-9]+'

                                                        ),
                                                ),
                                        ),
                                        'clone'  => array(
                                                'type' => 'literal',
                                                'options' => array(
                                                        'route' => '/clone',
                                                        'defaults' => array(
                                                                'controller'   => 'ReportCloneApi',
                                                        ),
                                                ),
                                        ),
                                        'share'  => array(
                                                'type' => 'Segment',
                                                'options' => array(
                                                        'route' => '/share[/:sharing_id]',
                                                        'defaults' => array(
                                                                'controller'   => 'ReportShareApi',
                                                        ),
                                                        'constraints' => array(
                                                          'sharing_id' => '[0-9]+',
                                                        ),
                                                ),
                                        ),
                                        'removeshare'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/removeshare[/:report_id][/:user_id]',
                                                        'defaults' => array(
                                                                'controller'   => 'Report',
                                                                'action'       => 'removeshare',
                                                                'report_id'       => '[0-9]+',
                                                                'user_id'       => '[0-9]+'
                                                        ),
                                                ),
                                        ),
                                        'schedule'  => array(
                                                'type' => 'Segment',
                                                'options' => array(
                                                        'route' => '/schedule[/:schedule_id]',
                                                        'defaults' => array(
                                                                'controller'   => 'ReportScheduleApi',
                                                        ),
                                                        'constraints' => array(
                                                            'schedule_id' => '[0-9]+',
                                                        ),
                                                ),
                                        ),
                                        'removeschedule'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/removeschedule[/:report_id][/:user_id]',
                                                        'defaults' => array(
                                                                'controller'   => 'Report',
                                                                'action'       => 'removeschedule',
                                                                'report_id'       => '[0-9]+',
                                                                'user_id'       => '[0-9]+'
                                                        ),
                                                ),
                                        ),
                                        'download'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/download[/:report_id]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'download',
                                                                'report_id'     => '[0-9]+'

                                                        ),
                                                ),
                                        ),
                                        'sendschedulereports'  => array(
                                                'type' => 'literal',
                                                'options' => array(
                                                        'route' => '/send-schedule-reports',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'sendScheduleReports'
                                                        ),
                                                ),
                                        ),
                                        'download-report-file'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/download-file[/:file_name]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'downloadReportFile',

                                                        ),
                                                ),
                                        ),
                                        'template' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/template[/:action]',
                                                    'defaults' => array(
                                                        'controller' => 'Template',
                                                        'action' => 'saveTemplate'
                                                    ),
                                                ),
                                        ),

                                        'tour-visited'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/tour-visited',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'visitTour',

                                                        ),
                                                ),
                                        ),

                                        'image-upload'  => array(
                                                        'type' => 'segment',
                                                        'options' => array(
                                                            'route' => '/image-upload',
                                                            'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'imageUpload',
                                                            ),
                                                        ),
                                        ),

                                        'downloadone'  => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                        'route' => '/downloadone[/:widget_id]',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'downloadOne',
                                                                'widget_id'     => '[0-9]+'

                                                        ),
                                                ),
                                        ),
                                        'connect'  => array(
                                                'type' => 'literal',
                                                'options' => array(
                                                        'route' => '/connect',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'connect',

                                                        ),
                                                ),
                                        ),
                                        'upgrade' => array(
                                                'type' => 'literal',
                                                'options' => array(
                                                        'route' => '/upgrade',
                                                        'defaults' => array(
                                                                'controller' => 'Report',
                                                                'action'     => 'upgrade',
                                                        ),
                                                ),
                                        ),
                                ),

                            ),
                                'shared-reports' => array(
                                                        'type' => 'Literal',
                                                        'options' => array(
                                                                'route' => '/shared-reports',
                                                                'defaults' => array(
                                                                        'controller' => 'Report',
                                                                        'action'     => 'sharedReports',
                                                                ),
                                                        ),
                                ),
                               
                                'jimmy-widget' => array(
                                                        'type' => 'Segment',
                                                        'options' => array(
                                                                    'route' => '/widget[/:widget_id]',
                                                                    'defaults' => array(
                                                                            'controller' => 'WidgetApi',
                                                                    ),
                                                                    'constraints' => array(
                                                                        'widget_id' => '[0-9]+',
                                                                    ),
                                                        ),
                                                        'may_terminate' => true,
                                                        'child_routes' =>array(
                                                                'messages'  => array(
                                                                        'type' => 'Literal',
                                                                        'options' => array(
                                                                                'route' => '/messages',
                                                                                'defaults' => array(
                                                                                        'controller' => 'MessageApi',
                                                                                ),
                                                                        ),
                                                                )
                                                        )
                                ),
            
                                'insight' => array(
                                                        'type' => 'Literal',
                                                        'options' => array(
                                                                'route' => '/insights',
                                                                'default' => array(
                                                                    'controller' => 'Insight',
                                                                    'action' => 'getInsightList'                                                                      
                                                                )
                                                        ),
                                                        'may_terminate' => true,
                                                        'child_routes' =>array(
                                                            'get-insight-list'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/insight-list',
									'defaults' => array(
										'controller' => 'Insight',
										'action'     => 'getInsightList',
									),
								),
                                                            ),
                                                            'get-widget-insight' => array(
                                                                'type' => 'segment',
                                                                'options' => array(
                                                                             'route' => '/widget-insight',
                                                                             'defaults' => array(
                                                                                    'controller' => 'Insight',
                                                                                    'action'     => 'getWidgetInsightRaw',
                                                                                ),
                                                                            )
                                                            ),
                                                            'get-insight-options' => array(
                                                                'type' => 'segment',
                                                                'options' => array(
                                                                             'route' => '/insight-options',
                                                                             'defaults' => array(
                                                                                    'controller' => 'Insight',
                                                                                    'action'     => 'getInsightOptions',
                                                                                ),
                                                                            )
                                                            ),
                                                            'save-insight' => array(
                                                                'type' => 'segment',
                                                                'options' => array(
                                                                             'route' => '/save-insight',
                                                                             'defaults' => array(
                                                                                    'controller' => 'Insight',
                                                                                    'action'     => 'saveInsight',
                                                                                ),
                                                                            )
                                                            )
                                                        ),
                                    ),
                               
            
                                'braintree-payment' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/braintree-payment',
							'defaults' => array(
								'controller' => 'Payment',
								'action'     => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' =>array(
							'get-token'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/get-token',
									'defaults' => array(
										'controller' => 'BraintreePayment',
										'action'     => 'getToken',
									),
								),
							),
                                                        'create-subscription'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/create-subscription',
									'defaults' => array(
										'controller' => 'BraintreePayment',
										'action'     => 'createCustomerSubscription',
									),
								),
							),
                                                        'update-subscription'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/update-subscription',
									'defaults' => array(
										'controller' => 'BraintreePayment',
										'action'     => 'updateSubscription',
									),
								),
							),
                                                        'cancel-subscription'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/cancel-subscription',
									'defaults' => array(
										'controller' => 'BraintreePayment',
										'action'     => 'cancelSubscription',
									),
								),
							),
                                                        'update-customer'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/update-customer',
									'defaults' => array(
										'controller' => 'BraintreePayment',
										'action'     => 'updateCustomer',
									),
								),
							),
                                                        'load-invoice'  => array(
                                'type' => 'Literal',
                                'options' => array(
                                    'route' => '/load-invoice',
                                    'defaults' => array(
                                        'controller' => 'BraintreePayment',
                                        'action'     => 'getInvoice',
                                    ),
                                ),
                            ),
                                                        'download-invoice'  => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/download-invoice[/:transaction_id]',
                                    'defaults' => array(
                                        'controller' => 'BraintreePayment',
                                        'action'     => 'downloadInvoiceFile',
                                    ),
                                ),
                            ),
                                                        'download'  => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/download[/:transaction_id]',
                                    'defaults' => array(
                                        'controller' => 'BraintreePayment',
                                        'action'     => 'download',
                                    ),
                                ),
                            ),
							                                                     
						),
				),
				'payment-controller' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/payment',
							'defaults' => array(
								'controller' => 'Payment',
								'action'     => 'index',
							),
						),
						'may_terminate' => true,
						'child_routes' =>array(
							'process'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/process',
									'defaults' => array(
										'controller' => 'Payment',
										'action'     => 'process',
									),
								),
							),
							'recurring_check'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/recurring_check',
									'defaults' => array(
										'controller' => 'Payment',
										'action'     => 'query',
									),
								),
							),                                                        
						),
				),
                        ), 
         ),

     'console' => array(
        'router' => array(
            'routes'=> array(
                'recurring_check'  => array(
                            'options' => array(
                                'route' => 'recurring_check',
                                'defaults' => array(
                                    'controller' => 'Payment',
                                    'action'     => 'query',
                                ),
                            ),
                ),
                'schedule-reports'  => array(
                            'options' => array(
                                'route' => 'schedule-reports',
                                'defaults' => array(
                                    'controller' => 'Report',
                                    'action'     => 'sendScheduleReports',
                                ),
                            ),
                ),
                                'refresh-tokens'  => array(
                            'options' => array(
                                'route' => 'refresh-tokens',
                                'defaults' => array(
                                    'controller' => 'Report',
                                    'action'     => 'refreshTokens',
                                ),
                            ),
                ),
                                  'copy-token'  => array(
                            'options' => array(
                                'route' => 'copy-token',
                                'defaults' => array(
                                    'controller' => 'Report',
                                    'action'     => 'copyToken',
                                ),
                            ),
                ),
                                'change-schedule'  => array(
                            'options' => array(
                                'route' => 'change-schedule',
                                'defaults' => array(
                                    'controller' => 'Report',
                                    'action'     => 'changeSchedule',
                                ),
                            ),
                ),
                                'send-schedule-parent'  => array(
                            'options' => array(
                                'route' => 'send-schedule-parent <parentId>',
                                'defaults' => array(
                                    'controller' => 'Report',
                                    'action'     => 'sendReportsToParent',
                                ),
                            ),
                ),


            )
        )
    ),
    'controllers' => array(
       'invokables' => array(
                   'Client'         => 'JimmyBase\Controller\ClientController',
                   'Report'         => 'JimmyBase\Controller\ReportController',
                   'Template'       => 'JimmyBase\Controller\TemplateController',
                   'ReportApi'      => 'JimmyBase\Controller\ReportApiController',
                   'ReportCloneApi' => 'JimmyBase\Controller\ReportCloneApiController',
                   'ReportShareApi' => 'JimmyBase\Controller\ReportShareApiController',
                   'ReportScheduleApi' => 'JimmyBase\Controller\ReportScheduleApiController',
                   'Insight'          => 'JimmyBase\Controller\InsightController',
                   'Widget'    	=> 'JimmyBase\Controller\WidgetController',
                   'WidgetApi'    	=> 'JimmyBase\Controller\WidgetApiController',
                   'Payment'    	=> 'JimmyBase\Controller\PaymentController',
                   'AdminReport'   => 'JimmyBase\Controller\ReportController',
                   'BraintreePayment' => 'JimmyBase\Controller\BraintreePaymentController'

       ),
    ),
    'service_manager' => array(
            'aliases' => array(
                'jimmybase_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
            ),
            'factories' => array(
                'JimmyBase\Provider\Identity\ZfcUserZendDb' => 'JimmyBase\Service\ZfcUserZendDbIdentityProviderServiceFactory',
            )
        ),
    'view_helpers' => array(
            'invokables' => array(
                'metrics' => 'JimmyBase\View\Helper\Metrics',
            ),
        ),
    'view_manager' => array(
        'template_path_stack'           => array(__DIR__ . '/../view' ),
        'template_map' => array(
            'actions-bar'               => __DIR__ . '/../view/jimmy-base/partials/actions-bar.phtml',
            'reports'                   => __DIR__ . '/../view/jimmy-base/client/reports.phtml',
            'report-list'               => __DIR__ . '/../view/jimmy-base/report/list.phtml',
            'report-view'               => __DIR__ . '/../view/jimmy-base/report/view.phtml',
            'report-form'               => __DIR__ . '/../view/jimmy-base/report/form.phtml',
            'report-create'             => __DIR__ . '/../view/jimmy-base/report/create.phtml',
            'report-templates'          => __DIR__ . '/../view/jimmy-base/report/templates.phtml',
            'report-workspace'          => __DIR__ . '/../view/jimmy-base/report/workspace.phtml',
            'client-account'            => __DIR__ . '/../view/jimmy-base/client/account.phtml',
            'client-edit'               => __DIR__ . '/../view/jimmy-base/client/edit.phtml',
            'client-changepwd'          => __DIR__ . '/../view/jimmy-base/client/changepwd.phtml',
            'widget'                    => __DIR__ . '/../view/jimmy-base/widget/widget.phtml',
            'widget-form'               => __DIR__ . '/../view/jimmy-base/widget/form.phtml',
            'widget-list'               => __DIR__ . '/../view/jimmy-base/widget/list.phtml',
            'kpi'                       => __DIR__ . '/../view/jimmy-base/widget/kpi.phtml',
            'notes'                     => __DIR__ . '/../view/jimmy-base/widget/notes.phtml',
            'table'                     => __DIR__ . '/../view/jimmy-base/widget/table.phtml',
            'graph'                     => __DIR__ . '/../view/jimmy-base/widget/graph.phtml',
            'graph-download'            => __DIR__ . '/../view/jimmy-base/widget/graph-download.phtml',
            'table-download'            => __DIR__ . '/../view/jimmy-base/widget/table-download.phtml',
            'piechart-download'         => __DIR__ . '/../view/jimmy-base/widget/piechart-download.phtml',
            'invoice-failure'           => __DIR__ . '/../view/jimmy-base/emails/invoice-failure.phtml',

        ),
        ),

);
