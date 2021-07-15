<?php

namespace Thunderstone\Order\Api;

use Thunderstone\Order\Model\Order;

interface OrderControllerInterface
{
    /**
     * POST for Order api
     * @param Order $order
     * @return array
     */
    public function postOrder(Order $order): array;
}