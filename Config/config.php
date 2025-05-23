<?php
// Config/config.php

return [
    'name' => 'Lodge Subscription Manager',
    'description' => 'Manages Lodge subscriptions with Stripe integration',
    'version' => '1.0.0',
    'author' => 'Adam Camp',

    // Add CSS files
    'onLoadCallback' => 'loadAssets',
    'css' => [
        'plugins/LodgeSubscriptionBundle/Assets/css/lodge-subscription.css',
    ],

    'routes' => [
        'main' => [
            'mautic_subscription_rates' => [
                'path' => '/lodge/rates/{page}',
                'controller' => 'LodgeSubscriptionBundle:Rate:index',
                'defaults' => [
                    'page' => 1
                ]
            ],
            'mautic_subscription_rate_new' => [
                'path' => '/lodge/rate/new',
                'controller' => 'LodgeSubscriptionBundle:Rate:new'
            ],
            'mautic_subscription_rate_edit' => [
                'path' => '/lodge/rate/{id}/edit',
                'controller' => 'LodgeSubscriptionBundle:Rate:edit'
            ],
            'mautic_subscription_rate_delete' => [
                'path' => '/lodge/rate/{id}/delete',
                'controller' => 'LodgeSubscriptionBundle:Rate:delete'
            ],
            'mautic_subscription_rate_get' => [
                'path' => '/lodge/rate/{year}/get',
                'controller' => 'LodgeSubscriptionBundle:Rate:getRate'
            ],
            'mautic_subscription_payment_form' => [
                'path' => '/lodge/subscription/payment/{contactId}',
                'controller' => 'LodgeSubscriptionBundle:Subscription:paymentForm'
            ],
            'mautic_subscription_record_payment' => [
                'path' => '/lodge/subscription/payment/record',
                'controller' => 'LodgeSubscriptionBundle:Subscription:recordPayment',
                'method' => 'POST'
            ],
            'mautic_subscription_dashboard' => [
                'path' => '/lodge/dashboard/{year}',
                'controller' => 'LodgeSubscriptionBundle:Report:dashboard',
                'defaults' => [
                    'year' => null
                ]
            ],
            'mautic_subscription_export' => [
                'path' => '/lodge/export',
                'controller' => 'LodgeSubscriptionBundle:Report:export'
            ]
        ],
        'api' => [
            'mautic_subscription_webhook' => [
                'path' => '/lodge/webhook/stripe',
                'controller' => 'LodgeSubscriptionBundle:Webhook:handle',
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
                ]
            ]
        ]
    ],

    'services' => [
        'events' => [
            'mautic.lodge.subscriber.token' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\EventListener\TokenSubscriber::class,
                'arguments' => [
                    'mautic.lodge.service.stripe'
                ]
            ],
            'mautic.lodge.subscriber.builder' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\EventListener\BuilderSubscriber::class,
                'arguments' => []
            ]
        ],
        'forms' => [
            'mautic.lodge.form.type.config' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Form\Type\ConfigType::class,
                'arguments' => [
                    'mautic.email.model.email'
                ]
            ]
        ],
        'models' => [
            'mautic.lodge.model.subscription' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Model\SubscriptionModel::class,
                'arguments' => [
                    'doctrine.orm.entity_manager'
                ]
            ]
        ],
        'other' => [
            'mautic.lodge.service.stripe' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Services\StripeService::class,
                'arguments' => [
                    'mautic.helper.integration',
                    'router',
                    'mautic.lodge.helper.subscription'
                ]
            ],
            'mautic.lodge.helper.subscription' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Helper\SubscriptionHelper::class,
                'arguments' => [
                    'mautic.lead.model.lead',
                    'doctrine.orm.entity_manager',
                    'mautic.user.model.user'
                ]
            ]
        ],
        'integrations' => [
            'mautic.integration.lodge' => [
                'class' => MauticPlugin\LodgeSubscriptionBundle\Integration\LodgeSubscriptionIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'request_stack',
                    'router',
                    'translator',
                    'monolog.logger.mautic',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
        'commands' => [
            'mautic.lodge.command.yearend' => [
                'class' => \MauticPlugin\LodgeSubscriptionBundle\Command\YearEndProcessCommand::class,
                'arguments' => [
                    'mautic.lodge.helper.subscription',
                    'mautic.lead.model.field',
                    'mautic.email.model.email',
                    'doctrine.orm.entity_manager'
                ]
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