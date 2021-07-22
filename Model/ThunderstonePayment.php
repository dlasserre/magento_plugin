<?php


namespace Thunderstone\Order\Model;


use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Model\Order\Payment;

class ThunderstonePayment extends AbstractExtensibleModel implements MethodInterface, PaymentMethodInterface
{
    
    /**
     * @var string
     */
    protected $code = 'thunderstone';

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    protected $formBlockType = \Magento\Payment\Block\Form::class;

    /**
     * @var string
     */
    protected $infoBlockType = \Magento\Payment\Block\Info::class;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $isGateway = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $isOffline = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canOrder = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canAuthorize = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canCapture = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canCapturePartial = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canCaptureOnce = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canRefund = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canRefundInvoicePartial = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canVoid = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canUseInternal = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canUseCheckout = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $isInitializeNeeded = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canFetchTransactionInfo = false;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $canReviewPayment = false;

    /**
     * This may happen when amount is captured, but not settled
     * @var bool
     */
    protected $canCancelInvoice = false;

    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var array
     */
    protected $debugReplacePrivateDataKeys = [];

    /**
     * Payment data
     *
     * @var Data
     */
    protected $paymentData;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var DirectoryHelper
     */
    private $directory;

    /**
     * @var string|null
     */
    private $title = 'Thunderstone Payment';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DirectoryHelper|null $directory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        DirectoryHelper $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->paymentData = $paymentData;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->directory = $directory ?: ObjectManager::getInstance()->get(DirectoryHelper::class);
        $this->initializeData($data);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setStore($storeId)
    {
        $this->setData('store', (int)$storeId);
        $this->storeId = $storeId;
    }

    public function getStore(): ?int
    {
        return $this->storeId;
    }

    public function getFormBlockType(): string
    {
        return $this->formBlockType;
    }

    public function getInfoBlockType(): string
    {
        return $this->infoBlockType;
    }

    public function canOrder(): bool
    {
        return $this->canOrder;
    }

    public function canAuthorize(): bool
    {
        return $this->canAuthorize;
    }

    public function canCapture(): bool
    {
        return $this->canCapture;
    }

    public function canCapturePartial(): bool
    {
        return $this->canCapturePartial;
    }

    public function canCaptureOnce(): bool
    {
        return $this->canCaptureOnce;
    }

    public function canRefund(): bool
    {
        return $this->canRefund;
    }

    public function canRefundPartialPerInvoice(): bool
    {
        return $this->canRefundInvoicePartial;
    }

    public function canVoid(): bool
    {
        return $this->canVoid;
    }

    public function canUseInternal(): bool
    {
        return $this->canUseInternal;
    }

    public function canUseCheckout(): bool
    {
        return $this->canUseCheckout;
    }

    public function canEdit(): bool
    {
        return false;
    }

    public function canFetchTransactionInfo(): bool
    {
        return $this->canFetchTransactionInfo;
    }

    public function fetchTransactionInfo(InfoInterface $payment, $transactionId): array
    {
        return [];
    }

    public function isGateway(): bool
    {
        return $this->isGateway;
    }

    public function isOffline(): bool
    {
        return $this->isOffline;
    }

    public function isInitializeNeeded(): bool
    {
        return $this->isInitializeNeeded;
    }

    public function canUseForCountry($country): bool
    {
        /*
       for specific country, the flag will set up as 1
       */
        if ($this->getConfigData('allowspecific') == 1) {
            $availableCountries = explode(',', $this->getConfigData('specificcountry'));
            if (!in_array($country, $availableCountries)) {
                return false;
            }
        }
        return true;
    }

    public function canUseForCurrency($currencyCode): bool
    {
        return true;
    }

    public function getInfoInstance(): InfoInterface
    {
        $instance = $this->getData('info_instance');
        if (!$instance instanceof InfoInterface) {
            throw new LocalizedException(
                __('We cannot retrieve the payment information object instance.')
            );
        }
        return $instance;
    }

    public function setInfoInstance(InfoInterface $info)
    {
        $this->setData('info_instance', $info);
    }

    public function validate(): ThunderstonePayment
    {
        /**
         * to validate payment method is allowed for billing country or not
         */
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Payment) {
            $billingCountry = $paymentInfo->getOrder()->getBillingAddress()->getCountryId();
        } else {
            $billingCountry = $paymentInfo->getQuote()->getBillingAddress()->getCountryId();
        }
        $billingCountry = $billingCountry ?: $this->directory->getDefaultCountry();

