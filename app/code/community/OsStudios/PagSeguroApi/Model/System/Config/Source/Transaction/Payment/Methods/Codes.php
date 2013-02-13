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

/**
 * PagSeguro Api Shipping Type Source
 *
 */

class OsStudios_PagSeguroApi_Model_System_Config_Source_Transaction_Payment_Methods_Codes extends OsStudios_PagSeguroApi_Model_System_Config_Source_Config
{
	
	public function toOptionArray()
	{
        $options = Mage::app()->getConfig()->getNode('default/osstudios_pagseguroapi/transaction/payment_methods/codes')->asArray();
        return $options;
	}

}