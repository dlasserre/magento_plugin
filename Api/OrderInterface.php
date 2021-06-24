<?php

namespace Thunderstone\Order\Api;

interface OrderInterface
{
    /**
     * GET for Order api
     * @param string $orderId
     * @return string
     */

    public function getOrder($orderId);
}