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

class OsStudios_PagSeguroApi_Model_Credentials extends OsStudios_PagSeguroApi_Model_Abstract
{
	
	/**
	 * PagSeguro Account E-mail
	 * 
	 * @var (string)
	 */
	protected $_accountEmail = null;
	

	/**
	 * PagSeguro Account Token
	 * 
	 * @var (string)
	 */
	protected $_token = null;
	
	
	/**
	 * Sets the credentials data to this model
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->_accountEmail = $this->getConfigData('account_email');
		$this->_token = $this->getConfigData('account_token');
		
		$data = array('account_email' => $this->_accountEmail, 'account_token' => $this->_token);
		
		$this->setData($data);
	}
	
}
