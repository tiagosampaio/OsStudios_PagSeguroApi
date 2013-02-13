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

class OsStudios_PagSeguroApi_Block_Api_Form extends Mage_Payment_Block_Form
{
    
    /**
     * Sets the template
     *
     * @return OsStudios_PagSeguroApi_Block_Api_Form
     */
    public function _construct() {
        parent::_construct();
        $this->setTemplate('osstudios/pagseguroapi/form.phtml');

        return $this;
    }


    /**
     * Whether the message will be shown
     *
     * @return boolean
     */
    public function getShowMessage()
    {
    	if(Mage::getSingleton('pagseguroapi/data')->getConfigData('message')) {
			return true;
		}
		return false;;
    }


    /**
     * Provides the message
     *
     * @return string
     */
    public function getMessage()
    {
    	return Mage::getSingleton('pagseguroapi/data')->getConfigData('message');
    }
    
}