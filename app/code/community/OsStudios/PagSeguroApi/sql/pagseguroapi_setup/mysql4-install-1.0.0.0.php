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

$installer = $this;

$installer->startSetup();

$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable('pagseguroapi/payment_history')}`;
	CREATE  TABLE IF NOT EXISTS `{$this->getTable('pagseguroapi/payment_history')}` (
	  `history_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
	  `order_id` INT(10) NOT NULL ,
	  `order_increment_id` INT(10) NOT NULL ,
	  `pagseguro_payment_identifier_code` VARCHAR(36) NULL ,
	  `pagseguro_transaction_id` VARCHAR(36) NULL ,
	  `pagseguro_transaction_date` DATETIME NULL ,
	  `pagseguro_transaction_status` INT NULL DEFAULT 1,
	  `pagseguro_transaction_fee_amount` DECIMAL(12,4) NULL DEFAULT 0 ,
	  `pagseguro_payment_method_type` INT UNSIGNED NULL ,
	  `pagseguro_payment_method_code` INT UNSIGNED NULL ,
	  `pagseguro_payment_installment_count` SMALLINT(5) NULL ,
	  `times_redirected` INT UNSIGNED DEFAULT 0 ,
	  `created_at` DATETIME NULL ,
	  `updated_at` DATETIME NULL ,
	  PRIMARY KEY (`history_id`) )
	ENGINE = MyISAM
	DEFAULT CHARACTER SET = utf8
	COLLATE = utf8_general_ci
");

$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable('pagseguroapi/returns_transaction')}`;
	CREATE  TABLE IF NOT EXISTS `{$this->getTable('pagseguroapi/returns_transaction')}` (
	  `id` INT NOT NULL AUTO_INCREMENT ,
	  `order_id` VARCHAR(45) NOT NULL ,
	  `reference` INT(10) NOT NULL ,
	  `code` VARCHAR(36) NOT NULL ,
	  `type` SMALLINT(5) NOT NULL ,
	  `status` SMALLINT(5) NOT NULL ,
	  `last_event_date` DATETIME NULL ,
	  `payment_method_type` SMALLINT(5) NOT NULL ,
	  `payment_method_code` SMALLINT(5) NOT NULL ,
	  `gross_amount` DECIMAL(12,4) NULL DEFAULT 0 ,
	  `discount_amount` DECIMAL(12,4) NULL DEFAULT 0 ,
	  `fee_amount` DECIMAL(12,4) NULL DEFAULT 0 ,
	  `net_amount` DECIMAL(12,4) NULL DEFAULT 0 ,
	  `extra_amount` DECIMAL(12,4) NULL DEFAULT 0 ,
	  `installment_count` SMALLINT(5) NULL ,
	  `item_count` SMALLINT(5) NULL ,
	  `sender_name` VARCHAR(255) NOT NULL ,
	  `sender_email` VARCHAR(255) NOT NULL ,
	  `received_from` SMALLINT(5) NULL ,
	  `created_at` DATETIME NULL ,
	  PRIMARY KEY (`id`) )
	ENGINE = InnoDB
");

$installer->endSetup();