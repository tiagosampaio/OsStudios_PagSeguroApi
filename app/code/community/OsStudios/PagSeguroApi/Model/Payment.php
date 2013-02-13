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

abstract class OsStudios_PagSeguroApi_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    /**
     * 
     * Current Order
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;
    

    /**
     * 
     * Current Quote
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;
    
    
    const PAGSEGURO_METHOD_CODE_API = 'pagseguro_api';
    

    /**
     * 
     * Status: Complete
     * @var (string)
     */
    const PAGSEGURO_STATUS_COMPLETE	= 'Completo';
    

    /**
     * 
     * Status: Waiting for Payment
     * @var (string)
     */
    const PAGSEGURO_STATUS_WAITING_PAYMENT = 'Aguardando Pagto';
    

    /**
     * 
     * Status: Approved
     * @var (string)
     */
    const PAGSEGURO_STATUS_APPROVED	= 'Aprovado';
    

    /**
     * 
     * Status: In Analysis
     * @var (string)
     */
    const PAGSEGURO_STATUS_ANALYSING = 'Em Análise';
    
    /**
     * 
     * Status: Canceled
     * @var (string)
     */
    const PAGSEGURO_STATUS_CANCELED = 'Cancelado';
    
    /**
     * 
     * Status: Returned
     * @var (string)
     */
    const PAGSEGURO_STATUS_RETURNED	= 'Devolvido';
    
    
    /**
     *  Return Quote Object
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getQuote()
    {
        if ($this->_quote == null) {
            if(!$this->_quote = Mage::getSingleton('checkout/session')->getQuote()) {
                return false;
            }
        }
        return $this->_quote;
    }
	
    
    /**
     * 
     *  Set Current Quote Object
     *
     *  @param Mage_Sales_Model_Quote $quote
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        $this->_quote = $quote;
        return $this;
    }
    
    
    /**
     *  Return Order
     *
     *  @return	  Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null) {
            if(!$this->_order = Mage::getSingleton('checkout/session')->getOrder()) {
                return false;
            }
        }
        return $this->_order;
    }
	
    
    /**
     * 
     *  Set Current Order
     *
     *  @param Mage_Sales_Model_Order $order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }
    
    
    /**
     * Provide Helper to children classes
     *
     * @return OsStudios_PagSeguroApi_Helper_Data
     */
    protected function helper()
    {
        return Mage::helper('pagseguroapi');
    }


    /**
     * Returns the URL where the customer needs to be redirected to
     * 
     * @param string $identifierCode
     *
     * @return (string)
     */ 
    protected function getPagseguroApiRedirectUrl($identifierCode = null)
    {
        return Mage::getModel('pagseguroapi/data')->getPagseguroApiRedirectUrl($identifierCode);
    }
    
}
