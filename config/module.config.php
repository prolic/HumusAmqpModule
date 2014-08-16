<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace HumusAmqpModule;

$config = array(
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
            __NAMESPACE__ . '\\Controller\\GenSupervisordConfig' => __NAMESPACE__ . '\\Controller\GenSupervisordConfigController'
        ),
        'factories' => array(
            __NAMESPACE__ . '\\Controller\\Consumer' => __NAMESPACE__ . '\\Service\\Controller\\ConsumerFactory',
            __NAMESPACE__ . '\\Controller\\AnonConsumer' => __NAMESPACE__ . '\\Service\\Controller\\AnonConsumerFactory',
            __NAMESPACE__ . '\\Controller\\MultipleConsumer' => __NAMESPACE__ . '\\Service\\Controller\\MultipleConsumerFactory',
            __NAMESPACE__ . '\\Controller\\PurgeConsumer' => __NAMESPACE__ . '\\Service\\Controller\\PurgeConsumerFactory',
            __NAMESPACE__ . '\\Controller\\PurgeAnonConsumer' => __NAMESPACE__ . '\\Service\\Controller\\PurgeAnonConsumerFactory',
            __NAMESPACE__ . '\\Controller\\PurgeMultipleConsumer' => __NAMESPACE__ . '\\Service\\Controller\\PurgeMultipleConsumerFactory',
            __NAMESPACE__ . '\\Controller\\RpcServer' => __NAMESPACE__ . '\\Service\\Controller\\RpcServerFactory',
            __NAMESPACE__ . '\\Controller\\SetupFabric' => __NAMESPACE__ . '\\Service\\Controller\\SetupFabricFactory',
            __NAMESPACE__ . '\\Controller\\StdInProducer' => __NAMESPACE__ . '\\Service\\Controller\\StdInProducerFactory',
        )
    ),
    'humus_amqp_module' => array(
        'classes' => array(
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
        'plugin_managers' => array(),
        'callbacks' => array(),
        'producers' => array(),
        'consumers' => array(),
        'anon_consumers' => array(),
        'multiple_consumers' => array(),
        'connections' => array()
    ),
    'humus_supervisor_module' => array(
        'humus-amqp-supervisor' => array(
            'host' => 'localhost',
            'port' => 19005,
            'username' => 'user',
            'password' => '123',
            'supervisord' => array(
                'config' => array(
                    'logfile' => getcwd() . '/data/supervisord/logs/supervisord.log',
                    'pidfile' => getcwd() . '/data/supervisord/supervisord.pid',
                    'childlogdir' => getcwd() . '/data/supervisord/logs',
                    'user' => 'root',
                ),
                'rpcinterface' => array(
                    'supervisor.rpcinterface_factory' => 'supervisor.rpcinterface:make_main_rpcinterface'
                ),
                'supervisorctl' => array(
                    'serverurl' => getcwd() . '/data/supervisord/supervisor.sock'
                ),
                'unix_http_server' => array(
                    'file' => getcwd() . '/data/supervisord/supervisor.sock',
                    'chmod' => '0700'
                ),
                'inet_http_server' => array(
                    'port' => 19005,
                    'username' => 'user',
                    'password' => '123'
                )
            )
        )
    ),
    'service_manager' => array(
        'factories' => array(
            __NAMESPACE__ . '\\Amqp\\PartsHolder' => 'HumusAmqpModule\Service\PartsHolderFactory'
        ),
    )
);

if (class_exists('HumusSupervisorModule\\Module')) {
    $config['console']['router']['routes']['humus_amqp_module-gen-supervisord-config'] = array(
        'options' => array(
            'route' => 'humus amqp gen-supervisord-config [<path>]',
            'defaults' => array(
                'controller' => __NAMESPACE__ . '\\Controller\GenSupervisordConfig',
                'action' => 'index'
            )
        )
    );
}

return $config;
