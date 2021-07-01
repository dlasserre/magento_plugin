<?php

namespace Thunderstone\Order\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Interceptor;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Service\OrderService;
use Magento\Store\Model\StoreManagerInterface;
use Thunderstone\Order\Api\OrderControllerInterface;

class OrderController implements OrderControllerInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var FormKey
     */
    private $formkey;
    /**
     * @var Quote
     */
    private $quote;
    /**
     * @var QuoteManagement
     */
    private $quoteManagement;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var OrderService
     */
    private $orderService;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param FormKey $formkey
     * @param QuoteFactory $quote ,
     * @param QuoteManagement $quoteManagement
     * @param CustomerFactory $customerFactory ,
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderService $orderService,
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Interceptor $productRepository,
        FormKey $formkey,
        QuoteFactory $quote,
        QuoteManagement $quoteManagement,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        OrderService $orderService
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
    }

    /**
     * Create Order On Your Store
     *
     * @param Order $order
     * @return array
     *
     * @throws LocalizedException
     */
    public function createOrder(Order $order)
    {
        $store = $this->storeManager->getStore();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);

        $objectManager = ObjectManager::getInstance();
        $customer = $objectManager->get('\Magento\Customer\Api\CustomerRepositoryInterface')->get($order->getCustomer()->getEmail());

        if(!$customer->getId()){
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customer->setStoreId($store->getId());
            $customer->setFirstname($order->getCustomer()->getFirstname());
            $customer->setLastname($order->getCustomer()->getLastname());
            $customer->setEmail($order->getCustomer()->getEmail());
        }

        $quoteFactory = $objectManager->get('\Magento\Quote\Model\QuoteFactory')->create(); //Create object of quote
        $quoteFactory->setStore($store); //set store for which you create quote
        $quoteFactory->setCurrency();
        $quoteFactory->assignCustomer($customer); //Assign quote to customer

        $address = [
            'country_id' => 'FR',
            'street' => '5 rue de paris',
            'city' => 'Paris',
            'firstname' => 'Jean',
            'lastname' => 'Dupond',
            'telephone' => '0102030405',
            'postcode' => '75001',
            'method' => 'free',
            'shipping_method' => 'flat_rate'
        ];

        $quoteFactory->getBillingAddress()->addData($address);
        $quoteFactory->getShippingAddress()->addData($address);

        //add items in quote
        foreach($order->getProducts() as $product){
            $quoteFactory->addProduct(
                $objectManager->get('\Magento\Catalog\Model\ProductRepository')->get($product->getSku()),
                $product->getQuantity()
            );
        }

        $quoteFactory->getShippingAddress()->setCollectShippingRates(true);
        $quoteFactory->getShippingAddress()->collectShippingRates();

        $shippingQuoteRate = $objectManager->get('\Magento\Quote\Model\Shipping');
        $quoteFactory->getShippingAddress()->setShippingMethod('flatrate_flatrate');
        //$quoteFactory->getShippingAddress()->addShippingRate($shippingQuoteRate);

        /**
        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('freeshipping_freeshipping'); //shipping method
         * */
        $quoteFactory->setPaymentMethod('checkmo'); //payment method
        $quoteFactory->setInventoryProcessed(true); //not effect inventory
        $quoteFactory->save(); //Now Save quote and your quote is ready

        // Set Sales Order Payment
        $quoteFactory->getPayment()->importData(['method' => 'checkmo']);

        // Collect Totals & Save Quote
        $quoteFactory->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quoteFactory);

        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();
        if($order->getEntityId()){
            $result['order_id']= $order->getRealOrderId();
        }else{
            $result=['error'=>1,'msg'=>'Your custom message'];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder(string $orderId): string
    {
        return 'Here is the order id: '.$orderId;
    }

    /**
     * {@inheritdoc}
     */
    public function postOrder(Order $order): array
    {
        if(!$order->isValid()){
            return [
                [
                    'status' => 'ko',
                    'message' => 'order is not valid'
                ]
            ];
        }
        $this->createOrder($order);
        return [
            [
                'status' => [
                    'success' => true
                ]
            ]
        ];
    }
}