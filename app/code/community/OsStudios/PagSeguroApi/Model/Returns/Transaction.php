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

class OsStudios_PagSeguroApi_Model_Returns_Transaction extends OsStudios_PagSeguroApi_Model_Returns
{

	/**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'pagseguroapi_transaction';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'transaction';


	protected function _construct()
    {
        $this->_init('pagseguroapi/returns_transaction');
    }


    /**
     * Import data to model
     *
     * @param Varien_Simplexml_Config $xml
     *
     * @var string
     */
	public function importData(Varien_Simplexml_Config $xml)
	{
		$arr = $xml->getNode()->asArray();

		$data = array(
			'date' 					=> $arr['date'],
			'reference' 			=> $arr['reference'],
			'type' 					=> $arr['type'],
			'status' 				=> $arr['status'],
			'last_event_date' 		=> $arr['lastEventDate'],
			'gross_amount' 			=> $arr['grossAmount'],
			'discount_amount' 		=> $arr['discountAmount'],
			'fee_amount' 			=> $arr['feeAmount'],
			'net_amount' 			=> $arr['netAmount'],
			'extra_amount' 			=> $arr['extraAmount'],
		);

		if(isset($arr['installmentCount'])) {
			$data['installment_count'] = $arr['installmentCount'];
		}

		if(isset($arr['itemCount'])) {
			$data['item_count'] = $arr['itemCount'];
		}

		if(isset($arr['code'])) {
			$data['code'] = $arr['code'];
		}

		/* Payment Method */
		$paymentMethod = new Varien_Object();
		if(isset($arr['paymentMethod'])) {
			if(isset($arr['paymentMethod']['type'])) {
				$paymentMethod->setType($arr['paymentMethod']['type']);
				$data['payment_method_type'] = $paymentMethod->getType();
			}

			if(isset($arr['paymentMethod']['code'])) {
				$paymentMethod->setCode($arr['paymentMethod']['code']);
			  	$data['payment_method_code'] = $paymentMethod->getCode();
			}
		}
		$data['payment_method'] = $paymentMethod;

		/* Sender */
		$sender = new Varien_Object();
		if(isset($arr['sender'])) {
			if(isset($arr['sender']['name'])) {
				$sender->setName($arr['sender']['name']);
				$data['sender_name'] = $sender->getName();
			}

			if(isset($arr['sender']['email'])) {
				$sender->setEmail($arr['sender']['email']);
				$data['sender_email'] = $sender->getEmail();
			}
		}
		$data['sender'] = $sender;

		if($data['reference']) {
			$order = Mage::getModel('sales/order')->loadByIncrementId($data['reference']);
			if($order->getId()) {
				$this->setOrder($order)
					 ->setOrderId($order->getEntityId());
			}
		}

		$this->addData($data);

		$this->setIsValid($this->_isTransactionCompatible());

		if($this->getIsValid()) {
			switch ($this->getReceivedFrom()) {
				case 1:
					$canSave = Mage::getStoreConfigFlag('payment/pagseguro_api/allow_log_notifications');
					break;
				case 2:
					$canSave = Mage::getStoreConfigFlag('payment/pagseguro_api/allow_log_consults');
					break;
				case 3:
					$canSave = Mage::getStoreConfigFlag('payment/pagseguro_api/allow_log_mass_consults');
					break;
			}

			if($canSave) {
				$this->save();
			}
		}

		return $this;
	}


	/**
     * Processing object before save data
     *
     * @return OsStudios_PagSeguroApi_Model_Returns_Transaction
     */
    protected function _beforeSave()
    {
    	return parent::_beforeSave();
    }


    /**
	 * Verifies if the transaction passed is compatible with the order in the system.
	 *
	 * @return (boolean)
	 */
	private function _isTransactionCompatible()
	{
		if(!$this->getOrder() || !($this->getOrder() instanceof Mage_Sales_Model_Order)) {
			return false;
		} elseif(round(((float) $this->getOrder()->getGrandTotal() - (float) $this->getGrossAmount()), 2) != (float) 0.00) {
			return false;
		} 

		/**
		 * Validates only if the consult was made by massaction option
		 *
		 */
		if($this->getReceivedFrom() != 3) {
			if($this->getOrder()->getCustomerEmail() !== $this->getSender()->getEmail()) {
				return false;
			} elseif((int) count($this->getOrder()->getAllVisibleItems()) !== (int) $this->getItemCount()) {
				return false;
			}
		}

		return true;
	}

}