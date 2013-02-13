<?php
/**
 * Os Studios PagSeguro Api Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OsStudios
 * @package    OsStudios_PagSeguroApi
 * @copyright  Copyright (c) 2013 Os Studios (www.osstudios.com.br)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Tiago Sampaio <tiago.sampaio@osstudios.com.br>
 */

class OsStudios_PagSeguroApi_Model_Observer extends OsStudios_PagSeguroApi_Model_Abstract
{

	public function salesOrderPaymentLoadAfter(Varien_Event_Observer $observer)
	{
		$order = $observer->getOrder();

		if($order->getId() && ($order instanceof Mage_Sales_Model_Order)) {
			$payment = $order->getPayment();

			$history = Mage::getModel('pagseguroapi/payment_history')->load($order->getId(), 'order_id');

			if($history->getHistoryId()) {
				$payment->addData(array(
					'pagseguro_info' => $history,
				));
			}
		}
	}
	
}