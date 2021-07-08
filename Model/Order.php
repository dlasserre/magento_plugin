<?php


namespace Thunderstone\Order\Model;

class Order
{

    /**
     * @var \Thunderstone\Order\Model\Customer
     */
    private $customer;

    /**
     * @var \Thunderstone\Order\Model\Shipping
     */
    private $shipping;

    /**
     * @var \Thunderstone\Order\Model\Product[]
     */
    private $products;

    /**
     * @return \Thunderstone\Order\Model\Customer
     */
    public function getCustomer(): \Thunderstone\Order\Model\Customer
    {
        return $this->customer;
    }

    /**
     * @param \Thunderstone\Order\Model\Customer $customer
     */
    public function setCustomer(\Thunderstone\Order\Model\Customer $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return \Thunderstone\Order\Model\Shipping
     */
    public function getShipping(): \Thunderstone\Order\Model\Shipping
    {
        return $this->shipping;
    }

    /**
     * @param \Thunderstone\Order\Model\Shipping $shipping
     */
    public function setShipping(\Thunderstone\Order\Model\Shipping $shipping): void
    {
        $this->shipping = $shipping;
    }

    /**
     * @return \Thunderstone\Order\Model\Product[]
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @param \Thunderstone\Order\Model\Product[] $products
     */
    public function setProducts(array $products): void
    {
        $this->products = $products;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return !is_null($this->customer) &&
            !is_null($this->products) && !empty($this->products);
    }
}