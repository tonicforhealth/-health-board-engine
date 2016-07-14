<?php

namespace TonicHealthCheck\Check\ActiveMQ\SendReciveAck\Exception;

/**
 * Class ActiveMQReceiveAckCheckException.
 */
class ActiveMQReceiveAckCheckException extends ActiveMQSendReciveAckCheckException
{
    const EXCEPTION_NAME = 'ActiveMQSendReceiveAckCheck';

    const CODE_CAN_NOT_RECIVE = 4003;
    const TEXT_CAN_NOT_RECIVE = 'can\'t recive message destination:%s error:%s';

    /**
     * @param string     $destination
     * @param \Exception $e
     *
     * @return ActiveMQReceiveAckCheckException
     */
    public static function canNotReceive($destination, \Exception $e)
    {
        return new self(sprintf(self::TEXT_CAN_NOT_RECIVE, $destination, $e->getMessage()), self::CODE_CAN_NOT_RECIVE, $e);
    }
}
