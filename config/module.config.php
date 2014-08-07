<?php

namespace HumusAmqpModule;

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'humus_amqp_module-setup-fabric' => array(
                    'options' => array(
                        'route'    => 'humus amqp setup-fabric [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\SetupFabric',
                        )
                    )
                ),
                'humus_amqp_module-consumer' => array(
                    'options' => array(
                        'route'    => 'humus amqp consumer <name> [<amount>] [--route=] [--memory_limit=] [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\Consumer',
                        )
                    )
                ),
                'humus_amqp_module-multiple-consumer' => array(
                    'options' => array(
                        'route'    => 'humus amqp multiple-consumer <name> [<amount>] [--route=] [--memory_limit=] [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\MultipleConsumer',
                        )
                    )
                ),
                'humus_amqp_module-anon-consumer' => array(
                    'options' => array(
                        'route'    => 'humus amqp anon-consumer <name> [<amount>] [--route=] [--memory_limit=] [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\AnonConsumer',
                        )
                    )
                ),
                'humus_amqp_module-list' => array(
                    'options' => array(
                        'route'    => 'humus amqp list (consumers|multiple-consumers|anon-consumers|producers|rpc-clients|rpc-servers|connections):type',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\List',
                        )
                    )
                ),
                'humus_amqp_module-exchanges' => array(
                    'options' => array(
                        'route'    => 'humus amqp list-exchanges',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\Exchanges',
                        )
                    )
                ),
                'humus_amqp_module-stdin-producer' => array(
                    'options' => array(
                        'route'    => 'humus amqp stdin-producer <name> [--route=] <msg> [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\StdInProducer',
                        )
                    )
                ),
                'humus_amqp_module-purge-consumer-queue' => array(
                    'options' => array(
                        'route' => 'humus amqp purge-consumer <consumer-name> [--no-confirmation]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\PurgeConsumer',
                        )
                    )
                ),
                'humus_amqp_module-purge-anon-consumer-queue' => array(
                    'options' => array(
                        'route' => 'humus amqp purge-anon-consumer <consumer-name> [--no-confirmation]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\PurgeAnonConsumer',
                        )
                    )
                ),
                'humus_amqp_module-purge-multiple-consumer-queue' => array(
                    'options' => array(
                        'route' => 'humus amqp purge-multiple-consumer <consumer-name> [--no-confirmation]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\PurgeMultipleConsumer',
                        )
                    )
                ),
                'humus_amqp_module-rpc-server' => array(
                    'options' => array(
                        'route' => 'humus amqp rpc-server <name> [<amount>] [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\RpcServer',
                        )
                    )
                ),
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . '\\Controller\\List' => __NAMESPACE__ . '\\Controller\\ListController',
            __NAMESPACE__ . '\\Controller\\Exchanges' => __NAMESPACE__ . '\\Controller\\ExchangesController',
            __NAMESPACE__ . '\\Controller\\StdInProducer' => __NAMESPACE__ . '\\Controller\\StdInProducerController',
            __NAMESPACE__ . '\\Controller\\RpcServer' => __NAMESPACE__ . '\\Controller\\RpcServerController'
        ),
        'factories' => array(
            __NAMESPACE__ . '\\Controller\\Consumer' => __NAMESPACE__ . '\\Service\\Controller\\ConsumerFactory',
            __NAMESPACE__ . '\\Controller\\AnonConsumer' => __NAMESPACE__ . '\\Service\\Controller\\AnonConsumerFactory',
            __NAMESPACE__ . '\\Controller\\MultipleConsumer' => __NAMESPACE__ . '\\Service\\Controller\\MultipleConsumerFactory',
            __NAMESPACE__ . '\\Controller\\PurgeConsumer' => __NAMESPACE__ . '\\Service\\Controller\\PurgeConsumerFactory',
            __NAMESPACE__ . '\\Controller\\PurgeAnonConsumer' => __NAMESPACE__ . '\\Service\\Controller\\PurgeAnonConsumerFactory',
            __NAMESPACE__ . '\\Controller\\PurgeMultipleConsumer' => __NAMESPACE__ . '\\Service\\Controller\\PurgeMultipleConsumerFactory',
            __NAMESPACE__ . '\\Controller\\SetupFabric' => __NAMESPACE__ . '\\Service\\Controller\\SetupFabricFactory',
        )
    ),
    'humus_amqp_module' => array(
        'classes' => array(
            'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
            'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
            'producer' => __NAMESPACE__ . '\Amqp\Producer',
            'consumer' => __NAMESPACE__ . '\Amqp\Consumer',
            'multiple_consumer' => __NAMESPACE__ . '\Amqp\MultipleConsumer',
            'anon_consumer' => __NAMESPACE__ . '\Amqp\AnonConsumer',
            'rpc_client' => __NAMESPACE__ . '\Amqp\RpcClient',
            'rpc_server' => __NAMESPACE__ . '\Amqp\RpcServer',
            'logged_channel' => __NAMESPACE__ . '\Amqp\AMQPLoggedChannel',
            'parts_holder' => __NAMESPACE__ . '\Amqp\PartsHolder',
            'fallback' => __NAMESPACE__ . '\Amqp\Fallback'
        ),
        'plugin_managers' => array()
    ),
    'humus_supervisor_module' => array(
        'humus-amqp-supervisor' => array(
            'host' => 'localhost',
            'port' => 19005,
            'username' => 'user',
            'password' => '123'
        )
    ),
    'service_manager' => array(
        'factories' => array(
            __NAMESPACE__ . '\\Amqp\PartsHolder' => 'HumusAmqpModule\Service\PartsHolderFactory'
        ),
    )
);
