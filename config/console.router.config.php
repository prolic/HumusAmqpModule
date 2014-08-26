<?php

namespace HumusAmqpModule;

return array(
    'routes' => array(
        'humus_amqp_module-setup-fabric' => array(
            'options' => array(
                'route'    => 'humus amqp setup-fabric',
                'defaults' => array(
                    'controller' => __NAMESPACE__ . '\\Controller\\SetupFabric',
                )
            )
        ),
        'humus_amqp_module-consumer' => array(
            'options' => array(
                'route'    => 'humus amqp consumer <name> [<amount>]',
                'defaults' => array(
                    'controller' => __NAMESPACE__ . '\\Controller\\Consumer',
                )
            )
        ),
        'humus_amqp_module-list' => array(
            'options' => array(
                'route'    => 'humus amqp list (consumers|producers|rpc-clients|rpc-servers|connections):type',
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
                'route'    => 'humus amqp stdin-producer <name> [--route=] <msg>',
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
                'route' => 'humus amqp rpc-server <name> [<amount>]',
                'defaults' => array(
                    'controller' => __NAMESPACE__ . '\\Controller\\RpcServer',
                )
            )
        ),
    )
);
