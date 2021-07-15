<?php

namespace Thunderstone\Order\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Interceptor;
use Magento\Catalog\Model\ProductRepository;
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
use Magento\Quote\Model\QuoteRepository;
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
    private $productInterceptor;
    /**
     * @var FormKey
     */
    private $formkey;
    /**
     * @var Quote
     */
    private $quoteFactory;
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
     * @param Interceptor $productInterceptor
     * @param FormKey $formkey
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param OrderService $orderService
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Interceptor $productInterceptor,
        FormKey $formkey,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        OrderService $orderService
    ) {
        $this->storeManager = $storeManager;
        $this->productInterceptor = $productInterceptor;
        $this->formkey = $formkey;
        $this->quoteFactory = $quoteFactory;
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
    public function createOrder(Order $order): array
    {
        $store = $this->storeManager->getStore();
        $objectManager = ObjectManager::getInstance();
        $errors = [];

        try{
            $customer = $this->customerRepository->get($order->getCustomer()->getEmail());
        }
        catch (NoSuchEntityException $exception){
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customer->setStoreId($store->getId());
            $customer->setFirstname($order->getCustomer()->getFirstname());
            $customer->setLastname($order->getCustomer()->getLastname());
            $customer->setEmail($order->getCustomer()->getEmail());
            $this->customerRepository->save($customer);
            $customer = $this->customerRepository->getById($customer->getEntityId());
        }

        $quoteRepository = $objectManager->get(QuoteRepository::class);

        $quote = $this->quoteFactory->create();
        $quote->setStore($store);
        $quote->setCurrency();
        $quote->assignCustomer($customer);

        $quote->getBillingAddress()->addData($order->getShipping()->getAddress());
        $quote->getShippingAddress()->addData($order->getShipping()->getAddress());

        $productRepository = $objectManager->get(ProductRepository::class);
        foreach($order->getProducts() as $product){
            try{
                $quote->addProduct(
                    $productRepository->get($product->getSku()),
                    $product->getQuantity()
                );
            }
            catch (NoSuchEntityException $exception) {
                $errors[] = [
                    'type' => 'product not found',
                    'info' => $product->getSku()
                ];
            }
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->getShippingAddress()->collectShippingRates();

        if(!is_null($method = $order->getShipping()->getMethod())){
            $quote->getShippingAddress()->setShippingMethod($method);
        }
        else{
            $quote->getShippingAddress()->setShippingMethod('thunderstone_thunderstone');
        }

        $quote->setPaymentMethod('thunderstone');
        $quote->setInventoryProcessed(true);


        $quoteRepository->save($quote);

        $quote->getPayment()->importData(['method' => 'thunderstone']);
        $quote->collectTotals();
        $quoteRepository->save($quote);

        $order = $this->quoteManagement->submit($quote);
        $order->setState(\Magento\Sales\Model\Order::STATE_COMPLETE)->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
        $order->setEmailSent(0);
        $order->setTotalPaid($order->getTotalDue());
        $objectManager->get('\Magento\Sales\Api\OrderRepositoryInterface')->save($order);

        /**
        $invoice = $objectManager->get(InvoiceFactory::class)->create();
        $invoice->setOrder($order);
        $invoice->setRequestCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->setItems($order->getItems());
        $invoice->register();
        $invoice->capture();
        $invoice->pay();
        $objectManager->get(InvoiceRepository::class)->save($invoice);
         * */

        if($order->getEntityId()){
            return [
                'response' => [
                    'success' => true,
                    'message' => 'ok',
                    'order_id' => $order->getRealOrderId(),
                    'errors' => $errors
                ]
            ];
        }
        else
        {
            return [
                'response' => [
                    'success' => false,
                    'message'=> 'Error while processing the order',
                    'errors' => $errors
                ]
            ];
        }
    }

    /**
     * {@inheritdoc}
     * @throws LocalizedException
     */
    public function postOrder(Order $order): array
    {
        if(!$order->isValid()){
            return [
                [
                    'success' => false,
                    'message' => 'order is not valid',
                    'errors' => [
                        [
                            'type' => 'invalid order',
                            'info' => 'invalid order'
                        ]
                    ]
                ]
            ];
        }
        return $this->createOrder($order);
    }
}