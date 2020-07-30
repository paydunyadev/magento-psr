<?php /**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paydunya\PaydunyaMagento\Api;


interface ManagementInterface
{

    /**
     * GET for Post api
     * @param string $param
     * @return string
     */

    public function callback();
}
