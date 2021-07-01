<?php

namespace Thunderstone\Order\Api;

use Thunderstone\Order\Model\Order;

interface OrderControllerInterface
{
    /**
     * GET for Order api
     * @param string $orderId
     * @return string
     */

    public function getOrder(string $orderId): string;

    /**
     * POST for Order api
     * @param Order $order
     * @return array
     */
    public function postOrder(Order $order): array;
}