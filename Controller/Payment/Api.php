<?php /**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paydunya\PaydunyaMagento\Controller\Payment;

use Paydunya\PaydunyaMagento\Helper\Data;

class Api extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    protected $resource;
    protected $dataFunctions;
    protected $salesOrderFactory;
    protected $checkoutSession;
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        Data $dataFunctions,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order $salesOrderFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory)
    {
        $this->salesOrderFactory = $salesOrderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->resource = $resource;
        $this->dataFunctions = $dataFunctions;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context);
    }
    /**
     * View  page action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
//        $invoiceToken = $_GET['token'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

        $orderid = $this->checkoutSession->getLastRealOrder()->getIncrementId();

        $order = $this->salesOrderFactory->loadByIncrementId($orderid);

        $orderDetails = $order->getData();

        $orderDetails["p_store_name"] = $storeManager->getStore()->getFrontendName();
        $orderDetails["p_store_website_url"] = $storeManager->getStore()->getBaseUrl();
        $logo = $objectManager->get('\Magento\Theme\Block\Html\Header\Logo');
        $orderDetails["p_store_logo_url"] = $logo->getLogoSrc();
        $orderDetails["p_cancel_url"] = $storeManager->getStore()->getBaseUrl() . "paydunyamagento/payment/response";
        $orderDetails["p_return_url"] = $storeManager->getStore()->getBaseUrl() . "paydunyamagento/payment/response";
        $orderDetails["p_callback_url"] = $storeManager->getStore()->getBaseUrl() . "rest/V1/api/pin";
        $orderDetails['p_order_items'] = $orderItems = $order->getAllItems();
        $orderDetails['order_id'] = $orderid;
        \Paydunya\Setup::setMasterKey($this->scopeConfig->getValue('payment/paydunya/master_key'));
        \Paydunya\Setup::setPrivateKey($this->scopeConfig->getValue('payment/paydunya/live_private_key'));
        \Paydunya\Setup::setToken($this->scopeConfig->getValue('payment/paydunya/live_token'));
        \Paydunya\Setup::setMode("live"); // Optionnel. Utilisez cette option pour les paiements tests.
        //Configuration des informations de votre service/entreprise
        \Paydunya\Checkout\Store::setName($orderDetails["p_store_name"]); // Seul le nom est requis
        \Paydunya\Checkout\Store::setWebsiteUrl($orderDetails["p_store_website_url"]);
        \Paydunya\Checkout\Store::setCallbackUrl($orderDetails["p_callback_url"]);
        \Paydunya\Checkout\Store::setCancelUrl($orderDetails["p_cancel_url"] );
        \Paydunya\Checkout\Store::setReturnUrl($orderDetails["p_return_url"]);


        $invoice = new \Paydunya\Checkout\CheckoutInvoice();
        $invoice->setTotalAmount($orderDetails['grand_total'] );

        if($invoice->create()) {

            $test = "{\"success\":true,\"token\":\"".$invoice->token."\"}";
            echo  $test;

        }else{

            echo $invoice->response_text;

        }

    }
}
