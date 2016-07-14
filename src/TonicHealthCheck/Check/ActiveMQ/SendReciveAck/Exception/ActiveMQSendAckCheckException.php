<?php

namespace TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception;

/**
 * Class ActiveMQSendAckCheckException.
 */
class ActiveMQSendAckCheckException extends ActiveMQSendReciveAckCheckException
{
    const EXCEPTION_NAME = 'ActiveMQSendReciveAckCheck';

    const CODE_CAN_NOT_SENT = 4002;
    const TEXT_CAN_NOT_SENT = 'can\'t send message destination:%s body:%s error:%s';

    /**
     * @param string     $destination
     * @param string     $body
     * @param \Exception $e
     *
     * @return self
     */
    public static function canNotSent($destination, $body, \Exception $e)
    {
        return new self(sprintf(self::TEXT_CAN_NOT_SENT, $destination, $body, $e->getMessage()), self::CODE_CAN_NOT_SENT, $e);
    }
}
