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

class OsStudios_PagSeguroApi_Model_Consulter extends OsStudios_PagSeguroApi_Model_Returns
{

	const PAGSEGURO_CONSULT_RESPONSE_UNAUTHORIZED 	= 'Unauthorized';
	const PAGSEGURO_CONSULT_RESPONSE_AUTHORIZED 	= 'Authorized';
	const PAGSEGURO_CONSULT_RESPONSE_ERROR 			= 'Process Error';
	const PAGSEGURO_CONSULT_RESPONSE_NOT_FOUND 		= 'Not Found';


	/**
	 * Makes the consult by Transaction ID
	 * The store is consulting a transaction in PagSeguro here
	 *
	 * @param (string) $transactionId
 	 * 
	 * @return OsStudios_PagSeguroApi_Model_Consulter
	 */
	public function consultByTransactionId($transactionId)
	{
		if($transactionId) {
			$url = $this->getPagSeguroTransactionUrl($transactionId);
			
			$body = $this->_consult($url);

			if($body) {
				$xml = new Varien_Simplexml_Config($body);
				$this->updateSingleTransaction($xml, 2);
			}

			return $this;
		}
	}


	/**
	 * Makes the consult by Notification ID
	 * PagSeguro sent a notification to store
	 * 
	 * @param (string) $notificationId
 	 * 
	 * @return OsStudios_PagSeguroApi_Model_Consulter
	 */
	public function consultByNotificationId($notificationId)
	{
		$url = $this->getPagSeguroNotificationUrl($notificationId);

		$body = $this->_consult($url);

		if($body) {
			$xml = new Varien_Simplexml_Config($body);
			$this->updateSingleTransaction($xml, 1);
		}

		return $this;
	}


	/**
	 * Makes the mass consult 
	 * 
	 * @param (datetime) $initialDate
	 * @param (datetime) $finalDate
	 * @param (int) $page
	 * @param (int) $maxPageResults
 	 * 
	 * @return 
	 */
	public function massConsult($initialDate = null, $finalDate = null, $page = 1, $maxPageResults = 100)
	{
		/**
		 * @example https://ws.pagseguro.uol.com.br/v2/transactions
		 *			?initialDate=2011-01-01T00:00
		 *			&finalDate=2011-01-28T00:00
		 *			&page=1
		 *			&maxPageResults=100
		 *			&email=suporte@lojamodelo.com.br
		 *			&token=95112EE828D94278BD394E91C4388F20
		 */

		$url = Mage::getStoreConfig('payment/pagseguro_api/pagseguro_transactions_url');

		$credentials = Mage::getModel('pagseguroapi/credentials');

		$date = array();

		if(is_null($initialDate)) {
			$date['from'] = strtotime(date('Y-m-d', time()).' - 30 DAYS');
		} else {
			$date['from'] = $initialDate;
		}

		if(is_null($finalDate)) {
			$date['to'] = time();
		} else {
			$date['to'] = $finalDate;
		}

		$date['from'] = Mage::getSingleton('core/date')->date('Y-m-d\T00:00', $date['from']);
		$date['to'] = Mage::getSingleton('core/date')->date('Y-m-d\TH:m', $date['to']);

		$fullUrl =  sprintf('%s?initialDate=%s&finalDate=%s&page=%s&maxPageResults=%s&email=%s&token=%s', $url, $date['from'], $date['to'], $page, $maxPageResults, $credentials->getAccountEmail(), $credentials->getAccountToken());

		$body = $this->_consult($fullUrl);

		if($body) {
			$xml = new SimpleXMLElement($body);

			$a = 0; /* Count of consults */
			$y = 0; /* Success on Update */
			$w = 0; /* Not Updated */
			foreach($xml->transactions->transaction as $trans) {
				$transaction = new Varien_Simplexml_Config($trans->asXML());
				if($this->updateSingleTransaction($transaction, 3)) {
					$y++;
				} else {
					$w++;
				};

				$a++;
			}
		}

		if($a) {
			Mage::getSingleton('admin/session')->addSuccess($this->helper()->__('%s transactions were consulted in PagSeguro. %s successfully updated a transaction in the store.', $a, $y));
		}

		if($w) {
			Mage::getSingleton('admin/session')->addNotice($this->helper()->__('%s transactions was not validated by the system.', $w));
		}

		return true;
	}


	/**
	 * Abstract method that makes the real consult in PagSeguro
	 * but not process the result itself, only gets the response
	 *
	 * @param (string) $url
 	 * 
	 * @return xml format
	 */
	protected function _consult($url)
	{
		$client = new Zend_Http_Client($url);
		$client->setMethod(Zend_Http_Client::GET);
		
		$request = $client->request();
		$body = $request->getBody();

		if(!$this->helper()->isXml($body)) {
			return;
		}

		return $body;
	}

}