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

/**
 * PagSeguro Api Payment Info Block
 *
 */

class OsStudios_PagSeguroApi_Block_Api_Info extends Mage_Payment_Block_Info
{

    /**
     * Sets the template
     *
     * @return OsStudios_PagSeguroApi_Block_Api_Info
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('osstudios/pagseguroapi/info.phtml');

        return $this;
    }
    

    protected function _getOrder()
    {
        return $this->getInfo()->getOrder();
    }


    protected function _getPayment()
    {
        return $this->_getOrder()->getPayment();
    }


    protected function _beforeToHtml()
    {
        $this->_prepareInfo();
        return parent::_beforeToHtml();
    }


    protected function _prepareInfo()
    {
        $order = $this->_getOrder();

        if($order instanceof Mage_Sales_Model_Order) {
            $payment = $order->getPayment();
            $pagseguroInfo = $payment->getPagseguroInfo();

            if($pagseguroInfo) {
                $this->setPagseguroInfo($pagseguroInfo);
            }
        }
    }


    public function getShowInfo()
    {
        if($this->getPagseguroInfo()) {
            return true;
        }

        return false;
    }


    public function isPaid()
    {
        return ($this->getPagseguroInfo()->getPagseguroTransactionStatus() == 1);
    }


    public function isCanceled()
    {
        return ($this->getPagseguroInfo()->getPagseguroTransactionStatus() == 7);
    }


    public function getShowPaylink()
    {
        if(!$this->isPaid() && !$this->isCanceled() && !$this->getShowPayBilletUrl()) {
            return true;
        }

        return false;
    }


    public function getShowPayBilletUrl()
    {
        if(!$this->isPaid() && !$this->isCanceled() && ($this->getPagseguroInfo()->getPagseguroPaymentMethodType() == 2)) {
            return true;
        }

        return false;
    }


    public function getBilletPayUrl()
    {
        if($this->getShowPayBilletUrl()) {
            return Mage::getSingleton('pagseguroapi/data')->getPagSeguroBilletUrl($this->getPagseguroInfo()->getPagseguroTransactionId());
        }

        return;
    }


    public function getPayUrl()
    {
        if($this->getShowPaylink()) {
            return Mage::getSingleton('pagseguroapi/data')->getPagseguroApiRedirectUrl($this->getPagseguroInfo()->getPagseguroPaymentIdentifierCode());
        }

        return;
    }


    public function getTransactionPaymentMethodTypeLabel()
    {
        $paymentMethodType = $this->_getPayment()->getPagseguroInfo()->getPagseguroPaymentMethodType();
        $label = Mage::getSingleton('pagseguroapi/data')->getTransactionPaymentMethodTypeLabel($paymentMethodType);

        return $label;
    }


    public function getTransactionPaymentMethodCodeLabel()
    {
        $paymentMethodCode = $this->_getPayment()->getPagseguroInfo()->getPagseguroPaymentMethodCode();
        $label = Mage::getSingleton('pagseguroapi/data')->getTransactionPaymentMethodCodeLabel($paymentMethodCode);

        return $label;
    }


    public function getTransactionStatusLabel()
    {
        $transactionStatus = $this->_getPayment()->getPagseguroInfo()->getPagseguroTransactionStatus();
        $label = Mage::getSingleton('pagseguroapi/data')->getTransactionStatusLabel($transactionStatus);

        return $label;
    }
}