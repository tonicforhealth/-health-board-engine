<?php

namespace TonicHealthCheck\Component;

use Exception;

/**
 * Class ComponentException.
 */
class ComponentException extends Exception
{
    const CODE_CAN_NOT_CREATE_RESOURCE = 1101;
    const TEXT_CAN_NOT_CREATE_RESOURCE = 'ComponentManager Can\'t create resource error:%s';

    /**
     * @param string $errorMsg
     *
     * @return ComponentException
     */
    public static function canNotCreateResource($errorMsg)
    {
        return new self(sprintf(self::TEXT_CAN_NOT_CREATE_RESOURCE, $errorMsg), self::CODE_CAN_NOT_CREATE_RESOURCE);
    }
}
