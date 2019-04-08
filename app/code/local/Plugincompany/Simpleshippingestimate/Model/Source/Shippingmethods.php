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
 * Option source class for shipping methods
 *
 * Class Plugincompany_Simpleshippingestimate_Model_Source_Shippingmethods
 */
class Plugincompany_Simpleshippingestimate_Model_Source_Shippingmethods {

    /**
     * Generates option array with all available shipping methods for store view
     *
     * @return array
     */
    public function toOptionArray()
	{
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

		$options = array();

		foreach($methods as $_ccode => $_carrier)
		{
			$_methodOptions = array();
			if($_methods = $_carrier->getAllowedMethods())
			{
				foreach($_methods as $_mcode => $_method)
				{
					$_code = $_ccode . '_' . $_mcode;
					$_methodOptions['value'] = $_code;
					$_methodOptions['label'] = $_method;
				}

				if($_title = Mage::getStoreConfig("carriers/$_ccode/title")){
                    $_methodOptions['title'] = $_title;
                }
                if(!$_methodOptions['label'])
                    $_methodOptions['label'] = $_title;

				$options[] = $_methodOptions;
			}
		}

		return $options;
	}
}