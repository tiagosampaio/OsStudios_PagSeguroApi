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

class OsStudios_PagSeguroApi_PayController extends OsStudios_PagSeguroApi_Controller_Front_Action
{
    
    /**
     * Shows success page after payment.
     * 
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        Mage::register('osstudios_pagseguro_last_order_id', $lastOrderId);
        Mage::dispatchEvent('osstudios_pagseguroapi_controller_success_action', array('order_ids' => array($lastOrderId)));

        try {
            $order = Mage::getModel('sales/order')->load($lastOrderId);
            if ($order->getCanSendNewEmailFlag()){
                $order->sendNewOrderEmail();
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId)));
        $this->renderLayout();

        $session->clear();

        Mage::unregister('osstudios_pagseguro_last_order_id');
    }
    

    /**
     * Returns the installments block
     * 
     */
    public function installmentsAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}