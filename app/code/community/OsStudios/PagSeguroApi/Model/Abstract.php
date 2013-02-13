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

abstract class OsStudios_PagSeguroApi_Model_Abstract extends Mage_Core_Model_Abstract
{

    const XPATH_CONFIG_TRANSACTION = 'osstudios_pagseguroapi/transaction/';


    /**
     * Get config instance
     *
     * @return Mage_Core_Model_Config
     */
    private function _getConfig()
    {
        return Mage::app()->getConfig();
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
     * Provides any module config option
     *
     */
	protected function getConfigData($configNode)
	{
		return Mage::getStoreConfig('payment/pagseguro_api/'.$configNode);
	}


    /**
     * Provides the credentials model
     *
     * @return OsStudios_PagSeguroApi_Model_Credentials
     */
    protected function _getCredentials()
    {
        return Mage::getSingleton('pagseguroapi/credentials');
    }


    /**
     * Get Transaction Payment Method Type label
     *
     * @return string
     */
    public function getTransactionPaymentMethodTypeLabel($type)
    {
        $path = self::XPATH_CONFIG_TRANSACTION.'payment_methods/types/type_'.$type.'/label';
        $label = Mage::getStoreConfig($path);
        return $label;
    }


    /**
     * Get Transaction Payment Method Code label
     *
     * @return string
     */
    public function getTransactionPaymentMethodCodeLabel($code)
    {
        $path = self::XPATH_CONFIG_TRANSACTION.'payment_methods/codes/code_'.$code.'/label';
        $label = Mage::getStoreConfig($path);
        return $label;
    }


    /**
     * Get Transaction Status label
     *
     * @return string
     */
    public function getTransactionStatusLabel($code)
    {
        $path = self::XPATH_CONFIG_TRANSACTION.'status/status_'.$code.'/label';
        $label = Mage::getStoreConfig($path);
        return $label;
    }


	/**
     * Returns the URL to generate the billets of PagSeguro
     * 
     * @param string $transactionId
     * @param boolean $escapeHtml
     *
     * @return (string)
     */ 
    public function getPagSeguroBilletUrl($transactionId = null, $escapeHtml = false)
    {
        $url = $this->getConfigData('pagseguro_billet_url');
    	if(!$url) {
            Mage::throwException($this->helper()->__('The PagSeguro Billet URL could not be retrieved.'));
    	}
    	
        if(!is_null($transactionId)) {
            $url = sprintf('%s?resizeBooklet=n&code=%s', $url, $transactionId);
        }

        return $escapeHtml ? $this->helper()->htmlEscape($url) : $url;
    }


    /**
     * Returns the URL to consult the transactions in PagSeguro
     * 
     * @param string $transactionId
     * @param boolean $escapeHtml
     *
     * @return (string)
     */ 
    public function getPagSeguroTransactionUrl($transactionId = null, $escapeHtml = false)
    {
        $url = $this->getConfigData('pagseguro_transactions_url');
        if(!$url) {
            Mage::throwException($this->helper()->__('The PagSeguro Transaction URL could not be retrieved.'));
        }

        if(!is_null($transactionId)) {
            $url = sprintf('%s/%s?email=%s&token=%s', $url, $transactionId, $this->_getCredentials()->getAccountEmail(), $this->_getCredentials()->getAccountToken());
        }

        return $escapeHtml ? $this->helper()->htmlEscape($url) : $url;
    }
    

    /**
     * Returns the notifications URL to consult the transactions in PagSeguro
     * 
     * @param string $notificationId
     * @param boolean $escapeHtml
     *
     * @return (string)
     */ 
    public function getPagSeguroNotificationUrl($notificationId = null, $escapeHtml = false)
    {
        $url = $this->getConfigData('pagseguro_notifications_url');
        if(!$url) {
            Mage::throwException($this->helper()->__('The PagSeguro Notification URL could not be retrieved.'));
        }

        if(!is_null($notificationId)) {
            $url = sprintf('%s/%s?email=%s&token=%s', $url, $notificationId, $this->_getCredentials()->getAccountEmail(), $this->_getCredentials()->getAccountToken());
        }

        return $escapeHtml ? $this->helper()->htmlEscape($url) : $url;
    }


    /**
     * Returns the URL where the customer needs to be redirected to
     * 
     * @param string $identifierCode
     * @param boolean $escapeHtml
     *
     * @return (string)
     */ 
    public function getPagseguroApiRedirectUrl($identifierCode = null, $escapeHtml = false)
    {
        $url = $this->getConfigData('pagseguro_api_redirect_url');
        if(!$url) {
            Mage::throwException($this->helper()->__('The PagSeguro Redirect URL could not be retrieved.'));
        }

        if(!is_null($identifierCode)) {
            $url = sprintf('%s?code=%s', $url, $identifierCode);
        }

        return $escapeHtml ? $this->helper()->htmlEscape($url) : $url;
    }

}