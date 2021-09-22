<?php 
namespace Magecomp\Qtydropdown\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const QTYDROPDOWN_MINIMUM_QTY = 'qtydropdown/generalsettings/minvalue';
	const QTYDROPDOWN_MAXIMUM_QTY = 'qtydropdown/generalsettings/maxvalue';
	const QTYDROPDOWN_INCREAMENT_VALUE = 'qtydropdown/generalsettings/qtyincreament';
	const QTYDROPDOWN_CUSTOM_VALUE = 'qtydropdown/generalsettings/customvalue';
	const QTYDROPDOWN_QTY_TYPE = 'qtydropdown/generalsettings/qtytype';
	
	public function getQtyType()
	{
		return $this->scopeConfig->getValue(
            self::QTYDROPDOWN_QTY_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getMinimumQty()
	{
		return $this->scopeConfig->getValue(
            self::QTYDROPDOWN_MINIMUM_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getMaximumQty()
	{
		return $this->scopeConfig->getValue(
            self::QTYDROPDOWN_MAXIMUM_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getIncrementValue()
	{
		return $this->scopeConfig->getValue(
            self::QTYDROPDOWN_INCREAMENT_VALUE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getCustomText()
	{
		return $this->scopeConfig->getValue(
            self::QTYDROPDOWN_CUSTOM_VALUE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
	}
	public function getCustomValue()
	{
		$values=$this->getCustomText();
		return explode(",",$values);
	}
	
}