        if (!$this->canUseForCountry($billingCountry)) {
            throw new LocalizedException(
                __('You can\'t use the payment type you selected to make payments to the billing country.')
            );
        }

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function order(InfoInterface $payment, $amount): ThunderstonePayment
    {
        if (!$this->canOrder()) {
            throw new LocalizedException(__('The order action is not available.'));
        }
        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount): ThunderstonePayment
    {
        if (!$this->canAuthorize()) {
            throw new LocalizedException(__('The authorize action is not available.'));
        }
        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function capture(InfoInterface $payment, $amount): ThunderstonePayment
    {
        if (!$this->canCapture()) {
            throw new LocalizedException(__('The capture action is not available.'));
        }

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount): ThunderstonePayment
    {
        if (!$this->canRefund()) {
            throw new LocalizedException(__('The refund action is not available.'));
        }
        return $this;
    }

    public function cancel(InfoInterface $payment): ThunderstonePayment
    {
        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function void(InfoInterface $payment): ThunderstonePayment
    {
        if (!$this->canVoid()) {
            throw new LocalizedException(__('The void action is not available.'));
        }
        return $this;
    }

    public function canReviewPayment(): bool
    {
        return $this->canReviewPayment;
    }

    public function acceptPayment(InfoInterface $payment): bool
    {
        if (!$this->canReviewPayment()) {
            throw new LocalizedException(__('The payment review action is unavailable.'));
        }
        return false;
    }

    public function denyPayment(InfoInterface $payment): bool
    {
        if (!$this->canReviewPayment()) {
            throw new LocalizedException(__('The payment review action is unavailable.'));
        }
        return false;
    }

    public function getConfigData($field, $storeId = null)
    {
        if ('order_place_redirect_url' === $field) {
            return $this->getConfigPaymentAction();
        }
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        $path = 'payment/' . $this->getCode() . '/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);

    }

    /**
     * @throws LocalizedException
     */
    public function assignData(DataObject $data): ThunderstonePayment
    {
        $this->_eventManager->dispatch(
            'payment_method_assign_data_' . $this->getCode(),
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        $this->_eventManager->dispatch(
            'payment_method_assign_data',
            [
                AbstractDataAssignObserver::METHOD_CODE => $this,
                AbstractDataAssignObserver::MODEL_CODE => $this->getInfoInstance(),
                AbstractDataAssignObserver::DATA_CODE => $data
            ]
        );

        return $this;
    }

    public function isAvailable(CartInterface $quote = null): bool
    {
        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        $checkResult = new DataObject();
        $checkResult->setData('is_available', true);

        // for future use in observers
        $this->_eventManager->dispatch(
            'payment_method_is_active',
            [
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote
            ]
        );

        return $checkResult->getData('is_available');
    }

    public function isActive($storeId = null): bool
    {
        return (bool)(int)$this->getConfigData('active', $storeId);
    }

    public function initialize($paymentAction, $stateObject): ThunderstonePayment
    {
       return $this;
    }

    public function getConfigPaymentAction(): string
    {
        return $this->getConfigData('payment_action');
    }

    /**
     * Log debug data to file
     *
     * @param array $debugData
     * @return void
     * @deprecated 100.2.0
     */
    protected function _debug(array $debugData)
    {
        $this->logger->debug(
            $debugData,
            $this->getDebugReplacePrivateDataKeys(),
            $this->getDebugFlag()
        );
    }

    /**
     * Define if debugging is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @api
     * @deprecated 100.2.0
     */
    public function getDebugFlag(): bool
    {
        return (bool)(int)$this->getConfigData('debug');
    }

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     * @api
     * @deprecated 100.2.0
     */
    public function debugData($debugData)
    {
        $this->_debug($debugData);
    }

    /**
     * Return replace keys for debug data
     *
     * @return array
     * @deprecated 100.2.0
     */
    public function getDebugReplacePrivateDataKeys(): array
    {
        return (array) $this->debugReplacePrivateDataKeys;
    }

    public function getStoreId(): int
    {
        return $this->getStore();
    }

    public function getIsActive(): bool
    {
        return $this->isActive();
    }


    /**
     * Initializes injected data
     *
     * @param array $data
     * @return void
     */
    protected function initializeData(array $data = [])
    {
        if (!empty($data['formBlockType'])) {
            $this->formBlockType = $data['formBlockType'];
        }
    }
}