<?php


namespace Thunderstone\Order\Model;


use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Payment\Model\Method\Adapter;
use Magento\Framework\Locale\Resolver;

class ThunderstonePayment implements MethodInterface
{
    
    /**
     * @var string
     */
    private $code = 'thunderstone';

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string|null
     */
    private $title = 'Thunderstone Payment';

    public function getCode()
    {
        return $this->code;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setStore($storeId)
    {
        $this->storeId = $storeId;
    }

    public function getStore()
    {
        return $this->storeId;
    }

    public function getFormBlockType()
    {
        var_dump('ploop');
        return 'page/html';
    }

    public function canOrder()
    {
        return true;
    }

    public function canAuthorize()
    {
        // TODO: Implement canAuthorize() method.
    }

    public function canCapture()
    {
        // TODO: Implement canCapture() method.
    }

    public function canCapturePartial()
    {
        // TODO: Implement canCapturePartial() method.
    }

    public function canCaptureOnce()
    {
        // TODO: Implement canCaptureOnce() method.
    }

    public function canRefund()
    {
        // TODO: Implement canRefund() method.
    }

    public function canRefundPartialPerInvoice()
    {
        // TODO: Implement canRefundPartialPerInvoice() method.
    }

    public function canVoid()
    {
        // TODO: Implement canVoid() method.
    }

    public function canUseInternal()
    {
        // TODO: Implement canUseInternal() method.
    }

    public function canUseCheckout()
    {
        // TODO: Implement canUseCheckout() method.
    }

    public function canEdit()
    {
        // TODO: Implement canEdit() method.
    }

    public function canFetchTransactionInfo()
    {
        // TODO: Implement canFetchTransactionInfo() method.
    }

    public function fetchTransactionInfo(InfoInterface $payment, $transactionId)
    {
        // TODO: Implement fetchTransactionInfo() method.
    }

    public function isGateway()
    {
        // TODO: Implement isGateway() method.
    }

    public function isOffline()
    {
        // TODO: Implement isOffline() method.
    }

    public function isInitializeNeeded()
    {
        // TODO: Implement isInitializeNeeded() method.
    }

    public function canUseForCountry($country)
    {
        // TODO: Implement canUseForCountry() method.
    }

    public function canUseForCurrency($currencyCode)
    {
        // TODO: Implement canUseForCurrency() method.
    }

    public function getInfoBlockType()
    {
        return \Magento\Payment\Block\Form::class;
    }

    public function getInfoInstance()
    {
        // TODO: Implement getInfoInstance() method.
    }

    public function setInfoInstance(InfoInterface $info)
    {
        // TODO: Implement setInfoInstance() method.
    }

    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function order(InfoInterface $payment, $amount)
    {
        // TODO: Implement order() method.
    }

    public function authorize(InfoInterface $payment, $amount)
    {
        // TODO: Implement authorize() method.
    }

    public function capture(InfoInterface $payment, $amount)
    {
        // TODO: Implement capture() method.
    }

    public function refund(InfoInterface $payment, $amount)
    {
        // TODO: Implement refund() method.
    }

    public function cancel(InfoInterface $payment)
    {
        // TODO: Implement cancel() method.
    }

    public function void(InfoInterface $payment)
    {
        // TODO: Implement void() method.
    }

    public function canReviewPayment()
    {
        // TODO: Implement canReviewPayment() method.
    }

    public function acceptPayment(InfoInterface $payment)
    {
        // TODO: Implement acceptPayment() method.
    }

    public function denyPayment(InfoInterface $payment)
    {
        // TODO: Implement denyPayment() method.
    }

    public function getConfigData($field, $storeId = null)
    {
        // TODO: Implement getConfigData() method.
    }

    public function assignData(DataObject $data)
    {
        // TODO: Implement assignData() method.
    }

    public function isAvailable(CartInterface $quote = null)
    {
        return true;
    }

    public function isActive($storeId = null)
    {
        return true;
    }

    public function initialize($paymentAction, $stateObject)
    {
        // TODO: Implement initialize() method.
    }

    public function getConfigPaymentAction()
    {
        // TODO: Implement getConfigPaymentAction() method.
    }
}