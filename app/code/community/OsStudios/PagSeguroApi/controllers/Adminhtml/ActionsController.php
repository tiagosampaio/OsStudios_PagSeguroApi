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

class OsStudios_PagSeguroApi_Adminhtml_ActionsController extends OsStudios_PagSeguroApi_Controller_Adminhtml_Action
{

	public function updateordersAction()
	{
		try {
			$consulter = Mage::getModel('pagseguroapi/consulter');
			$consulter->massConsult();
		} catch (Exception $e) {
			Mage::getSingleton('admin/session')->addError($e->getMessage());
		}

		$this->_redirect('*/adminhtml_transaction/history');
	}

	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('pagseguroapi/actions');
    }

}