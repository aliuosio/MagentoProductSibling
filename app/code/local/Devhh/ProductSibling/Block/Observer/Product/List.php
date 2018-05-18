<?php

/**
 * @package   Devhh_ProductSibling
 * @author    Osiozekhai Aliu <aliu@dev-hh.de>
 * @copyright 2018 Devhh
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      begeisternderwahlkampf.de
 */

/**
 * Class Devhh_ProductSibling_Block_Observer_Product_List
 */
class Devhh_ProductSibling_Block_Observer_Product_List
{

    const SIBLING_PRODUCT_ID = 'sibling_product_id';

    const ENTITY_ID = 'entity_id';

    const TAX_PERCENT = 'tax_percent';

    const TAX_CLASS_ID = 'tax_class_id';

    const LIFE_TIME = 86400;

    /** @var Mage_Catalog_Model_Resource_Product_Collection */
    private $collection;

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection|Object
     */
    private function getResourceCollection()
    {
        return Mage::getResourceModel('catalog/product_collection');
    }

    /**
     * @return Mage_Core_Model_Store
     * @throws Mage_Core_Model_Store_Exception
     */
    private function getStore()
    {
        return Mage::app()->getStore(Mage::app()->getStore()->getId());
    }

    /**
     * @return false|Mage_Core_Model_Abstract|Mage_Tax_Model_Calculation
     */
    private function getTaxCalculationModel()
    {
        return Mage::getModel('tax/calculation');
    }

    /**
     * @return Zend_Cache_Core
     */
    private function getCollectionCache()
    {
        return Mage::app()->getCache();
    }

    private function setCacheLifeTime()
    {
        $this->getCollectionCache()->setLifetime(self::LIFE_TIME);
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    public function addSiblingProductTaxLinkToCollection(Varien_Event_Observer $observer)
    {
        $this->collection = $observer
            ->getEvent()
            ->getCollection()
            ->addAttributeToSelect(self::SIBLING_PRODUCT_ID);

        $observer->getEvent()->setCollection(
            $this->addSiblingsDataToCollection()
        );

        return $this->prepareCollectionCache();;
    }

    public function prepareCollectionCache()
    {
        if (Mage::app()->useCache('collections')) {
            $this->setCacheLifeTime();
            $this->getCollection()->initCache(
                $this->getCollectionCache(),
                Mage_Catalog_Model_Category::CACHE_TAG,
                array('collections')
            );
        }

        return $this;
    }

    /**
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    private function addSiblingsDataToCollection()
    {
        foreach ($this->getCollection() as $item) {
            /** Mage_Catalog_Model_Product $item */
            if ($item->getData(self::SIBLING_PRODUCT_ID) !== null) {
                $this->setItemData($item, $this->getSiblings()[$item->getData(self::SIBLING_PRODUCT_ID)]);
            }
        }
    }

    /**
     * @param Mage_Catalog_Model_Product $item
     * @param Mage_Catalog_Model_Product $sibling
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    private function setItemData(Mage_Catalog_Model_Product $item, Mage_Catalog_Model_Product $sibling)
    {
        // set item tax percent START
        $item->setData(
            'tax_percent',
            $this->getTaxPercent($item->getData(self::TAX_CLASS_ID))
        );
        // set item tax percent END

        //set sibling product data
        $item->setSibling($sibling->getData());

        //set sibling product tax percent START
        $item->setSibling(
            $sibling->setData(
                'tax_percent',
                $this->getTaxPercent($sibling[self::TAX_CLASS_ID])
            )
        );
        //set sibling product tax percent END
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    private function getSiblings()
    {
        try {
            $siblingCollection = $this->getResourceCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter(
                    self::ENTITY_ID,
                    array('in' => $this->siblingIds())
                );

            return $siblingCollection->getItems();
        } catch (Exception $e) {
            Mage::log($e->getTraceAsString());
        }
    }

    /**
     * @return array
     */
    private function siblingIds()
    {
        $result = [];

        foreach ($this->getCollection() as $item) {
            /** Mage_Catalog_Model_Product $item */
            if ($item->getData(self::SIBLING_PRODUCT_ID) !== null) {
                array_push($result, $item->getData(self::SIBLING_PRODUCT_ID));
            }
        }

        return $result;
    }

    /**
     * @param $taxClassId
     * @return string
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    private function getTaxPercent($taxClassId)
    {
        $percent = $this->getTaxCalculationModel()
            ->getRate(
                $this->getTaxCalculation()->setProductClassId($taxClassId)
            );

        return sprintf('%d%%', $percent);
    }

    /**
     * @return Varien_Object
     * @throws Mage_Core_Model_Store_Exception
     * @throws Varien_Exception
     */
    private function getTaxCalculation()
    {
        return $this->getTaxCalculationModel()
            ->getRateRequest(null, null, null, $this->getStore());
    }

}