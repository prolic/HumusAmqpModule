<?php

namespace HumusAmqpModule\Listener;

use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\Log\LoggerAwareInterface;
use Zend\Log\LoggerAwareTrait;
use Zend\Log\LoggerInterface;

/**
 * Class LoggerListener
 * @package HumusAmqpModule\Listener
 */
class LoggerListener implements LoggerAwareInterface, ListenerAggregateInterface
{
    use ListenerAggregateTrait;
    use LoggerAwareTrait;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->setLogger($logger);
    }

    /**
     * @param EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach('ack', [$this, 'onAck']);
        $this->listeners[] = $events->attach('deliveryException', [$this, 'onDeliveryException']);
        $this->listeners[] = $events->attach('flushDeferredException', [$this, 'onFlushDeferredException']);
    }

    /**
     * @param EventInterface $e
     */
    public function onAck(EventInterface $e)
    {
        $delta = $e->getParam('timestampLastMessage') - $e->getParam('timestampLastAck');
        $unacked = $e->getParam('countMessagesUnacked');
        $this->getLogger()->debug(sprintf(
            'Acknowledged %d messages at %.0f msg/s',
            $unacked,
            $delta ? $unacked / $delta : 0
        ));
    }

    /**
     * @param EventInterface $e
     */
    public function onDeliveryException(EventInterface $e)
    {
        /** @var \Exception $exception */
        $exception = $e->getParam('exception');
        $message = $exception->getMessage();
        $this->getLogger()->err(sprintf('Exception during handleDelivery: %s', $message));
    }

    /**
     * @param EventInterface $e
     */
    public function onFlushDeferredException(EventInterface $e)
    {
        /** @var \Exception $exception */
        $exception = $e->getParam('exception');
        $this->getLogger()->err(sprintf('Exception during flushDeferred: %s', $exception->getMessage()));
    }
}
