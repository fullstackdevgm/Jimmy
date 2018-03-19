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
            'admin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        'controller' => 'Admin',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
				   'settings' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/settings',
						    'defaults' => array(
                                'controller' => 'Settings',
                                'action'     => 'index',
                            ),
                        ),
						'may_terminate' => true,
						'child_routes' =>array(
									'updateaccount'  => array(
											'type' => 'literal',
											'options' => array(
												'route' => '/updateaccount',
												'defaults' => array(
													'controller' => 'Settings',
													'action'     => 'updateAccount',
												),
											),	
									),
									'changepwd'  => array(
											'type' => 'literal',
											'options' => array(
												'route' => '/changeadminpwd',
												'defaults' => array(
													'controller' => 'Settings',
													'action'     => 'changePwd',
												),
											),	
									),
									'clearcache'  => array(
											'type' => 'literal',
											'options' => array(
												'route' => '/clearcache',
												'defaults' => array(
													'controller' => 'Settings',
													'action'     => 'clearCache',
												),
											),	
									),
						
						)
					 ),	
					 'agency' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/agency',
						    'defaults' => array(
                                'controller' => 'Agency',
                                'action'     => 'index',
                            ),
                        ),              
					    'may_terminate' => true,
						'child_routes' => array(
							'add'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/add',
									'defaults' => array(
                                		'controller' => 'Agency',
                                		'action'     => 'add',
                            		),
								),	
							),
							'edit'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/edit[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Agency',
                                		'action'     => 'edit',
                            		),
								),	
							),
							'view'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/view[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Agency',
                                		'action'     => 'view',
                            		),
								),	
							),
							'save'  => array(
								'type' => 'literal',
								'options' => array(
									'route' => '/save',
									'defaults' => array(
                                		'controller' => 'Agency',
                                		'action'     => 'save',
                            		),
								),	
							),
							'changepwd'  => array(
								'type' => 'literal',
								'options' => array(
									'route' => '/changepwd',
									'defaults' => array(
                                		'controller' => 'Agency',
                                		'action'     => 'changePwd',
                            		),
								),	
							),
							'delete'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/delete[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Agency',
                                		'action'     => 'delete',
                            		),
								),	
							),
							'client' => array(
										'type' => 'Literal',
										'options' => array(
											'route' => '/client',
											'defaults' => array(
												'controller' => 'Client',
												'action'     => 'index',
											),
										),              
										'may_terminate' => true,
										'child_routes' => array(
											'add'  => array(
												'type' => 'Segment',
												'options' => array(
													'route' => '/add[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'add',
													),
												),	
											),
											'edit'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/edit[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'edit',
													),
												),	
											),
											'view'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/view[/:agency_id][/:id]',
													'constraints' => array(
															'agency_id' => '[0-9]+',
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'view',
													),
												),	
											),
											'save'  => array(
												'type' => 'literal',
												'options' => array(
													'route' => '/save',
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'save',
													),
												),	
											),
											'changepwd'  => array(
												'type' => 'literal',
												'options' => array(
													'route' => '/changepwd',
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'changePwd',
													),
												),	
											),
											'delete'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/delete[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'delete',
													),
												),	
											),
											'createreport'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/createreport[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'createReport',
													),
												),	
												'may_terminate' => true,
												'child_routes' => array(
													'wildcard' => array(
														'type' => 'Wildcard',
													),
												),
											),
											'report'  => array(
												'type' => 'Literal',
												'options' => array(
													'route' => '/report',
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'report',
													),
												),	
												'may_terminate' => true,
												'child_routes' => array(
														'create'  => array(
																'type' => 'segment',
																'options' => array(
																	'route' => '/create[/:id]',
																	'constraints' => array(
																			'id' => '[0-9]+'
																	),
																	'defaults' => array(
																		'controller' => 'Client',
																		'action'     => 'createReport',
																		'report_action' => 'create',
																	),
																),	
																'may_terminate' => true,
																'child_routes' => array(
																	'wildcard' => array(
																		'type' => 'Wildcard',
																	),
																),
														),
													),
													'edit'  => array(
																'type' => 'segment',
																'options' => array(
																	'route' => '/edit[/:id]',
																	'constraints' => array(
																			'id' => '[0-9]+',
																	),
																	'defaults' => array(
																		'controller' => 'Client',
																		'action'     => 'editReport',
																		'report_action' => 'edit',
																	),
																),	
																'may_terminate' => true,
																'child_routes' => array(
																	'wildcard' => array(
																		'type' => 'Wildcard',
																	),
																),
														),
												
											),
											'editreport'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/editreport[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'editReport',
													),
												),	
												'may_terminate' => true,
												'child_routes' => array(
													'wildcard' => array(
														'type' => 'Wildcard',
													),
												),
											),
											'fetchcampaigns'  => array(
												'type' => 'literal',
												'options' => array(
													'route' => '/fetchcampaigns',
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'fetchCampaigns',
													),
												)
											),
											'viewreport'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/viewreport[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'viewReport',
													),
												),	
											),
											'graph'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/graph[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'graph',
													),
												),	
											),
											'raw'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/raw[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'rawData',
													),
												),	
											),
										),
									
							
							)
						),
						
					),           
                    'client' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/client',
						    'defaults' => array(
                                'controller' => 'Client',
                                'action'     => 'index',
                            ),
                        ),              
					    'may_terminate' => true,
						'child_routes' => array(
							'add'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/add',
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'add',
                            		),
								),	
							),
							'edit'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/edit[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'edit',
                            		),
								),	
							),
							'view'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/view[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'view',
                            		),
								),	
							),
							'save'  => array(
								'type' => 'literal',
								'options' => array(
									'route' => '/save',
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'save',
                            		),
								),	
							),
							'changepwd'  => array(
								'type' => 'literal',
								'options' => array(
									'route' => '/changepwd',
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'changePwd',
                            		),
								),	
							),
							'delete'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/delete[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'delete',
                            		),
								),	
							),
							'createreport'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/createreport[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'createReport',
                            		),
								),	
								'may_terminate' => true,
								'child_routes' => array(
									'wildcard' => array(
										'type' => 'Wildcard',
									),
								),
							),
							'report'  => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/report',
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'report',
                            		),
								),	
								'may_terminate' => true,
								'child_routes' => array(
										'create'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/create[/:id]',
													'constraints' => array(
															'id' => '[0-9]+'
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'createReport',
														'report_action' => 'create',
													),
												),	
												'may_terminate' => true,
												'child_routes' => array(
													'wildcard' => array(
														'type' => 'Wildcard',
													),
												),
										),
									),
									'edit'  => array(
												'type' => 'segment',
												'options' => array(
													'route' => '/edit[/:id]',
													'constraints' => array(
															'id' => '[0-9]+',
													),
													'defaults' => array(
														'controller' => 'Client',
														'action'     => 'editReport',
														'report_action' => 'edit',
													),
												),	
												'may_terminate' => true,
												'child_routes' => array(
													'wildcard' => array(
														'type' => 'Wildcard',
													),
												),
										),
								
							),
							'editreport'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/editreport[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'editReport',
                            		),
								),	
								'may_terminate' => true,
								'child_routes' => array(
									'wildcard' => array(
										'type' => 'Wildcard',
									),
								),
							),
							'fetchcampaigns'  => array(
								'type' => 'literal',
								'options' => array(
									'route' => '/fetchcampaigns',
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'fetchCampaigns',
                            		),
								)
							),
							'viewreport'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/viewreport[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'viewReport',
                            		),
								),	
							),
							'graph'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/graph[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'graph',
                            		),
								),	
							),
							'raw'  => array(
								'type' => 'segment',
								'options' => array(
									'route' => '/raw[/:id]',
									'constraints' => array(
											'id' => '[0-9]+',
                    				),
									'defaults' => array(
                                		'controller' => 'Client',
                                		'action'     => 'rawData',
                            		),
								),	
							),
						),
                    ),
                   'login' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/login',
                            'defaults' => array(
                                'controller' => 'AdminUser',
                                'action'     => 'login',
                            ),
                        ),
                    ),
                    'authenticate' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/authenticate',
                            'defaults' => array(
                                'controller' => 'AdminUser',
                                'action'     => 'authenticate',
                            ),
                        ),
                    ),
                    'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/logout',
                            'defaults' => array(
                                'controller' => 'AdminUser',
                                'action'     => 'logout',
                            ),
                        ),
                    ),
					'report' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/report',
							'defaults' => array(
								'controller' => 'Report',
								'action'     => 'index',
							),
						),
						'may_terminate' => true,
					),
				'report' => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/report',
									'defaults' => array(
										'controller' => 'AdminReport',
										'action'     => 'index',
									),
								),
								'may_terminate' => true,
								'child_routes' =>array(
									'create'  => array(
										'type' => 'literal',
										'options' => array(
											'route' => '/create',
											'defaults' => array(
												'controller' => 'AdminReport',
												'action'     => 'create',
											),
										),	
									),
									'edit'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/edit[/:report_id]',
											'constraints' => array(
													'report_id' 	=> '[0-9]+',
											),
											'defaults' => array(
												'controller' => 'AdminReport',
												'action'     => 'edit',
											),
										),	
									),
									'delete'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/delete[/:report_id]',
											'constraints' => array(
													'report_id' 	=> '[0-9]+',
											),
											'defaults' => array(
												'controller' => 'AdminReport',
												'action'     => 'delete',
											),
										),	
									),
									'getclients'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/getclients[/:agency_id]',
											'defaults' => array(
												'controller' => 'AdminReport',
												'action'     => 'getclients',
												'agency_id'	 => '[0-9]+'
											),
										),	
									),
									'save'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/save',
											'defaults' => array(
												'controller' => 'AdminReport',
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
												'report_id'	 => '[0-9]+'

											),
										),	
									),
								)
							),
				'widget' => array(
								'type' => 'Literal',
								'options' => array(
									'route' => '/widget',
									'defaults' => array(
										'controller' => 'Widget',
										'action'     => 'index',
									),
								),
								'may_terminate' => true,
								'child_routes' =>array(
									'add'  => array(
										'type' => 'Segment',
										'options' => array(
											'route' => '/add[/:widget_type][/:client_id][/:report_id]',
											'constraints' => array(
													'client_id'   	=> '[0-9]+',
													'report_id' 	=> '[0-9]+',
													'widget_type'   => '[a-zA-Z]+',                    
											),
											'defaults' => array(
												'controller' => 'Widget',
												'action'     => 'add',
											),
										),	
									),
									'edit'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/edit[/:widget_id]',
											'constraints' => array(
													'widget_id' 	=> '[0-9]+',
											),
											'defaults' => array(
												'controller' => 'Widget',
												'action'     => 'edit',
											),
										),	
									),	
									'delete'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/delete[/:widget_id]',
											'constraints' => array(
													'widget_id' 	=> '[0-9]+',
											),
											'defaults' => array(
												'controller' => 'Widget',
												'action'     => 'delete',
											),
										),	
									),
									'list'  => array(
										'type' => 'Segment',
										'options' => array(
											'route' => '/list[/:report_id]',
											'constraints' => array(
													'report_id' 	=> '[0-9]+',
											),
											'defaults' => array(
												'controller' => 'Widget',
												'action'     => 'list',
											),
										),	
									),	
									'sortupdate'  => array(
										'type' => 'literal',
										'options' => array(
											'route' => '/sortupdate',
											'defaults' => array(
												'controller' => 'Widget',
												'action'     => 'sortupdate',
											),
										),	
									),
									'load'  => array(
										'type' => 'segment',
										'options' => array(
											'route' => '/load[/:widget_id]',
											'constraints' => array(
													'widget_id' 	=> '[0-9]+',
											),
											'defaults' => array(
												'controller' => 'Widget',
												'action'     => 'load',
											),
										),	
									),
								)
					),
				),
              ),
        ),
    ),
     'controllers' => array(
        'invokables' => array(
            'Admin'     => 'Admin\Controller\AdminController',
            'Agency'    => 'Admin\Controller\AgencyController',
            'Settings'  => 'Admin\Controller\SettingsController',
            'AdminUser'      => 'Admin\Controller\UserController',
        ),
    ),
 	 'service_manager' => array(
        'aliases' => array(
            'admin_zend_db_adapter' => 'Zend\Db\Adapter\Adapter',
        ),
    ),
    'view_manager' => array( 
        'template_path_stack' => array(__DIR__ . '/../view' ),
		'template_map' => array(
			'create-report'			  => __DIR__ . '/../view/admin/client/create-report.phtml',
		  //'reports'  				  => __DIR__ . '/../view/admin/client/reports.phtml',
			'account'  				  => __DIR__ . '/../view/admin/client/account.phtml',
			'edit'  				  => __DIR__ . '/../view/admin/client/edit.phtml',
			'changepwd'  			  => __DIR__ . '/../view/admin/client/changepwd.phtml',
			'admin-account'  		  => __DIR__ . '/../view/admin/settings/account.phtml',
			'admin-edit'  			  => __DIR__ . '/../view/admin/settings/edit.phtml',
			'admin-changepwd'  		  => __DIR__ . '/../view/admin/settings/changepwd.phtml',
			'agency-account'		  => __DIR__ . '/../view/admin/agency/account.phtml',
			'agency-edit'  			  => __DIR__ . '/../view/admin/agency/edit.phtml',
			'agency-changepwd'  	  => __DIR__ . '/../view/admin/agency/changepwd.phtml',
			'agency-clients'		  => __DIR__ . '/../view/admin/agency/clients.phtml',
			

		),
    ),
	 'navigation' => array(
		'admin' => array(
             array(
                 'label' => 'Agency',
                 'route' => 'admin/agency'
             ),
			 array(
                 'label' => 'Clients',
                 'route' => 'admin/client'
             ), 
			 array(
                 'label' => 'Reports',
                 'route' => 'admin/report'
             ), 
			 
         ),
		)
);
