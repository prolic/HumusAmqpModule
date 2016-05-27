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

$config = [
    'console' => [
        'router' => include __DIR__ . '/console.router.config.php'
    ],
    'controllers' => [
        'factories' => [
            Controller\ConsumerController::class => Controller\ConsumerControllerFactory::class,
            Controller\ExchangesController::class => Controller\ExchangesControllerFactory::class,
            Controller\ListController::class => Controller\ListControllerFactory::class,
            Controller\PurgeConsumerController::class => Controller\PurgeConsumerControllerFactory::class,
            Controller\GenSupervisordConfigController::class => Controller\GenSupervisordConfigControllerFactory::class,
            Controller\RpcServerController::class => Controller\RpcServerControllerFactory::class,
            Controller\SetupFabricController::class => Controller\SetupFabricControllerFactory::class,
            Controller\StdInProducerController::class => Controller\StdInProducerControllerFactory::class,
        ]
    ],
    'humus_amqp_module' => [
        'default_connection' => 'default',
        'plugin_managers' => [
            'callback' => [],
            'connection' => [
                'abstract_factories' => [
                    Service\ConnectionAbstractServiceFactory::class,
                ]
            ],
            'producer' => [
                'abstract_factories' => [
                    Service\ProducerAbstractServiceFactory::class,
                ]
            ],
            'consumer' => [
                'abstract_factories' => [
                    Service\ConsumerAbstractServiceFactory::class,
                ]
            ],
            'rpc_server' => [
                'abstract_factories' => [
                    Service\RpcServerAbstractServiceFactory::class,
                ]
            ],
            'rpc_client' => [
                'abstract_factories' => [
                    Service\RpcClientAbstractServiceFactory::class,
                ]
            ]
        ],
        'exchanges' => [],
        'queues' => [],
        'producers' => [],
        'consumers' => [],
        'rpc_clients' => [],
        'rpc_servers' => [],
        'connections' => []
    ],
    'humus_supervisor_module' => [
        'humus-amqp-supervisor' => [
            'host' => 'localhost',
            'port' => 19005,
            'username' => 'user',
            'password' => '123',
            'supervisord' => [
                'config' => [
                    'logfile' => getcwd() . '/data/supervisord/logs/supervisord.log',
                    'pidfile' => getcwd() . '/data/supervisord/supervisord.pid',
                    'childlogdir' => getcwd() . '/data/supervisord/logs',
                    'user' => get_current_user(),
                ],
                'rpcinterface' => [
                    'supervisor.rpcinterface_factory' => 'supervisor.rpcinterface:make_main_rpcinterface'
                ],
                'supervisorctl' => [
                    'serverurl' => getcwd() . '/data/supervisord/supervisor.sock'
                ],
                'unix_http_server' => [
                    'file' => getcwd() . '/data/supervisord/supervisor.sock',
                    'chmod' => '0700'
                ],
                'inet_http_server' => [
                    'port' => 19005,
                    'username' => 'user',
                    'password' => '123'
                ]
            ]
        ]
    ],
    'service_manager' => [
        
    ],
];

if (class_exists('HumusSupervisorModule\Module')) {
    $config['console']['router']['routes']['humus_amqp_module-gen-supervisord-config'] = [
        'options' => [
            'route' => 'humus amqp gen-supervisord-config [<path>]',
            'defaults' => [
                'controller' => Controller\GenSupervisordConfigController::class,
                'action' => 'index'
            ]
        ]
    ];
}

return $config;
