<?php

/**
 * Madia Adyen Payment Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category	Madia
 * @package	Madia_Adyen
 * @copyright	Copyright (c) 2012 Madia (http://www.madia.nl)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Payment Gateway
 * @package    Madia_Adyen
 * @author     Omar,Muhsin <info@madia.nl>
 * @property   Madia B.V
 * @copyright  Copyright (c) 2012 Madia BV (http://www.madia.nl)
 */
class Madia_Adyen_Model_Authenticate extends Mage_Core_Model_Abstract {

    /**
     * @param type $actionName
     * @param type $varienObj
     * @return type 
     */
    public function authenticate($actionName, $varienObj) {
        $authStatus = false;
        switch ($actionName) {
            case 'success':
                $authStatus = $this->_signAuthenticate($varienObj);
                break;
            default:
                $authStatus = $this->_httpAuthenticate($varienObj);
                break;
        }
        try {
            if (false === $authStatus ) {
                throw new SoapFault('200', Mage::helper('adyen')->__('Username or Password is incorrect, please contact Adyen for support!'));            
            }
        } catch(SoapFault $e) {
            Mage::logException($e);
        }
        return $authStatus;
    }

    /**
     * @desc Authenticate using sha1 Merchant signature
     * @see success Action during checkout
     * @param Varien_Object $response
     */
    protected function _signAuthenticate(Varien_Object $response) {
        if($this->_getConfigData('demoMode')=== 'Y') {
        	$secretWord = $this->_getConfigData('secret_wordt', 'adyen_hpp');
        }else{
        	$secretWord = $this->_getConfigData('secret_wordp', 'adyen_hpp');
        }
        $sign = $response->getData('authResult') . $response->getData('pspReference') . $response->getData('merchantReference') . $response->getData('skinCode');
        $signMac = Zend_Crypt_Hmac::compute($secretWord, 'sha1', $sign);
        $localStringToHash = base64_encode(pack('H*', $signMac));
        if (strcmp($localStringToHash, $response->getData('merchantSig')) === 0) {
            return true;
        }
        return false;
    }

    /**
     * @desc Authenticate using http_auth
     * @see Notifications
     * @todo get rid of global variables here
     * @param Varien_Object $response
     */
    protected function _httpAuthenticate(Varien_Object $response) {
        $this->fixCgiHttpAuthentication(); //add cgi support
        $internalMerchantAccount = $this->_getConfigData('merchantAccount');
        $username = $this->_getConfigData('notification_username');
        $password = $this->_getConfigData('notification_password');
        $submitedMerchantAccount = $response->getData('merchantAccountCode');
        if (empty($submitedMerchantAccount) && empty($internalMerchantAccount)) {
            return false;
        }
        if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
            return false;
        }
        $accountCmp = strcmp($submitedMerchantAccount, $internalMerchantAccount);
        $usernameCmp = strcmp($_SERVER['PHP_AUTH_USER'], $username);
        $passwordCmp = strcmp($_SERVER['PHP_AUTH_PW'], $password);
        if ($accountCmp === 0 && $usernameCmp === 0 && $passwordCmp === 0) {
            return true;
        }
        return false;
    } 

    /**
     * Fix these global variables for the CGI
     */
    public function fixCgiHttpAuthentication() { // unsupported is $_SERVER['REMOTE_AUTHORIZATION']: as stated in manual :p
    	//Mage::log(print_r($_SERVER,true));
    	 
        if (isset($_SERVER['REDIRECT_REMOTE_AUTHORIZATION']) && $_SERVER['REDIRECT_REMOTE_AUTHORIZATION'] != '') { //pcd note: no idea who sets this
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode($_SERVER['REDIRECT_REMOTE_AUTHORIZATION']));
/* TODO: added pcd */
        } elseif(!empty($_SERVER['HTTP_AUTHORIZATION'])){ //pcd note: standard in magento?
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
/* end added pcd */
        } elseif (!empty($_SERVER['REMOTE_USER'])) { //pcd note: when cgi and .htaccess modrewrite patch is executed
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['REMOTE_USER'], 6)));
        } elseif (!empty($_SERVER['REDIRECT_REMOTE_USER'])) { //pcd note: no idea who sets this
            list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['REDIRECT_REMOTE_USER'], 6)));
        }
    }
    
    /**
     * @desc Give Default settings
     * @example $this->_getConfigData('demoMode','adyen_abstract')
     * @since 0.0.2
     * @param string $code
     */
    protected function _getConfigData($code, $paymentMethodCode = null, $storeId = null) {
        if (null === $storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
        if (empty($paymentMethodCode)) {
            return Mage::getStoreConfig("payment/adyen_abstract/$code", $storeId);
        }
        return Mage::getStoreConfig("payment/$paymentMethodCode/$code", $storeId);
    }    

}