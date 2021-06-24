<?php

namespace Thunderstone\Order\Model;

class Order
{
    /**
     * {@inheritdoc}
     */
    public function getOrder($orderId)
    {
        return 'Here is the order id: '.$orderId;
    }
}