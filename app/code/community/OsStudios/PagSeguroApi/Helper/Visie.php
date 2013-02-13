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
 * PagSeguro Api Payment Visie Helper
 *
 */

class OsStudios_PagSeguroApi_Helper_Visie extends Mage_Core_Helper_Data
{
    
	/**
	 * trataEndereco
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $end   Endereço a ser tratado
	 *
	 * @return array
	 */
    function trataEndereco($end) {
        $numeros = $this->dados('numeros');
        $complementos = $this->dados('complementos');
        if ($this->ehBrasilia($end)) {
          $numero = 's/nº';
          list($endereco, $complemento) = $this->brasiliaSeparaComplemento($end);
        } else {
          $endereco = $end;
          $numero = 's/nº';
          $complemento = '';
          $quebrado = preg_split('/[-,]/', $end);
          if (sizeof($quebrado) == 3){
            list($endereco, $numero, $complemento) = $quebrado;
          } elseif (sizeof($quebrado) == 2) {
            list($endereco, $numero) = $quebrado;
          } else {
            list($endereco, $numero) = $this->buscaReversa($end);
          }
          $endereco = $this->tiraNumeroFinal($endereco);
          if ($complemento == '')
            list($numerob,$complemento) = $this->separaNumeroComplemento($numero);
        }
        return array($this->endtrim($endereco), $this->endtrim($numero), $this->endtrim($complemento));
    }
	

	/**
	 * separaNumeroComplemento
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $n   Número a ser tratado
	 *
	 * @return array
	 */
    function separaNumeroComplemento($n) {
        $semnumeros = $this->dados('semnumeros');
        $n = $this->endtrim($n);
        foreach ($semnumeros as $sn) {
          if ($n == $sn)return array($n, '');
          if (substr($n, 0, strlen($sn)) == $sn)
            return array(substr($n, 0, strlen($sn)), substr($n, strlen($sn)));
        }
        $q = preg_split('/\D/', $n);
        $pos = strlen($q[0]);
        return array(substr($n, 0, $pos), substr($n,$pos));
    }
    

	/**
     * endtrim
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
	 * Remove caracteres e espaços desnecessários
	 *
	 * @param string|int|double $e Texto que deseja alterar
	 * 
	 * @return string
	 */
    function endtrim($e){
        return preg_replace('/^\W+|\W+$/', '', $e);
    }
    

	/**
	 * brasiliaSeparaComplemento
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $end   Endereço a ser tratado
	 *
	 * @return array
	 */
    function brasiliaSeparaComplemento($end) {
        $complementos = $this->dados('complementos');
        foreach ($complementos as $c)
          if ($pos = strpos(strtolower($end), $c))
            return array(substr($end, 0 ,$pos), substr($end, $pos));
        return array($end, '');
    }
	

	/**
	 * tiraNumeroFinal
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $endereco   Endereço a ser tratado
	 *
	 * @return string
	 */
    function tiraNumeroFinal($endereco) {
        $numeros = $this->dados('numeros');
        foreach ($numeros as $n)
          foreach (array(" $n"," $n ") as $N)
          if (substr($endereco, -strlen($N)) == $N)
            return substr($endereco, 0, -strlen($N));
        return $endereco;
    }
    

	/**
	 * buscaReversa
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
     * Encontra o primeiro caractere númerico dentre os últimos 10 da string informada
     * e retorna a string separada na posição localizada
	 *
	 * @param string $texto   Texto a ser procurado
	 *
	 * @return array
	 */
    function buscaReversa($texto) {
        $encontrar = substr($texto, -10);
        for ($i = 0; $i < 10; $i++) {
          if (is_numeric(substr($encontrar, $i, 1))) {
            return array(
                substr($texto, 0, -10+$i),
                substr($texto, -10+$i)
                );
          }
        }
    }
    

	/**
	 * ehBrasilia
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
	 *
	 * @param string $end   Endereço a ser analisado
	 *
	 * @return bool
	 */
    function ehBrasilia($end) {
        $brasilias = $this->dados('brasilias');
        $naobrasilias = $this->dados('naobrasilias');
        $brasilia = false;
        foreach ($brasilias as $b)
          if (strpos(strtolower($end),$b) != false)
            $brasilia = true;
        if ($brasilia)
          foreach ($naobrasilias as $b)
            if (strpos(strtolower($end),$b) != false)
              $brasilia = false;
        return $brasilia;
    }

    
	/**
	 * dados
     * (Extraída da biblioteca PHP do PagSeguro produzida pela Visie)
     * 
     * Retorna dados auxiliares de acordo com o argumento passado,
     * que podem ser:
     * - 'complementos'
     * - 'brasilias'
     * - 'naobrasilias'
     * - 'sems'
     * - 'numeros'
     * - 'semnumeros'
	 *
	 * @param string $v   Código para escolha do retorno
	 *
	 * @return array
	 */
    function dados($v) {
        
    	$dados = array();
        $dados['complementos'] 		= array("casa", "ap", "apto", "apart", "frente", "fundos", "sala", "cj");
        $dados['brasilias'] 		= array("bloco", "setor", "quadra", "lote");
        $dados['naobrasilias'] 		= array("av", "avenida", "rua", "alameda", "al.", "travessa", "trv", "praça", "praca");
        $dados['sems'] 				= array("sem ", "s.", "s/", "s. ", "s/ ");
        $dados['numeros'] 			= array('n.º', 'nº', "numero", "num", "número", "núm", "n");
        $dados['semnumeros'] 		= array();
        
        foreach ($dados['numeros'] as $n)
          foreach ($dados['sems'] as $s)
            $dados['semnumeros'][] = "$s$n";
        return $dados[$v];
    }
    
}