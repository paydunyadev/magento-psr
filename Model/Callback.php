<?php
/**
 * Copyright © 2019 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paydunya\PaydunyaMagento\Model;

/**
 * Pay In Store payment method model
 */
class Callback
{
    protected $scopeConfig;
    protected $dataFunctions;

    function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                         \Paydunya\PaydunyaMagento\Helper\Data $dataFunctions
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->dataFunctions = $dataFunctions;
    }


    /**
     * {@inheritdoc}
     */
    public function callback(){
        try {
//            Prenez votre MasterKey, hashez la et comparez le résultat au hash reçu par IPN
            if($_POST['data']['hash'] === hash('sha512', $this->scopeConfig->getValue('payment/paydunya/master_key'))) {
                echo $this->scopeConfig->getValue('payment/paydunya/master_key');

              if ($_POST['data']['status'] == "completed") {
                /** update the order's state
                 * send order email and move to the success page
                 */
                  $response_decoded = $_POST['data'];
                  $custom_data = $response_decoded['custom_data'];
                  $orderId = $custom_data['order_id'];
                  $trackingId = $_POST['data']['invoice']['token'];

                  $this->dataFunctions->updateOrder($orderId, $trackingId, 'completeorder');
              }

            } else {
                die("An error occured while processing the response.");
            }
        } catch(Exception $e) {
            die('An error occured while processing the response.');
        }

        exit;
    }

}
