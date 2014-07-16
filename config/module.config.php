<?php

namespace HumusAmqpModule;

return array(
    'console' => array(
        'router' => array(
            'routes' => array(
                'humus_amqp_module-setup-fabric' => array(
                    'options' => array(
                        'route'    => 'amqp  setup-fabric [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\SetupFabric',
                        )
                    )
                ),
                'humus_amqp_module-consumer' => array(
                    'options' => array(
                        'route'    => 'amqp  consumer <name> [<amount>] [--route=] [--memory_limit=] [--without-signals|-w] [--debug|-d]',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\Consumer',
                        )
                    )
                ),
                'humus_amqp_module-list' => array(
                    'options' => array(
                        'route'    => 'amqp  list (consumers|multiple_consumers|anon_consumers|producers|rpc_clients|rpc_servers|connections):type',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\List',
                        )
                    )
                ),
                'humus_amqp_module-exchanges' => array(
                    'options' => array(
                        'route'    => 'amqp  list-exchanges',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\Exchanges',
                        )
                    )
                ),
                'humus_amqp_module-supervisor' => array(
                    'options' => array(
                        'route'    => 'amqp  supervisor (start|stop|processlist|pid|version|api|islocal):action',
                        'defaults' => array(
                            'controller' => __NAMESPACE__ . '\\Controller\\Supervisor',
                        )
                    )
                )
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . '\\Controller\\Consumer' => __NAMESPACE__ . '\\Controller\\ConsumerController',
            __NAMESPACE__ . '\\Controller\\List' => __NAMESPACE__ . '\\Controller\\ListController',
            __NAMESPACE__ . '\\Controller\\Exchanges' => __NAMESPACE__ . '\\Controller\\ExchangesController',
        ),
        'factories' => array(
            __NAMESPACE__ . '\\Controller\\SetupFabric' => __NAMESPACE__ . '\\Service\\Controller\\SetupFabricFactory',
            __NAMESPACE__ . '\\Controller\\Supervisor' => __NAMESPACE__ . '\\Service\\Controller\\SupervisorFactory'
        )
    ),
    'humus_supervisor_module' => array(
        'humus-amqp-supervisor' => array(
        )
    ),
    'humus_amqp_module' => array(
        'classes' => array(
            'connection' => 'PhpAmqpLib\Connection\AMQPConnection',
            'lazy_connection' => 'PhpAmqpLib\Connection\AMQPLazyConnection',
            'producer' => __NAMESPACE__ . '\Amqp\Producer',
            'consumer' => __NAMESPACE__ . '\Amqp\Consumer',
            'multi_consumer' => __NAMESPACE__ . '\Amqp\MultipleConsumer',
            'anon_consumer' => __NAMESPACE__ . '\Amqp\AnonConsumer',
            'rpc_client' => __NAMESPACE__ . '\Amqp\RpcClient',
            'rpc_server' => __NAMESPACE__ . '\Amqp\RpcServer',
            'logged_channel' => __NAMESPACE__ . '\Amqp\AMQPLoggedChannel',
            'parts_holder' => __NAMESPACE__ . '\Amqp\PartsHolder',
            'fallback' => __NAMESPACE__ . '\Amqp\Fallback'
        )
    ),
    'service_manager' => array(
        'factories' => array(
            __NAMESPACE__ . '\\Amqp\PartsHolder' => 'HumusAmqpModule\Service\PartsHolderFactory'
        )
    )
);
