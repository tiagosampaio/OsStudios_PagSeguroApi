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

class OsStudios_PagSeguroApi_Model_Payment_Method_Api_Xml extends OsStudios_PagSeguroApi_Model_Abstract
{
    
    /**
     * Handles the Quote Object
     * 
     * @var Mage_Sales_Model_Quote
     */
    protected $_quote = null;
    
    
    /**
     * Handles the Order Object
     * 
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;
    
    
    /**
     * Handles the XML Object
     * 
     * @var SimpleXMLElement
     */
    protected $_xml = null;
    
    
    /**
     * Sets the Quote Object
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    public function setQuote(Mage_Sales_Model_Quote $quote)
    {
        if($quote->getId()) {
            $this->_quote = $quote;
        }
        
        return $this;
    }
    
    
    /**
     * Returns the Quote Object
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if(!$this->_quote) {
            $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        return $this->_quote;
    }
    
    
    /**
     * Sets the Order Object
     * 
     * @param Mage_Sales_Model_Order $order
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        if($order->getId()) {
            $this->_order = $order;
        }
        
        return $this;
    }
    
    
    /**
     * Returns the Order Object
     * 
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if(!$this->_order) {
            $this->_order = Mage::getSingleton('checkout/session')->getOrder();
        }
        
        /**
         * Remove......
         */
        //return $this->getQuote();
        return $this->_order;
    }
    
    
    /**
     * Point of entry to external classes get a XML Object
     * 
     * @return SimpleXMLElement
     */
    public function getXml()
    {
        if(!$this->_xml) {
            $this->_getBaseXml();
        }
        
        return $this->_xml;
    }
    
    
    /**
     * Sets the base XML object
     * 
     * @param SimpleXMLElement $xml
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _setBaseXml(SimpleXMLElement $xml)
    {
        $this->_xml = $xml;
        return $this;
    }
    
    
    /**
     * Parent method to generate the XML Object
     * It calls the responsible for generate the other nodes
     * 
     * @return SimpleXMLElement
     */
    protected function _getBaseXml()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="ISO-8859-1" standalone="yes"?><checkout/>');
        
        $this->_setBaseXml($xml);
        
        $this->_getNodeReceiver()
             ->_getNodeCurrency()
             ->_getNodeMaxUses()
             ->_getNodeMaxAge()
             ->_getNodeExtraAmount()
             ->_getNodeRedirectURL()
			 ->_getNodeNotificationURL()
             ->_getNodeItems()
             ->_getNodeReference()
             ->_getNodeSender()
             ->_getNodeShipping();
        
        return $xml;
    }
    
    
    /**
     * Generates the <receiver/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeReceiver()
    {
        $credentials = Mage::getSingleton('pagseguroapi/credentials');
        
        if($credentials->getAccountEmail()) {
            $xmlReceiver = $this->_xml->addChild('receiver');
            $xmlReceiver->addChild('email', $credentials->getAccountEmail());
        }
        return $this;
    }
    
    
    /**
     * Generates the <currency/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeCurrency()
    {
        if($this->getOrder()) {
            $this->_xml->addChild('currency', 'BRL');
        }
        return $this;
    }
    
    
    /**
     * Generates the <maxUses/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeMaxUses()
    {
        $this->_xml->addChild('maxUses', Mage::getStoreConfig('payment/pagseguro_api/max_uses'));
        return $this;
    }
    
    
    /**
     * Generates the <maxAge/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeMaxAge()
    {
        $this->_xml->addChild('maxAge', Mage::getStoreConfig('payment/pagseguro_api/max_age'));
        return $this;
    }
    
    
    /**
     * Generates the <extraAmount/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeExtraAmount()
    {
        $this->_xml->addChild('extraAmount', $this->_formatNumberToXml(Mage::getStoreConfig('payment/pagseguro_api/extra_amount')));
        return $this;
    }
    
    
    /**
     * Generates the <redirectURL/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeRedirectURL()
    {
        $this->_xml->addChild('redirectURL', (string) substr(Mage::getUrl('pagseguroapi/pay/success', array('order_id' => $this->getOrder()->getId())), 0, 255));
        return $this;
    }


	/**
	 * Generates the <notificationURL/> node
	 *
	 * @return OsStudios_PagSeguro_Model_Api_Xml
	 */
	protected function _getNodeNotificationURL()
	{
		$this->_xml->addChild('notificationURL', (string) substr(Mage::getUrl('pagseguroapi/returns'), 0, 255));
		return $this;
	}
    
    
    /**
     * Generates the <items/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeItems()
    {
        
        $xmlItems = $this->_xml->addChild('items');
        
        if($this->getOrder()) {
            foreach($this->getOrder()->getAllVisibleItems() as $item) {
                $xmlItem = $xmlItems->addChild('item');
                
                $xmlItem->addChild('id', 			(string) substr($item->getProductId(), 0, 100));
                $xmlItem->addChild('description', 	(string) substr($item->getName(), 0, 100));
                $xmlItem->addChild('amount', 		$this->_formatNumberToXml(($item->getRowTotal() /  $item->getQtyOrdered())));
                $xmlItem->addChild('quantity', 		(int) $item->getQtyOrdered());
                $xmlItem->addChild('shippingCost', 	(double) '0.00');
                $xmlItem->addChild('weight', 		(int) $item->getWeight());
            }
        }
        
        return $this;
    }
    
    
    /**
     * Generates the <reference/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeReference()
    {
        if($this->getOrder()) {
            $this->_xml->addChild('reference', substr($this->getOrder()->getRealOrderId(), 0, 200));
        }
        return $this;
    }
    
    
    /**
     * Generates the <sender/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeSender()
    {
        $xmlSender = $this->_xml->addChild('sender');
        
        if($this->getOrder()) {
            $xmlSender->addChild('name', $this->getOrder()->getCustomerFirstname() . ' ' . $this->getOrder()->getCustomerLastname());
            $xmlSender->addChild('email', $this->getOrder()->getCustomerEmail());
            
            /**
             * @todo: Find another way to threat the phone number.
             */
            $phone = preg_replace('/[^0-9]/', null, $this->getOrder()->getShippingAddress()->getTelephone());
            
            $digitCount = 8;
            if(($len = strlen($phone)) >= 11) {
                $digitCount = 9;
            }
            
            $areaCode = substr($phone, 0, ($len-$digitCount));
            $number = substr($phone, ($len-$digitCount), $digitCount);
            
            $xmlPhone = $xmlSender->addChild('phone');
            $xmlPhone->addChild('areaCode', $areaCode);
            $xmlPhone->addChild('number', $number);
        }
        
        return $this;
    }
    
    
    /**
     * Generates the <shipping/> node
     * 
     * @return OsStudios_PagSeguro_Model_Api_Xml
     */
    protected function _getNodeShipping()
    {
        $xmlShipping = $this->_xml->addChild('shipping');
        
        if($this->getOrder()) {
        	$shipping = $this->getOrder()->getShippingAddress() ? $this->getOrder()->getShippingAddress() : $this->getOrder()->getBillingAddress();
        
        	$xmlShipping->addChild('cost', $this->_formatNumberToXml($this->getOrder()->getShippingAmount()));
        
            $xmlShipping->addChild('type', Mage::getStoreConfig('payment/'.OsStudios_PagSeguroApi_Model_Payment::PAGSEGURO_METHOD_CODE_API.'/shipping_type'));
            $xmlAddress = $xmlShipping->addChild('address');
            
            if(is_array($shipping->getStreet())) {
                $address = $shipping->getStreet();
            } else {
				$address = array(
					$shipping->getStreet(),
					'0',
					$this->helper()->__('Not Provided'),
					$this->helper()->__('Not Provided')
				);
            }
            
            $xmlAddress->addChild('street', 		substr($this->helper()->cleanStringToXml($address[0]), 0, 80));
            $xmlAddress->addChild('number', 		substr(preg_replace('/[^0-9]/', null, $address[1]), 0, 20));
            $xmlAddress->addChild('complement', 	substr($this->helper()->cleanStringToXml($address[2]), 0, 40));
            $xmlAddress->addChild('district', 		substr($this->helper()->cleanStringToXml($address[3]), 0, 60));
            $xmlAddress->addChild('postalCode', 	substr(preg_replace('/[^0-9]/', null, $shipping->getPostcode()), 0, 8));
            $xmlAddress->addChild('city', 			substr($this->helper()->cleanStringToXml($shipping->getCity()), 0, 60));
            
            $regionCode = $this->helper()->cleanStringToXml($shipping->getRegionCode());
            
            $xmlAddress->addChild('state', (strlen($regionCode)==2) ? $regionCode : $this->helper()->getRegionCode($regionCode) );
            $xmlAddress->addChild('country', $this->helper()->cleanStringToXml($shipping->getCountryId()));
        }
        
        return $this;
    }


	/**
	 * Formats number for XML purpose
	 *
	 * @param float $value
	 *
	 * @return float
	 */
	protected function _formatNumberToXml($value = 0.00)
    {
        return (double) number_format($value, 2, '.', '');
    }

}
