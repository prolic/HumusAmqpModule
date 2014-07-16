Humus AMQP Module
=================

[![Build Status](https://travis-ci.org/prolic/HumusAmqpModule.svg)](https://travis-ci.org/prolic/HumusAmqpModule)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/prolic/HumusAmqpModule/?branch=master)
[![Dependency Status](http://www.versioneye.com/user/projects/53c69599a54f97bc3c00000a/badge.svg?style=flat)](http://www.versioneye.com/user/projects/53c69599a54f97bc3c00000a)

Humus AMQP Module is a Module for Zend Framework 2 based on php-amqplib.

Dependencies
------------

 - PHP 5.3.3
 - [php-amqplib](https://github.com/videlalvaro/php-amqplib)

Installation
------------

 1.  Add `"prolic/humus-amqp-module": "dev-master"` to your `composer.json`
 2.  Run `php composer.phar install`
 3.  Enable the module in your `config/application.config.php` by adding `HumusAmqpModule` to `modules`
