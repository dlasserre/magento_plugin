<?php

namespace Thunderstone\Order\Controller\Order;



use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Create implements ActionInterface
{
    protected $jsonFactory;

    public function __construct(
        $context,
        JsonFactory $jsonFactory
    )
    {
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {

        return $this->jsonFactory->create(['ok']);
    }
}
