Humus AMQP Module
=================

[![Build Status](https://travis-ci.org/prolic/HumusAmqpModule.svg)](https://travis-ci.org/prolic/HumusAmqpModule)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/?branch=master)
[![Dependency Status](http://www.versioneye.com/user/projects/53c69599a54f97bc3c00000a/badge.svg?style=flat)](http://www.versioneye.com/user/projects/53c69599a54f97bc3c00000a)

Humus AMQP Module is a Module for Zend Framework 2 based on php-amqplib.

About
-----

The Humus AMQP Module incorporates messaging in your zf2 application via [RabbitMQ](http://www.rabbitmq.com/) using the [php-amqplib](http://github.com/videlalvaro/php-amqplib) library.

This module implements several messaging patterns as seen on the [Thumper](https://github.com/videlalvaro/Thumper) library.

A lot of ideas and even implementation details came from the [RabbitMqBundle](https://github.com/videlalvaro/RabbitMqBundle), special thanks to [Alvaro Videla](https://github.com/videlalvaro) and the contributors of this project.

Demo
----

You can install the [Demo-Module](https://github.com/prolic/HumusAmqpDemoModule) additionally. That should help you getting started. Just remove the demo module, when you're ready to go!

Dependencies
------------

 - PHP 5.3.3
 - [php-amqplib](https://github.com/videlalvaro/php-amqplib)
 - [HumusSupervisorModule](https://github.com/prolic/HumusSupervisorModule) (optional)

Installation
------------

 1.  Add `"prolic/humus-amqp-module": "dev-master"` to your `composer.json`
 2.  Run `php composer.phar install`
 3.  Enable the module in your `config/application.config.php` by adding `HumusAmqpModule` to `modules`

Configuration
-------------

@todo

Usage
-----

Sending SIGTERM or SIGINT stops a running consumer.

Controller-Usage
----------------

@todo

Cli-Usage
---------

see: php public/index.php

TODOS:
------

 - Write more tests
 - Move factories to AbstractServiceFactory
 - Add documentation
 - Add data collectors
 - Restart consumers
 - Add logged channel
