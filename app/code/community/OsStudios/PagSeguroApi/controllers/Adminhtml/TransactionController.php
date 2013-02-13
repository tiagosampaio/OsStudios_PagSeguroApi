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

class OsStudios_PagSeguroApi_Adminhtml_TransactionController extends OsStudios_PagSeguroApi_Controller_Adminhtml_Action
{

	/**
	 * View Transaction History
	 *
	 */
	public function historyAction()
	{
		$this->_title($this->__('PagSeguro API'))->_title($this->__('Transaction'))->_title($this->__('View History'));

		$this->loadLayout();
		$this->_initLayoutMessages('admin/session');
		$this->renderLayout();
	}


	/**
	 * Mass remove
	 *
	 */
	public function massRemoveAction()
	{
		try {
			$ids = $this->getRequest()->getPost('ids', array());
			foreach ($ids as $id) {
                  $model = Mage::getModel('pagseguroapi/returns_transaction');
				  $model->setId($id)->delete();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess($this->__('%s transaction(s) was successfully removed.', count($ids)));
		}
		catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*/history');
	}
	

	/**
	 * Export order grid to CSV format
	 *
	 */
	public function exportCsvAction()
	{
		$fileName   = 'osstudios_pagseguro_returns_transactions.csv';
		$grid       = $this->getLayout()->createBlock('pagseguroapi/adminhtml_transaction_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
	}


	/**
	 * Export order grid to Excel XML format
	 *
	 */
	public function exportExcelAction()
	{
		$fileName   = 'osstudios_pagseguro_returns_transactions.xml';
		$grid       = $this->getLayout()->createBlock('pagseguroapi/adminhtml_transaction_grid');
		$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
	}


	/**
	 * Used for ajax update in transaction history grid
	 *
	 */
	public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }


	/**
	 * ACL Checks
	 *
	 * @return boolean
	 */
	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('pagseguroapi/returns');
    }

}