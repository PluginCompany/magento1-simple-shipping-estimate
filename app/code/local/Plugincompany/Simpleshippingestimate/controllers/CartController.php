<?php
require_once 'Mage/Checkout/controllers/CartController.php';
/**
 * Class Plugincompany_Simpleshippingestimate_CartController
 *
 * Rewrites Mage_Checkout_CartController to enable initial shipping calculation
 */
class Plugincompany_Simpleshippingestimate_CartController extends Mage_Checkout_CartController {

    /**
     * Rewritten action for initial shipping estimation
     */
    public function indexAction()
    {
        //check if Simple Shipping Estimate extension is enabled
        if(!Mage::getStoreConfig('simpleshippingestimate/settings/active')){
            //if not enabled run default action
            return parent::indexAction();
        }

        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (version_compare(Mage::getVersion(), '1.7', '>=')) {
                //Magento 1.7 and above
                if (!$this->_getQuote()->validateMinimumAmount()) {
                    $minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                        ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                    $warning = Mage::getStoreConfig('sales/minimum_order/description')
                        ? Mage::getStoreConfig('sales/minimum_order/description')
                        : Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

                    $cart->getCheckoutSession()->addNotice($warning);
                }
            }
            else{
                //Magento below 1.7
                if (!$this->_getQuote()->validateMinimumAmount()) {
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                    $cart->getCheckoutSession()->addNotice($warning);
                }
            }
        }

        if (version_compare(Mage::getVersion(), '1.7', '>=')) {
            // Magento gt 1.7
            // Compose array of messages to add
            $messages = array();
            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {
                    // Escape HTML entities in quote message to prevent XSS
                    $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                    $messages[] = $message;
                }
            }
            $cart->getCheckoutSession()->addUniqueMessages($messages);
        }elseif (version_compare(Mage::getVersion(), '1.6', '>=')) {
            // Magento 1.6
            // Compose array of messages to add
            $messages = array();
            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {
                    $messages[] = $message;
                }
            }
            $cart->getCheckoutSession()->addUniqueMessages($messages);
        }else{
            // Magento 1.5 and lower
            foreach ($cart->getQuote()->getMessages() as $message) {
                if ($message) {
                    $cart->getCheckoutSession()->addMessage($message);
                }
            }
        }


      /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        $this->_getSession()->setCartWasUpdated(true);

        //RETRIEVE SHIPPING METHODS AND CALCULATE
        
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        $shippingAddress = $quote->getShippingAddress();
        $hasItems = $cart->getItemsCount();
        if ($hasItems && (!$shippingAddress->getCountryId() || !$shippingAddress->getShippingMethod())) {

            //add default values to quote object
            $quote->getShippingAddress()
                ->setCountryId($this->_getShippingCountryId())
                ->setCity($this->_getShippingCity())
                ->setPostcode($this->_getShippingPostcode())
                ->setRegionId($this->_getShippingRegionId())
            ;

            $quote
               ->getShippingAddress()
               ->setShippingMethod(Mage::getStoreConfig('simpleshippingestimate/shipping/method'));

            $shippingAddress->save();

            $this->_redirect('*/*/*');
            return;
        }

        Varien_Profiler::start(__METHOD__ . 'cart_display');
        $this
            ->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->_initLayoutMessages('catalog/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
        $this->renderLayout();
        Varien_Profiler::stop(__METHOD__ . 'cart_display');
    }
    
    protected function _getShippingCountryId()
    {
        if($this->_customerHasAddress()){
            return $this
                ->_getCustomerAddressForCalculation()
                ->getCountry();
        }
        return Mage::getStoreConfig('simpleshippingestimate/origin/country_id');
    }
    
    protected function _getShippingCity()
    {
        if($this->_customerHasAddress()){
            return $this
                ->_getCustomerAddressForCalculation()
                ->getCity();
        }
        return Mage::getStoreConfig('simpleshippingestimate/origin/city');
    }

    protected function _getShippingRegionId()
    {
        if($this->_customerHasAddress()){
            return $this
                ->_getCustomerAddressForCalculation()
                ->getRegionId();
        }
        return Mage::getStoreConfig('simpleshippingestimate/origin/region_id');
    }
    
    protected function _getShippingPostcode()
    {
        if($this->_customerHasAddress()){
            return $this
                ->_getCustomerAddressForCalculation()
                ->getPostcode();
        }
        return Mage::getStoreConfig('simpleshippingestimate/origin/postcode');
    }
    
    protected function _customerHasAddress()
    {
        return $this->_getCustomerAddressForCalculation();
    }
    
    protected function _getCustomerAddressForCalculation()
    {
        $customer = $this->_getCustomerFromSession();
        if($this->_currentCustomerHasShippingAddress()){
            return $customer->getDefaultShippingAddress();
        }
        return $customer->getDefaultBillingAddress();
    }
    
    protected function _getCustomerFromSession()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        return $customer;
    }
    
    protected function _currentCustomerHasShippingAddress()
    {
        if($addr = $this->_getCustomerFromSession()->getDefaultShippingAddress()){
            return $addr;
        }
        return false;
    }

}
