<?php
// Config/config.php

declare(strict_types=1);

return [
    'name' => 'Lodge Subscription Manager',
    'description' => 'Manages Lodge subscriptions with Stripe integration',
    'version' => '1.0.1',
    'author' => 'Adam Camp',

    // Add CSS and JS files
    'css' => [
        'plugins/LodgeSubscriptionBundle/Assets/css/lodge-subscription.css',
    ],
    'js' => [
        'plugins/LodgeSubscriptionBundle/Assets/js/lodge-subscription.js',
    ],

    'routes' => [
        'main' => [
            'mautic_subscription_rates' => [
                'path' => '/lodge/rates/{page}',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::indexAction',
                'defaults' => [
                    'page' => 1
                ]
            ],
            'mautic_subscription_rate_new' => [
                'path' => '/lodge/rate/new',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::newAction'
            ],
            'mautic_subscription_rate_edit' => [
                'path' => '/lodge/rate/{id}/edit',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::editAction'
            ],
            'mautic_subscription_rate_delete' => [
                'path' => '/lodge/rate/{id}/delete',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::deleteAction'
            ],
            'mautic_subscription_rate_get' => [
                'path' => '/lodge/rate/{year}/get',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::getRateAction'
            ],
            'mautic_subscription_payment_form' => [
                'path' => '/lodge/subscription/payment/{contactId}',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\SubscriptionController::class.'::paymentFormAction'
            ],
            'mautic_subscription_record_payment' => [
                'path' => '/lodge/subscription/payment/record',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\SubscriptionController::class.'::recordPaymentAction',
                'method' => 'POST'
            ],
            'mautic_subscription_dashboard' => [
                'path' => '/lodge/dashboard/{year}',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\ReportController::class.'::dashboardAction',
                'defaults' => [
                    'year' => null
                ]
            ],
            'mautic_subscription_export' => [
                'path' => '/lodge/export',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\ReportController::class.'::exportAction'
            ]
        ],
        'public' => [
            'mautic_subscription_webhook' => [
                'path' => '/lodge/webhook/stripe',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\WebhookController::class.'::handleAction',
                'method' => 'POST'
            ],
            'mautic_subscription_webhook_test' => [
                'path' => '/lodge/webhook/stripe/test',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\WebhookController::class.'::testAction'
            ]
        ],
        'api' => [
            'mautic_subscription_dashboard_api' => [
                'path' => '/lodge/api/dashboard/{year}',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\ReportController::class.'::dashboardApiAction',
                'defaults' => [
                    'year' => null
                ]
            ],
            'mautic_subscription_rates_api' => [
                'path' => '/lodge/api/rates/{page}',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::indexAction',
                'defaults' => [
                    'page' => 1
                ]
            ],
            'mautic_subscription_rate_get_api' => [
                'path' => '/lodge/api/rate/{year}/get',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\RateController::class.'::getRateAction'
            ],
            'mautic_subscription_export_api' => [
                'path' => '/lodge/api/export',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\ReportController::class.'::exportAction'
            ],
            'mautic_subscription_record_payment_api' => [
                'path' => '/lodge/api/payment/record',
                'controller' => \MauticPlugin\LodgeSubscriptionBundle\Controller\SubscriptionController::class.'::recordPaymentAction',
                'method' => 'POST'
            ]
        ]
    ],

    'menu' => [
        'main' => [
            'priority' => 70,
            'items' => [
                'lodge.subscription' => [
                    'label' => 'Lodge Subscriptions',
                    'iconClass' => 'fa-money',
                    'route' => 'mautic_subscription_dashboard',
                    'checks' => [
                        'integration' => [
                            'LodgeSubscription' => [
                                'enabled' => true
                            ]
                        ]
                    ],
                    'items' => [
                        'lodge.subscription.dashboard' => [
                            'label' => 'Dashboard',
                            'route' => 'mautic_subscription_dashboard',
                        ],
                        'lodge.subscription.rates' => [
                            'label' => 'Subscription Rates',
                            'route' => 'mautic_subscription_rates',
                        ],
                        'lodge.subscription.export' => [
                            'label' => 'Export Payments',
                            'route' => 'mautic_subscription_export',
                        ]
                    ]
                ],
                'mautic.lodge.subscription' => [
                    'route' => 'mautic_subscription_dashboard_api',
                    'access' => ['lodge:subscriptions:view'],
                    'parent' => 'mautic.core.channels',
                    'priority' => 60
                ]
            ]
        ]
    ],

    'services' => [
        'models' => [
            'lodge_subscription.model.subscription' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel::class,
                'arguments' => [
                    'doctrine.orm.entity_manager',
                    'mautic.lead.model.lead',
                    'mautic.user.model.user',
                    'monolog.logger.mautic'
                ]
            ]
        ],
        'helpers' => [
            'lodge_subscription.helper.subscription' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper::class,
                'arguments' => [
                    'doctrine.orm.entity_manager'
                ]
            ]
        ],
        'others' => [
            'lodge_subscription.services.stripe' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Services\StripeService::class,
                'arguments' => [
                    'mautic.helper.core_parameters',
                    'monolog.logger.mautic'
                ]
            ]
        ],
        'forms' => [
            'lodge_subscription.form.type.subscription_rate' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Form\Type\SubscriptionRateType::class,
                'tag' => 'form.type'
            ]
        ]
    ],

    'parameters' => [
        'lodge_subscription_currency' => 'GBP',
        'lodge_subscription_reminder_template' => null
    ],

    'permissions' => [
        'lodge:subscriptions' => [
            'view' => [
                'label' => 'View Subscription Rates',
                'level' => 'ROLE_USER'
            ],
            'create' => [
                'label' => 'Create Subscription Rates',
                'level' => 'ROLE_ADMIN'
            ],
            'edit' => [
                'label' => 'Edit Subscription Rates',
                'level' => 'ROLE_ADMIN'
            ],
            'delete' => [
                'label' => 'Delete Subscription Rates',
                'level' => 'ROLE_ADMIN'
            ],
            'payments' => [
                'label' => 'Process Payments',
                'level' => 'ROLE_ADMIN'
            ]
        ]
    ],

    'tables' => [
        'lodge_subscription_rates' => [
            'name' => 'lodge_subscription_rates',
            'columns' => [
                'id' => [
                    'type' => 'integer',
                    'primary' => true,
                    'autoincrement' => true,
                ],
                'year' => [
                    'type' => 'integer',
                ],
                'amount' => [
                    'type' => 'decimal',
                    'precision' => 10,
                    'scale' => 2,
                ],
                'description' => [
                    'type' => 'string',
                    'length' => 255,
                    'nullable' => true,
                ],
                'date_added' => [
                    'type' => 'datetime',
                ],
                'date_modified' => [
                    'type' => 'datetime',
                    'nullable' => true,
                ]
            ],
            'indexes' => [
                'year_idx' => [
                    'columns' => ['year'],
                    'unique' => true
                ]
            ]
        ],
        'lodge_payments' => [
            'name' => 'lodge_payments',
            'columns' => [
                'id' => [
                    'type' => 'integer',
                    'primary' => true,
                    'autoincrement' => true,
                ],
                'contact_id' => [
                    'type' => 'integer',
                ],
                'amount' => [
                    'type' => 'decimal',
                    'precision' => 10,
                    'scale' => 2,
                ],
                'year' => [
                    'type' => 'integer',
                ],
                'stripe_payment_id' => [
                    'type' => 'string',
                    'length' => 255,
                    'nullable' => true,
                ],
                'payment_method' => [
                    'type' => 'string',
                    'length' => 50,
                ],
                'status' => [
                    'type' => 'string',
                    'length' => 50,
                ],
                'applied_to_current' => [
                    'type' => 'decimal',
                    'precision' => 10,
                    'scale' => 2,
                ],
                'applied_to_arrears' => [
                    'type' => 'decimal',
                    'precision' => 10,
                    'scale' => 2,
                ],
                'notes' => [
                    'type' => 'text',
                    'nullable' => true,
                ],
                'received_by' => [
                    'type' => 'string',
                    'length' => 255,
                    'nullable' => true,
                ],
                'date_added' => [
                    'type' => 'datetime',
                ],
                'date_modified' => [
                    'type' => 'datetime',
                    'nullable' => true,
                ]
            ],
            'indexes' => [
                'contact_idx' => [
                    'columns' => ['contact_id']
                ],
                'stripe_payment_idx' => [
                    'columns' => ['stripe_payment_id'],
                    'unique' => true,
                    'nullable' => true
                ]
            ]
        ]
    ]
];