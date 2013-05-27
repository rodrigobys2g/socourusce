<?php

abstract class Esmart_Eav_Block_Form_Renderer_Abstract extends Mage_Core_Block_Template
{
    /**
     * Attribute instance
     *
     * @var Mage_Eav_Model_Attribute
     */
    protected $_attribute;

    /**
     * EAV Entity Model
     *
     * @var Mage_Core_Model_Abstract
     */
    protected $_entity;

    /**
     * Format for HTML elements id attribute
     *
     * @var string
     */
    protected $_fieldIdFormat   = '%1$s';

    /**
     * Format for HTML elements name attribute
     *
     * @var string
     */
    protected $_fieldNameFormat = '%1$s';

    /**
     * Hold the custom clases for HTML output
     * 
     * @var array
     */
    protected $_customClasses = array();

    /**
     * Set attribute instance
     *
     * @param Mage_Eav_Model_Attribute $attribute
     * @return Esmart_Eav_Block_Form_Renderer_Abstract
     */
    public function setAttributeObject(Mage_Eav_Model_Attribute $attribute)
    {
        $this->_attribute = $attribute;
        return $this;
    }

    /**
     * Return Attribute instance
     *
     * @return Mage_Eav_Model_Attribute
     */
    public function getAttributeObject()
    {
        return $this->_attribute;
    }

    /**
     * Set Entity object
     *
     * @param Mage_Core_Model_Abstract
     * @return Esmart_Eav_Block_Form_Renderer_Abstract
     */
    public function setEntity(Mage_Core_Model_Abstract $entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Return Entity object
     *
     * @return Mage_Core_Model_Abstract
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Return Data Form Filter or false
     *
     * @return Varien_Data_Form_Filter_Interface
     */
    protected function _getFormFilter()
    {
        $filterCode = $this->getAttributeObject()->getInputFilter();
        if ($filterCode) {
            $filterClass = 'Varien_Data_Form_Filter_' . ucfirst($filterCode);
            if ($filterCode == 'date') {
                $format = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
                $filter = new $filterClass($format);
            } else {
                $filter = new $filterClass();
            }
            return $filter;
        }
        return false;
    }

    /**
     * Apply output filter to value
     *
     * @param string $value
     * @return string
     */
    protected function _applyOutputFilter($value)
    {
        $filter = $this->_getFormFilter();
        if ($filter) {
            $value = $filter->outputFilter($value);
        }

        return $value;
    }

    /**
     * Return validate class by attribute input validation rule
     *
     * @return string|false
     */
    protected function _getInputValidateClass()
    {
        $class          = false;
        $validateRules  = $this->getAttributeObject()->getValidateRules();
        if (!empty($validateRules['input_validation'])) {
            switch ($validateRules['input_validation']) {
                case 'alphanumeric':
                    $class = 'validate-alphanum';
                    break;
                case 'numeric':
                    $class = 'validate-digits';
                    break;
                case 'alpha':
                    $class = 'validate-alpha';
                    break;
                case 'email':
                    $class = 'validate-email';
                    break;
                case 'url':
                    $class = 'validate-url';
                    break;
                case 'date':
                    // @todo DATE FORMAT
                    break;
            }
        }
        return $class;
    }

    /**
     * Return array of validate classes
     *
     * @param boolean $withRequired
     * @return array
     */
    protected function _getValidateClasses($withRequired = true)
    {
        $classes = array();
        if ($withRequired && $this->isRequired()) {
            $classes[] = 'required-entry';
        }
        $inputRuleClass = $this->_getInputValidateClass();
        if ($inputRuleClass) {
            $classes[] = $inputRuleClass;
        }
        return $classes;
    }

    /**
     * Return original entity value
     * Value didn't escape and filter
     *
     * @return string
     */
    public function getValue()
    {
        $value = $this->getEntity()->getData($this->getAttributeObject()->getAttributeCode());
        return $value;
    }

    /**
     * Return HTML id for element
     *
     * @param string|null $index
     * @return string
     */
    public function getHtmlId($index = null)
    {
        $format = $this->_fieldIdFormat;
        if (!is_null($index)) {
            $format .= '_%2$s';
        }
        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    /**
     * Return HTML id for element
     *
     * @param string|null $index
     * @return string
     */
    public function getFieldName($index = null)
    {
        $format = $this->_fieldNameFormat;
        if (!is_null($index)) {
            $format .= '[%2$s]';
        }
        return sprintf($format, $this->getAttributeObject()->getAttributeCode(), $index);
    }

    /**
     * Return HTML class attribute value
     * Validate and rules
     *
     * @return string
     */
    public function getHtmlClass()
    {
        $classes = $this->_getValidateClasses(true);

        if(count($customClasses = $this->_customClasses)>0) {
            $classes = array_merge($classes, $customClasses);
        }

        return empty($classes) ? '' : ' ' . implode(' ', $classes);
    }

    /**
     * Add new custom class
     * 
     * @param string $class
     */
    public function addClass($class = null)
    {
        if(!is_null($class) && !in_array($class, $this->_customClasses)) {
            $this->_customClasses[] = $class;
        }

        return $this;
    }

    /**
     * Check is attribute value required
     *
     * @return boolean
     */
    public function isRequired()
    {
        return $this->getAttributeObject()->getIsRequired();
    }

    /**
     * Return attribute label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getAttributeObject()->getStoreLabel();
    }

    /**
     * Set format for HTML element(s) id attribute
     *
     * @param string $format
     * @return Esmart_Eav_Block_Form_Renderer_Abstract
     */
    public function setFieldIdFormat($format)
    {
        $this->_fieldIdFormat = $format;
        return $this;
    }

    /**
     * Set format for HTML element(s) name attribute
     *
     * @param string $format
     * @return Esmart_Eav_Block_Form_Renderer_Abstract
     */
    public function setFieldNameFormat($format)
    {
        $this->_fieldNameFormat = $format;
        return $this;
    }
}
