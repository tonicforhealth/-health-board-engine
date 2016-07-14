<?php

namespace TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception;

use TonicHealthCheck\Check\ActiveMQ\ActiveMQCheckException;

/**
 * Class ActiveMQSendReciveAckCheckException.
 */
class ActiveMQSendReciveAckCheckException extends ActiveMQCheckException
{
    const EXCEPTION_NAME = 'ActiveMQSendReciveAckCheck';
}
