<?php

/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 07/09/2019
 * Time: 15:24
 */


namespace Magenest\SagePay\Model;


use Magenest\SagePay\Api\ThreeDInfoInterface;

/**
 * Class ThreeDSecure
 * @package Magenest\SagePay\Model
 */
class ThreeDSecure implements ThreeDInfoInterface
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * ThreeDSecure constructor.
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
        $this->_request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return mixed
     */
    public function get3DInfo()
    {
        try {
            $data = $this->get3DSecureResponseData();
        } catch (\Exception $e) {
            return json_encode([
                'error' => true,
                'message' => __("Payment exception: " . $e->getMessage())
            ]);
        }

        return json_encode($data);

    }

    /**
     * @return array|mixed
     */
    public function get3DSecureResponseData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $payment = $order->getPayment();
        $data = json_decode($payment->getAdditionalInformation('3d_secure_response'), true);
        if ($data) {
            $order->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
            $order->setStatus('pending_payment');
            $this->orderRepository->save($order);
            $data['is3dSecure'] = true;
            $data['success'] = true;
            $data['threeDSSessionData'] = isset($data['VPSTxId']) ? $data['VPSTxId'] : '';
            return $data;
        } else {
            return [
                'is3dSecure' => false,
                'success' => true
            ];
        }
    }

}
