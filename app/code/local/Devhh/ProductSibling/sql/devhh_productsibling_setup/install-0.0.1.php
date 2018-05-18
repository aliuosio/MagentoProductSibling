<?php

/**
 * @package   Devhh_ProductListing
 * @author    Osiozekhai Aliu <aliu@dev-hh.de>
 * @copyright 2018 Devhh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      begeisternderwahlkampf.de
 */

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

//add product attributes
$catalogSetup = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$catalogSetup->addAttribute(
    Mage_Catalog_Model_Product::ENTITY, 'sibling_product_id',
    array(
        'group' => 'Sibling Product with different Tax',
        'frontend' => '',
        'label' => 'Sibling Product Id',
        'input' => 'text',
        'type' => 'int',
        'class' => '',
        'source' => '',
        'global' => true,
        'visible' => true,
        'required' => false,
        'user_defined' => true,
        'default' => '',
        'apply_to' => 'simple,virtual,configurable',
        'is_configurable' => 0,
        'visible_on_front' => false,
        'used_in_product_listing' => true,
        'is_used_for_price_rules' => false
    )
);

$installer->endSetup();