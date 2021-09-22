<?php
namespace Magecomp\Qtydropdown\Model\Config\Product;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
class Dropdowntype extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
		protected $optionFactory;
		public function getAllOptions()
		{		
			$this->_options=[['label'=>'Use General Configuration', 'value'=>0],
			['label'=>'Use Product Increment Value', 'value'=>1],
			['label'=>'Custom Value', 'value'=>2],
			['label'=>'None', 'value'=>3]];
			
			return $this->_options;
		}
}