<?php
namespace Magecomp\Qtydropdown\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
	private $eavSetupFactory;

	public function __construct(EavSetupFactory $eavSetupFactory)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}
	public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'dropdown_qty_value',
			[
				'type' => 'int',
				'label' => 'Quantity Dropdown Value',
				'input' => 'select',
				'source' => 'Magecomp\Qtydropdown\Model\Config\Product\Dropdowntype',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'sort_order' => 11,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'group' => 'Quantity Dropdown',
			]
		);
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'custom_qty_value',
			[
				'type' => 'text',
				'label' => 'Dropdown Custom Value',
				'input' => 'text',
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
				'visible' => true,
				'required' => false,
				'sort_order' => 12,
				'visible_on_front' => false,
				'used_in_product_listing' => true,
				'unique' => false,
				'group' => 'Quantity Dropdown',
			]
		);
	}
	
}