<?php
/**
 * Smart Commerce do Brasil
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@e-smart.com.br so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.e-smart.com.br for more information.
 *
 * @category    Esmart
 * @package     Esmart_Logging
 * @author      Tiago Sampaio <tiago.sampaio@e-smart.com.br>
 * @copyright   Copyright (c) 2012 Smart Commerce do Brasil. (http://www.e-smart.com.br)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Admin Actions Log Grid
 *
 */
class Esmart_Logging_Block_Adminhtml_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('loggingLogGrid');
        $this->setDefaultSort('time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * PrepareCollection method.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getResourceModel('esmart_logging/event_collection'));
        return parent::_prepareCollection();
    }

    /**
     * Return grids url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    /**
     * Grid URL
     *
     * @param Mage_Catalog_Model_Product|Varien_Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/details', array('event_id' => $row->getId()));
    }

    /**
     * Configuration of grid
     *
     * @return Esmart_Logging_Block_Adminhtml_Index_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('time', array(
            'header'    => $this->__('Time'),
            'index'     => 'time',
            'type'      => 'datetime',
            'width'     => 160,
        ));

        $this->addColumn('event', array(
            'header'    => $this->__('Action Group'),
            'index'     => 'event_code',
            'type'      => 'options',
            'sortable'  => false,
            'options'   => Mage::getSingleton('esmart_logging/config')->getLabels(),
        ));

        $actions = array();
        foreach (Mage::getResourceSingleton('esmart_logging/event')->getAllFieldValues('action') as $action) {
            $actions[$action] = Mage::helper('esmart_logging')->getLoggingActionTranslatedLabel($action);
        }
        $this->addColumn('action', array(
            'header'    => $this->__('Action'),
            'index'     => 'action',
            'type'      => 'options',
            'options'   => $actions,
            'sortable'  => false,
            'width'     => 75,
        ));

        $this->addColumn('ip', array(
            'header'    => $this->__('IP Address'),
            'index'     => 'ip',
            'type'      => 'text',
            'filter'    => 'esmart_logging/adminhtml_grid_filter_ip',
            'renderer'  => 'adminhtml/widget_grid_column_renderer_ip',
            'sortable'  => false,
            'width'     => 125,
        ));

        $this->addColumn('user', array(
            'header'    => $this->__('Username'),
            'index'     => 'user',
            'type'      => 'text',
            'escape'    => true,
            'sortable'  => false,
            'filter'    => 'esmart_logging/adminhtml_grid_filter_user',
            'width'     => 150,
        ));

        $this->addColumn('status', array(
            'header'    => $this->__('Result'),
            'index'     => 'status',
            'sortable'  => false,
            'type'      => 'options',
            'options'   => array(
                Esmart_Logging_Model_Event::RESULT_SUCCESS => $this->__('Success'),
                Esmart_Logging_Model_Event::RESULT_FAILURE => $this->__('Failure'),
            ),
            'width'     => 100,
        ));

        $this->addColumn('fullaction', array(
            'header'   => $this->__('Full Action Name'),
            'index'    => 'fullaction',
            'sortable' => false,
            'type'     => 'text'
        ));

        $this->addColumn('info', array(
            'header'    => $this->__('Short Details'),
            'index'     => 'info',
            'type'      => 'text',
            'sortable'  => false,
            'filter'    => 'adminhtml/widget_grid_column_filter_text',
            'renderer'  => 'esmart_logging/adminhtml_grid_renderer_details',
            'width'     => 100,
        ));

        $this->addColumn('view', array(
            'header'  => $this->__('Full Details'),
            'width'   => 50,
            'type'    => 'action',
            'getter'  => 'getId',
            'actions' => array(array(
                'caption' => $this->__('View'),
                'url'     => array(
                    'base'   => '*/*/details',
                ),
                'field'   => 'event_id'
            )),
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));
        return $this;
    }
}
