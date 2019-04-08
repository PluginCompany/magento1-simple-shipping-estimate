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
 * Config source class for calculation block options
 *
 * Class Plugincompany_Simpleshippingestimate_Model_Source_Calculateblock
 */
class Plugincompany_Simpleshippingestimate_Model_Source_Calculateblock{

    /**
     * Returns option array for shipping block config section
     * @return array
     */
    public function toOptionArray()
    {
        $options = array(
            array('value' => 1, 'label' => 'Replace block with shipping method dropdown'),
            array('value' => 2, 'label' => 'Completely remove block'),
            array('value' => 3, 'label' => 'Leave block as is'),
        );
        return $options;
    }
}