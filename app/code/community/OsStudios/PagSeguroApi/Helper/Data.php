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
 * PagSeguro Api Payment Data Helper
 *
 */

class OsStudios_PagSeguroApi_Helper_Data extends OsStudios_PagSeguroApi_Helper_Visie
{
    
    const PARCEL_MAX_VALUE = 5;
    
    
    /**
     * 
     * Checks if the content is an XML file
     * @param (mixed) $content
     * @return (bool)
     */
    public function isXml($content)
    {
    	libxml_use_internal_errors(true);
    	$doc = new DOMDocument('1.0', 'utf-8');
    	$doc->loadXML($content);
    	
    	$errors = libxml_get_errors(); 
	    if (empty($errors)) 
	    { 
	        return true; 
	    } 
	    return false; 
    }
    

    public function cleanStringToXml($string = null)
    {
        
        $chars = array(
            'a' => array('á', 'à', 'â', 'ã'),
            'A' => array('Á', 'À', 'Â', 'Ã'),
            'e' => array('é', 'è', 'ê'),
            'E' => array('É', 'È', 'Ê'),
            'i' => array('í', 'ì', 'î'),
            'I' => array('Í', 'Ì', 'Î'),
            'o' => array('ó', 'ò', 'ô', 'Õ'),
            'O' => array('Ó', 'Ò', 'Ô', 'Õ'),
            'u' => array('ú', 'ù', 'û'),
            'U' => array('Ú', 'Ù', 'Û'),
            'c' => array('ç'),
            'C' => array('Ç'),
        );
        
        foreach($chars as $char => $set) {
            $string = str_replace($set, $char, $string);
        }
        
        return preg_replace('/[^a-zA-Z0-9_ %\[\]\.\(\)%&-]/s', '', trim($string));
    }
    
    
    public function getRegionCode($regionString = null)
    {
        $matches = array(
            array('pattern' => '/^acre$/',                              'result' => 'AC'),
            array('pattern' => '/^alagoas$/',                           'result' => 'AL'),
            array('pattern' => '/^amap.?$/',                            'result' => 'AP'),
            array('pattern' => '/^amazona.?$/',                         'result' => 'AM'),
            array('pattern' => '/^bahia$/',                             'result' => 'BA'),
            array('pattern' => '/^cear.?$/',                            'result' => 'CE'),
            array('pattern' => '/^distrito.?federal$/',                 'result' => 'DF'),
            array('pattern' => '/^esp.?rito.?santo$/',                  'result' => 'ES'),
            array('pattern' => '/^goi.?s$/',                            'result' => 'GO'),
            array('pattern' => '/^maranh.?o$/',                         'result' => 'MA'),
            array('pattern' => '/^mato.?grosso$/',                      'result' => 'MT'),
            array('pattern' => '/^mato.?grosso.?do.?sul$/',             'result' => 'MS'),
            array('pattern' => '/^minas.?gerais$/',                     'result' => 'MG'),
            array('pattern' => '/^par.?$/',                             'result' => 'PA'),
            array('pattern' => '/^para.?ba$/',                          'result' => 'PB'),
            array('pattern' => '/^paran.?$/',                           'result' => 'PR'),
            array('pattern' => '/^pernambuco$/',                        'result' => 'PE'),
            array('pattern' => '/^piau.?$/',                            'result' => 'PI'),
            array('pattern' => '/^rio.?de.?janeiro$/',                  'result' => 'RJ'),
            array('pattern' => '/^rio.?grande.?do.?norte$/',            'result' => 'RN'),
            array('pattern' => '/^rio.?grande.?do.?sul$/',              'result' => 'RS'),
            array('pattern' => '/^rond.?nia$/',                         'result' => 'RO'),
            array('pattern' => '/^roraima$/',                           'result' => 'RR'),
            array('pattern' => '/^santa.?catarina$/',                   'result' => 'SC'),
            array('pattern' => '/^s.?o.?paulo$/',                       'result' => 'SP'),
            array('pattern' => '/^sergipe$/',                           'result' => 'SE'),
            array('pattern' => '/^tocantins$/',                         'result' => 'TO'),
        );
        
        if(($regionString = trim(strtolower($regionString)))) {
            foreach($matches as $match) {
                if(preg_match($match['pattern'], $regionString)) {
                    return strtoupper($match['result']);
                    break;
                }
            }
            
            return 'SP';
        }
    }


    /**
     * Whether the PagSeguro must be opened in other page
     *
     * @return (boolean)
     */
    public function openPagSeguroInOtherPage()
    {
        return (bool) Mage::getStoreConfigFlag('payment/pagseguro_api/open_in_other_page');
    }

    
    public function getPagSeguroRedirectUrl()
    {
        return Mage::getStoreConfig('payment/pagseguro_api/pagseguro_api_redirect_url');
    }
    
}