<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Customer extends \Magento\Reports\Block\Adminhtml\Grid\Shopcart
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ){
        parent::__construct($context, $backendHelper);

        $this->setId('amasty_followup_rule_test');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);

        $this->objectManager = $objectManager;
    }

    protected function _prepareCollection()
    {
        $collection = $this->objectManager
            ->create('Magento\Customer\Model\ResourceModel\Customer\Collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _addColumns()
    {
        $this->addColumn('run', array(
            'header'    => '',
            'index'     =>'customer_name',
            'sortable'  =>false,
            'filter'    => false,
            'renderer'  => 'Amasty\Followup\Block\Adminhtml\Rule\Edit\Tab\Test\Renderer\Run',
            'align'     => 'center',
        ));

        $this->addColumn('entity_id', array(
            'header'    => __('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        $this->addColumn('firstname', array(
            'header'    => __('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => __('Last Name'),
            'index'     => 'lastname'
        ));

        $groups = $this->objectManager
            ->create('Magento\Customer\Model\ResourceModel\Group\Collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  __('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        $this->addColumn('Telephone', array(
            'header'    => __('Telephone'),
            'width'     => '100',
            'index'     => 'billing_telephone'
        ));

        $this->addColumn('billing_postcode', array(
            'header'    => __('ZIP'),
            'width'     => '90',
            'index'     => 'billing_postcode',
        ));

        $this->addColumn('billing_country_id', array(
            'header'    => __('Country'),
            'width'     => '100',
            'type'      => 'country',
            'index'     => 'billing_country_id',
        ));

        $this->addColumn('billing_region', array(
            'header'    => __('State/Province'),
            'width'     => '100',
            'index'     => 'billing_region',
        ));

        $this->addColumn('customer_since', array(
            'header'    => __('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        $websites = $this->objectManager
            ->create('Magento\Config\Model\Config\Source\Website\OptionHash')
            ->toOptionArray();


        if (!$this->_storeManager->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => __('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => $websites,
                'index'     => 'website_id',
            ));
        }
    }

    protected function _prepareColumns()
    {
        $this->_addColumns();

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}