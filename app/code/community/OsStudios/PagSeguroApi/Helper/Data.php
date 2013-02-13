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
     * Calcula preço da parcela desejada, de acordo com o valor informado e até
     * quantas parcelas sem juros são disponibilizadas. Retorna um array com o
     * valor total, o valor da parcela e uma mensagem extra.
     */
    public function calculateRate($valor_original, $parcelas_sem_juros = 0, $intervalos = 1, $recalcula = false, $juros = 0.0199) {
    
        $parcelas = $intervalos;
        if ($parcelas_sem_juros > 1 and $parcelas <= $parcelas_sem_juros) {
            $parcelas = $parcelas_sem_juros;
        }
    
        if ($juros > 1) {
            $juros /= 100;
        }
    
        $msg_extra = '';
    
        $valor_total = $valor_original;
        if ($intervalos == 1 and $parcelas_sem_juros < 1) {
            $valor_parcela = $valor_original;
            $msg_extra = 'Sem juros';
        } else {
            if ($parcelas <= $parcelas_sem_juros or $parcelas_sem_juros < 1) {
                if ($parcelas_sem_juros > 1) {
                    $msg_extra = 'Sem juros';
                }
            } else {
                if ($juros == 0) {
                    $valor_parcela = $valor_original / $intervalos;
                } else {
                    if ($recalcula) {
                        $valor_parcela = ($valor_original * $juros) / (1 - pow(1 / (1 + $juros), $parcelas_sem_juros));
                        $valor_total = $valor_parcela * $parcelas_sem_juros;
                    }
                    $parcelas -= $parcelas_sem_juros;
                }
            }
            if ($juros != 0 and ($recalcula or $intervalos > $parcelas_sem_juros)) {
                $valor_parcela = ($valor_total * $juros) / (1 - pow(1 / (1 + $juros), $parcelas));
                $valor_total = $valor_parcela * $parcelas;
            }
            $valor_parcela = $valor_total / $intervalos;
        }
    
        return array($valor_total, $valor_parcela, $msg_extra);
    }
    
    
    /**
     * Calcula preço à vista com desconto, de acordo com o valor informado e até
     * quantas parcelas sem juros são disponibilizadas. Retorna um array com o
     * valor e a porcentagem de desconto.
     */
    public function calculateUpfrontPrice($valor_original, $parcelas_sem_juros, $juros = 0.0199) {
    
        if (preg_match('/^[-+]?[0-9]{1,3}(\.[0-9]{3})*(,[0-9]*)?$/', $valor_original)) {
            $valor_original = str_replace(".", "", $valor_original);
            $valor_original = str_replace(",", ".", $valor_original);
        }
    
        if ($juros > 1) {
            $juros /= 100;
        }
    
        $valor_a_vista = $valor_original;
        if ($parcelas_sem_juros >= 2 and $juros != 0) {
    
            $valor_parcela = $valor_a_vista / $parcelas_sem_juros;
            $valor_total = ($valor_parcela * (1 - pow(1 / (1 + $juros), $parcelas_sem_juros))) / $juros;
    
            $valor_a_vista = $valor_total;
        }
        
        $desconto = ceil((1 - $valor_a_vista / $valor_original) * 100);
        
        $valor_a_vista = number_format($valor_a_vista, 2, ",", "");
    
        return array($valor_a_vista, $desconto);
    }
    

    /**
     * Calcula planos de parcelamento de acordo com o valor e o número de parcelas
     * sem juros a serem exibidas.
     */
    public function calculateInstallments($valor_total_orig, $parcelas_sem_juros = 0, $recalcula = false, $juros = 0.0199, $parcelas_max = 18) {
    
        $installments = array();
    
        if (preg_match("/^[-+]?[0-9]{1,3}(\.[0-9]{3})*(,[0-9]*)?$/", $valor_total_orig)) {
            $valor_total_orig = str_replace(".", "", $valor_total_orig);
            $valor_total_orig = str_replace(",", ".", $valor_total_orig);
        }
    
        if ($parcelas_max < 1) {
            $parcelas_max = 1;
        }
    
        for ($parcels = 1; $parcels <= $parcelas_max; $parcels++) {
    
            list($valor_total, $valor_parcela, $msg_extra) = $this->calculateRate($valor_total_orig, $parcelas_sem_juros, $parcels, $recalcula, $juros);
            
            if ($parcels > 1 and $valor_parcela < self::PARCEL_MAX_VALUE) {
                break;
            }
    
            $valor_parcela = number_format($valor_parcela, 2, ",", "");
            $valor_total = number_format($valor_total, 2, ",", "");
            
            $installments[] = array(
                'valor_parcela' => $valor_parcela,
                'valor_total' => $valor_total,
                'msg_extra' => $msg_extra,
            );
        }
    
        return $installments;
    }
    

    /**
     * Retorna o menor valor de parcela sem juros possível,
     * de acordo o número máximo de parcelas sem juros.
     */
    public function getMinParcelWithoutRate($valor_total_orig, $parcelas_sem_juros = 0, $recalcula = false, $juros = 0.0199) {
        
        $minParcelValue = 0;
        $parcels = 1;
        
        if ($valor_total_orig > self::PARCEL_MAX_VALUE) {
            
            for (; $parcels <= $parcelas_sem_juros; $parcels++) {
                list($valor_total, $valor_parcela) = $this->calculateRate($valor_total_orig, $parcelas_sem_juros, $parcels, $recalcula, $juros);
                if ($parcels > 1 and $valor_parcela < self::PARCEL_MAX_VALUE) {
                    break;
                } else {
                    $minParcelValue = $valor_parcela;
                }
            }
            $parcels--;
            
        }
        
        $minParcelValue = number_format($minParcelValue, 2, ",", "");
        
        return array($minParcelValue, $parcels);
    }
    

    public function ceiling($value, $precision = 0) {
        return ceil($value * pow(10, $precision)) / pow(10, $precision);
    }
    

	/**
	 * trataTelefone
	 *
	 * @param string $tel   Telefone a ser tratado
	 *
	 * @return array
	 */
    function trataTelefone($tel)
    {
        $numeros = preg_replace('/\D/','', $tel);
        $tel     = substr($numeros, sizeof($numeros)-9);
        $ddd     = substr($numeros, sizeof($numeros)-11,2);
        return array($ddd, $tel);
    }
    

    /**
     * 
     * Registry any event/error log.
     * 
     * @return OsStudios_PagSeguro_Helper_Data
     * 
     * @param string $message
     */
    public function log($message)
    {
    	Mage::getSingleton('pagseguro/data')->log($message);
    	return $this;
    }
    
    
    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|Mage_Core_Model_Store $storeId
     *
     * @return mixed
     */
    public function getMethodConfigData($field, $methodCode = OsStudios_PagSeguro_Model_Payment::PAGSEGURO_METHOD_CODE_HPP, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore();
        }
        $path = 'payment/'.$methodCode.'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }
    
    
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