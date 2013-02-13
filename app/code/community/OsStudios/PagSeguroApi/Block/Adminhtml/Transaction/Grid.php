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

class OsStudios_PagSeguroApi_Block_Adminhtml_Transaction_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId('transactionGrid');
		$this->setDefaultSort('created_at');
		$this->setDefaultDir('DESC');
		$this->setUseAjax(true);
		$this->setSaveParametersInSession(true);
	}


	protected function _prepareCollection()
	{
		$collection = Mage::getModel("pagseguroapi/returns_transaction")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}


	protected function _prepareColumns()
	{
		$this->addColumn('reference', array(
			'header' => Mage::helper('pagseguroapi')->__('Reference'),
			'index' => 'reference',
			'width' => '50px',
		));

		$this->addColumn('code', array(
			'header' => Mage::helper('pagseguroapi')->__('Transaction ID'),
			'index' => 'code',
		));

		$this->addColumn('type', array(
			'header' => Mage::helper('pagseguroapi')->__('Type'),
			'index' => 'type',
			'type' => 'options',
			'options' => Mage::getModel('pagseguroapi/system_config_source_transaction_types')->getAssociativeArray(),
		));

		$this->addColumn('payment_method_type', array(
			'header' => Mage::helper('pagseguroapi')->__('Payment Method Type'),
			'index' => 'payment_method_type',
			'type' => 'options',
			'options' => Mage::getModel('pagseguroapi/system_config_source_transaction_payment_methods_types')->getAssociativeArray(),
		));

		$this->addColumn('payment_method_code', array(
			'header' => Mage::helper('pagseguroapi')->__('Payment Method Code'),
			'index' => 'payment_method_code',
			'type' => 'options',
			'options' => Mage::getModel('pagseguroapi/system_config_source_transaction_payment_methods_codes')->getAssociativeArray(),
		));

		$this->addColumn('status', array(
			'header' => Mage::helper('pagseguroapi')->__('Status'),
			'index' => 'status',
			'type' => 'options',
			'options'=> Mage::getModel('pagseguroapi/system_config_source_transaction_status')->getAssociativeArray(),
			'width' => '150px',
		));

		$this->addColumn('fee_amount', array(
			'header' => Mage::helper('pagseguroapi')->__('Fee Amount'),
			'index' => 'fee_amount',
			'type' => 'currency',
			'width' => '50px',
		));

		$this->addColumn('installment_count', array(
			'header' => Mage::helper('pagseguroapi')->__('Installment Count'),
			'index' => 'installment_count',
			'width' => '50px',
			'align' => 'center',
			'type' => 'number',
		));

		$this->addColumn('received_from', array(
			'header' => Mage::helper('pagseguroapi')->__('Received From'),
			'index' => 'received_from',
			'type' => 'options',
			'options' => Mage::getModel('pagseguroapi/system_config_source_transaction_sources')->getAssociativeArray(),
			'width' => '50px',
		));

		$this->addColumn('last_event_date', array(
			'header'    => Mage::helper('pagseguroapi')->__('Last Event Date'),
			'index'     => 'last_event_date',
			'type'      => 'datetime',
			'width' => '50px',
		));

		$this->addColumn('created_at', array(
			'header' => Mage::helper('pagseguroapi')->__('Created At'),
			'index' => 'created_at',
			'type' => 'datetime',
			'width' => '50px',
		));

		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
	}


	public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }


	public function getRowUrl($row)
	{
	   return;
	}
	

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('id');
		$this->getMassactionBlock()->setFormFieldName('ids');
		$this->getMassactionBlock()->setUseSelectAll(true);
		$this->getMassactionBlock()->addItem('remove_transaction', array(
			 'label'	=> Mage::helper('pagseguroapi')->__('Remove Transaction(s)'),
			 'url'  	=> $this->getUrl('*/adminhtml_transaction/massRemove'),
			 'confirm' 	=> Mage::helper('pagseguroapi')->__('Are you sure?')
		));
		return $this;
	}

}