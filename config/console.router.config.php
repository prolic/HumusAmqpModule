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

return [
    'routes' => [
        'humus_amqp_module-setup-fabric' => [
            'options' => [
                'route'    => 'humus amqp setup-fabric',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\SetupFabric',
                ]
            ]
        ],
        'humus_amqp_module-consumer' => [
            'options' => [
                'route'    => 'humus amqp consumer <name> [<amount>] [--without-signals|-w]',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\Consumer',
                ]
            ]
        ],
        'humus_amqp_module-list' => [
            'options' => [
                'route'    => 'humus amqp list (consumers|producers|rpc-clients|rpc-servers|connections):type',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\List',
                ]
            ]
        ],
        'humus_amqp_module-exchanges' => [
            'options' => [
                'route'    => 'humus amqp list-exchanges',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\Exchanges',
                ]
            ]
        ],
        'humus_amqp_module-stdin-producer' => [
            'options' => [
                'route'    => 'humus amqp stdin-producer <name> [--route=] <msg>',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\StdInProducer',
                ]
            ]
        ],
        'humus_amqp_module-purge-consumer-queue' => [
            'options' => [
                'route' => 'humus amqp purge-consumer <consumer-name> [--no-confirmation]',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\PurgeConsumer',
                ]
            ]
        ],
        'humus_amqp_module-rpc-server' => [
            'options' => [
                'route' => 'humus amqp rpc-server <name> [<amount>] [--without-signals|-w]',
                'defaults' => [
                    'controller' => __NAMESPACE__ . '\\Controller\\RpcServer',
                ]
            ]
        ],
    ]
];
