<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Simple Shipping Estimate, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
/**
 * Observer class, modifies shipping block on cart page
 *
 * Class Plugincompany_Simpleshippingestimate_Model_Observer
 */
class Plugincompany_Simpleshippingestimate_Model_Observer
{

    /**
     * Changes the cart shipping block according to config option
     *
     * @param $observer
     */
    public function controllerActionLayoutGenerateXmlBefore($observer)
    {
        //check if extension is enabled
        if (Mage::getStoreConfig('simpleshippingestimate/settings/active')) {

            //option completely remove shipping block
            if (Mage::getStoreConfig('simpleshippingestimate/settings/removeblock') == 2) {
                $layout = $observer->getEvent()->getLayout();
                $layout->getUpdate()->addUpdate('<remove name="checkout.cart.shipping" />');
            } //option replace with simple dropdown
            elseif (Mage::getStoreConfig('simpleshippingestimate/settings/removeblock') == 1) {
                $layout = $observer->getEvent()->getLayout();
                $layout->getUpdate()->addUpdate('<reference name="checkout.cart.shipping">
                    <action method="setTemplate">
                        <template>plugincompany/simpleshippingestimate/cartcalculator.phtml</template>
                    </action>
                </reference>" />');
            }

        }
    }

    /**
     * Replaces default shipping tax block template
     * Adds 'estimated' to the shipping cost name
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function coreBlockAbstractToHtmlBefore(Varien_Event_Observer $observer)
    {
        //if already checked, no need to run check again
        if (Mage::registry('pcnocartcontroller')) {
            return false;
        }

        $controller = Mage::app()->getRequest()->getControllerName();
        if ($controller == 'cart') {
            $block = $observer->getEvent()->getBlock();
            if ($block instanceof Mage_Tax_Block_Checkout_Shipping) {
                //set custom template for 'estimated' shipping cost title
                $block->setTemplate('plugincompany/simpleshippingestimate/tax/checkout/shipping.phtml');
            }
        } else {
            Mage::register('pcnocartcontroller', true);
        }
    }

}
