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

class OsStudios_PagSeguroApi_Model_Returns extends OsStudios_PagSeguroApi_Model_Abstract
{

	/**
	 * Updates a single transaction by return or consulting process
	 *
	 * @param Varien_Simplexml_Config $xml
	 *
	 * @return (boolean)
	 */
	public function updateSingleTransaction(Varien_Simplexml_Config $xml, $receivedFrom = 1)
	{
		$transaction = Mage::getModel('pagseguroapi/returns_transaction');
		$transaction->setReceivedFrom($receivedFrom)->importData($xml);
		
		if($transaction->getIsValid()) {
			$this->_updatePaymentHistory($transaction);

			if($this->getConfigData('automatically_change_orders')) {
				$this->_updateOrderByTransaction($transaction);
			}

			return true;
		}

		return false;
	}


	/**
	 * Updates the order according to transaction
	 *
	 * @param OsStudios_PagSeguroApi_Model_Returns_Transaction $transaction
	 *
	 * @return OsStudios_PagSeguroApi_Model_Returns
	 */
	protected function _updateOrderByTransaction(OsStudios_PagSeguroApi_Model_Returns_Transaction $transaction)
	{

		$order = $transaction->getOrder();

		//$transaction->setStatus(3);

		switch ((int) $transaction->getStatus()) {
			case 1:
			case 2:
				/**
				 * 1: Aguardando pagamento: o comprador iniciou a transação, mas até o momento o PagSeguro não recebeu nenhuma informação sobre o pagamento.
				 * 2: Em análise: o comprador optou por pagar com um cartão de crédito e o PagSeguro está analisando o risco da transação.
				 */

				if($transaction->getPaymentMethod()->getType() == 2) {
					if($this->getConfigData('automatically_hold_orders_for_billet')) {
						try {
							if($order->canHold()) {
								Mage::dispatchEvent('osstudios_pagseguroapi_return_order_hold_before', array('order' => $order, 'transaction' => $transaction));
							    $order->hold();
							    //$order->addStatusHistoryComment($this->helper()->__('Automatically holded by PagSeguroApi. Payment method is billet.'), false);
							    $order->save();
							    Mage::dispatchEvent('osstudios_pagseguroapi_return_order_hold_after', array('order' => $order, 'transaction' => $transaction));
							}
						} catch (Exception $e) {
							Mage::log($this->helper()->__('PagSeguroApi: Exception occurred when trying to hold order automatically. Exception message: %s.', $e->getMessage()), null, 'pagseguroapi_returns_exceptions.log');
						}
					}
				}				

				break;
			case 3:
			case 4:
				/**
				 * 3: Paga: a transação foi paga pelo comprador e o PagSeguro já recebeu uma confirmação da instituição financeira responsável pelo processamento. 
				 * 4: Disponível: a transação foi paga e chegou ao final de seu prazo de liberação sem ter sido retornada e sem que haja nenhuma disputa aberta. 
				 */

				if($this->getConfigData('automatically_invoice_orders')) {
					try {
						if($order->canUnhold()) {
						    $order->unhold();
						}
						if(!$order->canInvoice()) {
		                    $order->addStatusHistoryComment('PagSeguroApi: Order cannot be invoiced automatically.', false)->save();
		                    break;
		                }
		                Mage::dispatchEvent('osstudios_pagseguroapi_return_order_invoice_before', array('order' => $order, 'transaction' => $transaction));
						//START Handle Invoice
						$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
						$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
						$invoice->register();
						$invoice->getOrder()->setCustomerNoteNotify(false);
						$invoice->getOrder()->setIsInProcess(true);
						$transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
						$transactionSave->save();
						$order->addStatusHistoryComment($this->helper()->__('PagSeguroApi: Automatically invoiced by PagSeguroApi. PagSeguro confirmed the payment.'), false);
						$order->save();
						//END Handle Invoice
						Mage::dispatchEvent('osstudios_pagseguroapi_return_order_invoice_after', array('order' => $order, 'transaction' => $transaction));
					} catch (Exception $e) {
						Mage::log($this->helper()->__('PagSeguroApi: Exception occurred when trying to invoice order automatically. Exception message: %s.', $e->getMessage()), null, 'pagseguroapi_returns_exceptions.log');
					}
				}

				break;
			case 5:
				/**
				 * 5: Em disputa: o comprador, dentro do prazo de liberação da transação, abriu uma disputa. 
				 */

				break;
			case 6:
			case 7:
				/**
				 * 6: Devolvida: o valor da transação foi devolvido para o comprador. 
				 * 7: Cancelada: a transação foi cancelada sem ter sido finalizada. 
				 */

				if($this->getConfigData('automatically_cancel_orders')) {
					try {
						if($order->canUnhold()) {
						    $order->unhold();
						}
						if($order->canCancel()) {
							Mage::dispatchEvent('osstudios_pagseguroapi_return_order_cancel_before', array('order' => $order, 'transaction' => $transaction));
							$order->getPayment()->cancel();
							$order->registerCancellation($this->helper()->__('PagSeguroApi: Automatically canceled by PagSeguroApi. PagSeguro has canceled the payment.'));
							Mage::dispatchEvent('order_cancel_after', array('order' => $this));
							$order->save();
						} else {
							if($order->getState() != Mage_Sales_Model_Order::STATE_CANCELED) {
								$order->addStatusHistoryComment($this->helper()->__('PagSeguroApi: The order could not be automatically canceled by PagSeguroApi. PagSeguro has canceled the payment.'), false)->save();
							}
						}
					} catch (Exception $e) {
						Mage::log($this->helper()->__('PagSeguroApi: Exception occurred when trying to cancel order automatically. Exception message: %s.', $e->getMessage()), null, 'pagseguroapi_return_exceptions.log');
					}
				}

				break;
		}
	}


	/**
	 * Updates the history with these new information
	 *
	 * @param OsStudios_PagSeguroApi_Model_Returns_Transaction $transaction
	 *
	 * @return OsStudios_PagSeguroApi_Model_Returns
	 */
	protected function _updatePaymentHistory(OsStudios_PagSeguroApi_Model_Returns_Transaction $transaction, $forceUpdate = true)
	{
		try {
			$history = Mage::getModel('pagseguroapi/payment_history')->load($transaction->getOrder()->getEntityId(), 'order_id');

			if($history->getHistoryId()) {
				$history->setPagseguroTransactionId($transaction->getCode())
						->setPagseguroTransactionStatus($transaction->getStatus())
						->setPagseguroTransactionFeeAmount($transaction->getFeeAmount())
						->setPagseguroPaymentMethodType($transaction->getPaymentMethod()->getType())
						->setPagseguroPaymentMethodCode($transaction->getPaymentMethod()->getCode())
						->setPagseguroPaymentInstallmentCount($transaction->getInstallmentCount())
						->setUpdatedAt(now());

				$history->save();
			}
		} catch (Exception $e) {
			/* @todo */
		}

		return $this;
	}


}