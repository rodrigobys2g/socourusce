<?php

class Esmart_Customer_Block_Adminhtml_Customer_Address_Attribute_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize edit tabs
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('esmart_customer_address_attribute_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('esmart_customer')->__('Attribute Information'));
    }
}
