Humus AMQP Module
=================

[![Build Status](https://travis-ci.org/prolic/HumusAmqpModule.svg)](https://travis-ci.org/prolic/HumusAmqpModule)
[![Coverage Status](https://coveralls.io/repos/github/prolic/HumusAmqpModule/badge.svg?branch=master)](https://coveralls.io/github/prolic/HumusAmqpModule?branch=master)
[![Documentation Status](https://readthedocs.org/projects/humusamqp/badge/?version=latest)](https://readthedocs.org/projects/humusamqp/badge/?version=latest)
[![License](https://poser.pugx.org/prolic/humus-amqp-module/license.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Latest Stable Version](https://poser.pugx.org/prolic/humus-amqp-module/v/stable.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Latest Unstable Version](https://poser.pugx.org/prolic/humus-amqp-module/v/unstable.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Total Downloads](https://poser.pugx.org/prolic/humus-amqp-module/downloads.svg)](https://packagist.org/packages/prolic/humus-amqp-module)
[![Dependency Status](https://www.versioneye.com/php/prolic:humus-amqp-module/dev-master/badge.svg)](https://www.versioneye.com/php/prolic:humus-amqp-module)

## About

The Humus AMQP Module incorporates messaging in your zf2 application via RabbitMQ using [HumusAmqp](https://github.com/prolic/HumusAmqp>),
a PHP 7 AMQP libray supporting multiple drivers and providing full-featured Consumer, Producer, and JSON-RPC Client / Server implementations.

The JSON-RPC part implements JSON-RPC 2.0 Specification.

Current supported drivers are: php-amqp and PhpAmqpLib.

If you want to use it without Zend Framework, use [HumusAmqp](https://github.com/prolic/HumusAmqp/) without this module.

Documentation can be found here: [humusamqp.readthedocs.io](https://humusamqp.readthedocs.io/).

## Installation

You can install prolic/humus-amqp-module via composer by adding "prolic/humus-amqp": "^1.0" as requirement to your composer.json.

You can then enable the module in your config/application.config.php by adding 'HumusAmqpModule' to the 'modules' section.

## Support

- File issues at [https://github.com/prolic/HumusAmqp/issues](https://github.com/prolic/HumusAmqpDemoModule/issues).
- Say hello in the [HumusAmqp gitter](https://gitter.im/prolic/HumusAmqp) chat.

## Contribute

Please feel free to fork and extend existing or add new plugins and send a pull request with your changes!
To establish a consistent code quality, please provide unit tests for all your changes and may adapt the documentation.

## License

Released under the [MIT](LICENSE.txt).