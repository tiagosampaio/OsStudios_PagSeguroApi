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

class OsStudios_PagSeguroApi_Block_Api_Success extends Mage_Core_Block_Template
{

    /**
     * Sets the template
     *
     * @return Mage_Core_Block_Template
     */
	public function _construct()
	{
		$this->setTemplate('osstudios/pagseguroapi/success.phtml');
		return parent::_construct();
	}

    
	/**
     * Before rendering html, but after trying to load cache
     *
     * @return Mage_Core_Block_Template
     */
    protected function _beforeToHtml()
    {
    	if(($orderId = Mage::registry('osstudios_pagseguro_last_order_id'))) {
    		$order = Mage::getModel('sales/order')->load($orderId);

            $isVisible = !in_array($order->getState(), Mage::getSingleton('sales/order_config')->getInvisibleOnFrontStates());
            $isHolded = (boolean) ($order->getState() == Mage_Sales_Model_Order::STATE_HOLDED);

            $payment = $order->getPayment();
            $pagseguroInfo = $payment->getPagseguroInfo();

    		$this->addData(array(
    			'order_id' => $order->getRealOrderId(),
    			'is_order_visible' => $isVisible,
                'is_order_holded'  => $isHolded,
                'can_print_order' => $isVisible,
                'can_view_order'  => (boolean) (Mage::getSingleton('customer/session')->isLoggedIn() && $isVisible),
                'view_order_url' => $this->getUrl('sales/order/view/', array('order_id' => $orderId)),
                'print_url' => $this->getUrl('sales/order/print', array('order_id'=> $orderId)),
			));

    		if($pagseguroInfo instanceof OsStudios_PagSeguroApi_Model_Payment_History) {
    			$this->addData(array(
    				'pagseguro_transaction' => $pagseguroInfo,
    				'pagseguro_boleto_url'  => Mage::getSingleton('pagseguroapi/data')->getPagSeguroBilletUrl($pagseguroInfo->getPagseguroTransactionId()),
    				'pagseguro_payment_url' => Mage::getSingleton('pagseguroapi/data')->getPagseguroApiRedirectUrl($pagseguroInfo->getPagseguroPaymentIdentifierCode()),
    			));
    		}
    	}

        return parent::_beforeToHtml();
    }
    
}