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

class OsStudios_PagSeguroApi_Model_Payment_Method_Api extends OsStudios_PagSeguroApi_Model_Payment
{
    
    protected $_code = self::PAGSEGURO_METHOD_CODE_API;
    protected $_formBlockType = 'pagseguroapi/api_form';
    protected $_infoBlockType = 'pagseguroapi/api_info';
    
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = true;
    protected $_canUseForMultishipping  = false;
    
    protected $_isGateway               = true;
    protected $_canOrder                = true;
    
    protected $_identifierCodeRegistry = 'pagseguroapi_payment_identifier_code';

    
    /**
     * Return the URL to be redirected to when finish purchasing
     * 
     * @return boolean | string
     */
    public function getOrderPlaceRedirectUrl($orderId = null)
    {
        if($this->helper()->openPagSeguroInOtherPage()) {
            return Mage::getUrl('pagseguroapi/pay/success');
        } else {
            $_code = Mage::registry($this->_identifierCodeRegistry);

            if($this->_isValidPagSeguroResultCode($_code)) {
                $url = $this->getPagseguroApiRedirectUrl($_code);

                Mage::unregister($this->_identifierCodeRegistry);
                return $url;
            }
        }

        return false;
    }
    

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
		return $this->$paymentAction();
    }


    /**
     * Creates a new order request in PagSeguro via Api method
     *
     * @return OsStudios_PagSeguroApi_Model_Payment_Method_Api
     */
    public function createTransaction()
    {
        $credentials = Mage::getSingleton('pagseguroapi/credentials');
        $url = sprintf('%s?email=%s&token=%s', $this->getConfigData('pagseguro_api_url'), $credentials->getAccountEmail(), $credentials->getAccountToken());

		try{
			$xml = Mage::getSingleton('pagseguroapi/payment_method_api_xml')->setOrder($this->_getOrder())->getXml();

			$client = new Zend_Http_Client($url);
			$client->setMethod(Zend_Http_Client::POST)
				   ->setHeaders('Content-Type: application/xml; charset=ISO-8859-1')
				   ->setRawData($xml->asXML(), 'text/xml');

			$request = $client->request();

			if(!$this->helper()->isXml(($body = $request->getBody()))) {
				Mage::log($this->helper()->__("When the system tried to authorize with login '%s' and token '%s' got '%s' as result.", $credentials->getAccountEmail(), $credentials->getAccountToken(), $request->getBody()), null, 'osstudios_pagseguro_unauthorized.log');
				Mage::throwException($this->helper()->__('A problem has occured while trying to authorize the transaction in PagSeguro.'));
			}

			$errors = $this->_hasErrorInReturn($body);
			if(is_array($errors)) {
				$message = implode("\n", $errors);
				Mage::throwException($message);
				return;
			}

			$config = new Varien_Simplexml_Config($body);
			$result = $config->getNode()->asArray();

			if((!isset($result['code']) || !$this->_isValidPagSeguroResultCode($result['code'])) || !isset($result['date'])) {
				Mage::throwException($this->helper()->__('Your payment could not be processed by PagSeguro.'));
			}

			Mage::register($this->_identifierCodeRegistry, $result['code']);

			$history = Mage::getModel('pagseguroapi/payment_history');
			$history->setOrderId($this->_getOrder()->getId())
					->setOrderIncrementId($this->_getOrder()->getRealOrderId())
					->setPagseguroPaymentIdentifierCode($result['code'])
					->setPagseguroTransactionDate($result['date'])
					->save();

		} catch (Mage_Core_Exception $e) {
			Mage::throwException($e->getMessage());
			return $this;
		} catch (Exception $e) {
			Mage::throwException($e->getMessage());
			return $this;
		}

        return $this;
    }


    /**
     * Get Order Object
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        return Mage::getModel('sales/order')->loadByIncrementId($this->_getOrderIncrementId());
    }
    

    /**
     * Order increment ID getter (either real from order or a reserved from quote)
     *
     * @return string
     */
    private function _getOrderIncrementId()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->getReservedOrderId();
        }
    }
    

    /**
     * Whether current operation is order placement
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof Mage_Sales_Model_Quote_Payment) {
            return false;
        } elseif ($info instanceof Mage_Sales_Model_Order_Payment) {
            return true;
        }
    }

    
    /**
     * Validates the result code from PagSeguro call
     * 
     * @param (string) $code
     *
     * @return bool
     */
    protected function _isValidPagSeguroResultCode($code)
    {
        if($code && (strlen($code) == 32)) {
            return true;
        }

        return false;
    }


    /**
     * Checks if exists errors in the result
     * 
     * @param (xml) $code
     *
     * @return || Array
     */
    protected function _hasErrorInReturn($body)
    {
        if($this->helper()->isXml($body)) {
            $xml = new SimpleXMLElement($body);

            if(count($xml->error)) {
                
                $resultArr = array();

                foreach($xml->error as $error) {
                    if($error->code) {
						$resultArr[] = Mage::helper('pagseguroapi')->__('Error: %s (%s)', $error->message, $error->code);
                    }
                }
                return $resultArr;
            }
        }
        return;
    }
    
}
