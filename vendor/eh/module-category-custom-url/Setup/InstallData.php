<?php
/**
 * @author ExtensionHut Team
 * @copyright Copyright (c) 2019 ExtensionHut (https://www.extensionhut.com/)
 * @package EH_CategoryCustomUrl
 */

namespace EH\CategoryCustomUrl\Setup;
 
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
 
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    private $_eavSetupFactory;
 
    public function __construct(EavSetupFactory $_eavSetupFactory) {
        $this->_eavSetupFactory = $_eavSetupFactory;
    }
 
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'custom_link',
            [
                'type' => 'varchar',
                'label' => 'Custom Link',
                'group' => 'General Information',
                'input' => 'text',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required' => false,
                'user_defined' => true,
                'default' => null,
                'unique' => false,
				'sort_order' => 20
            ]
        );    
    }
}
