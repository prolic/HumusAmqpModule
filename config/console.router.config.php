<?php

return array(
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
);
