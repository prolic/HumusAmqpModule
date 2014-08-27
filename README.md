Humus AMQP Module
=================

[![Build Status](https://travis-ci.org/prolic/HumusAmqpModule.svg)](https://travis-ci.org/prolic/HumusAmqpModule)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/?branch=master)
[![License](https://poser.pugx.org/prolic/humus-amqp-module/license.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Latest Stable Version](https://poser.pugx.org/prolic/humus-amqp-module/v/stable.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Latest Unstable Version](https://poser.pugx.org/prolic/humus-amqp-module/v/unstable.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Total Downloads](https://poser.pugx.org/prolic/humus-amqp-module/downloads.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Dependency Status](http://www.versioneye.com/user/projects/53c69599a54f97bc3c00000a/badge.svg?style=flat)](http://www.versioneye.com/user/projects/53c69599a54f97bc3c00000a)

About
-----

The Humus AMQP Module incorporates messaging in your zf2 application via [RabbitMQ](http://www.rabbitmq.com/) using the [PHP AMQP Extension](https://github.com/pdezwart/php-amqp).

This module implements several messaging patterns as seen on the [Thumper](https://github.com/videlalvaro/Thumper) library.

A lot of ideas and even implementation details came from the [RabbitMQ Java Client](https://github.com/rabbitmq/rabbitmq-java-client) and the [RabbitMqBundle](https://github.com/videlalvaro/RabbitMqBundle), special thanks to [Alvaro Videla](https://github.com/videlalvaro) and the contributors of this project.

Documentation
-------------

Documentation is [available here](http://humus-amqp-module.readthedocs.org/), powered by [Read the docs](http://readthedocs.org/).

Demo
----

You can install the [Demo-Module](https://github.com/prolic/HumusAmqpDemoModule) additionally. That should help you getting started. Just remove the demo module, when you're ready to go!

Dependencies
------------

 - PHP 5.4.0
 - [ext-amqp](https://github.com/pdezwart/php-amqp)
 - [HumusSupervisorModule](https://github.com/prolic/HumusSupervisorModule) (optional)
