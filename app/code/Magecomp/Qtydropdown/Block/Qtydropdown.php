<?php
namespace Magecomp\Qtydropdown\Block;
use \Magento\Framework\View\Element\Template\Context;
use \Magecomp\Qtydropdown\Helper\Data;

class Qtydropdown extends \Magento\Framework\View\Element\Template
{
	protected $_registry;
	
	public function __construct(Context $context,Data $helperData,\Magento\Framework\Registry $registry,array $data = [])
    {        
        $this->_helperData = $helperData;
		$this->_registry = $registry;
        parent::__construct($context);
    }
	public function getQtyData($defaultqty)
	{
		$_product = $this->_registry->registry('current_product');
		$html="";
		if($_product->getDropdownQtyValue()!=3)
		{
			  $html .= "<select name='qty' id='qty' title='Qty' class='qty'>";
		  
			  if($_product->getDropdownQtyValue()==0)
			   {
				  if($this->_helperData->getQtyType()==1)
				   {	
					   $i=0;
					   $values=$this->_helperData->getCustomValue();
					   while( $i < count($values)) 
					   {
						$html.="<option value=".$defaultqty * $values[$i].">".$defaultqty * $values[$i]." for ".$this->getCurrencyIcon().$values[$i]*$_product->getPrice()."</option>";
						$i++;
					   } 
				   }
				   else
				   {
					$i = $this->_helperData->getMinimumQty() ; 
					while( $i <= $this->_helperData->getMaximumQty()) 
					{ 
						$html.="<option value=".$defaultqty * $i.">".$defaultqty * $i." for ".$this->getCurrencyIcon().$i*$_product->getPrice()."</option>";
						$i=$i+$this->_helperData->getIncrementValue(); 
					} 
				   }
				}
			   else if($_product->getDropdownQtyValue()==2)
			   {
				   $i=0;
				   $values=explode(",",$_product->getCustomQtyValue());
				   while( $i < count($values)) 
				   { 
					$html.="<option value=".$defaultqty * $values[$i].">".$defaultqty * $values[$i]." for ".$this->getCurrencyIcon().$values[$i]*$_product->getPrice()."</option>";
					$i++;
				   }    
			   }
			   else
			   {
					$i = $this->_helperData->getMinimumQty(); 
					while( $i <= $this->_helperData->getMaximumQty()) 
					{ 
						$html.="<option value=".$defaultqty * $i.">".$defaultqty * $i." for ".$this->getCurrencyIcon().$i*$_product->getPrice()."</option>";
						$i=$i+$this->_helperData->getIncrementValue(); 
					} 
			   }
			   $html.="</select>";
		 } 
		 else 
		 { 
			   $html.= "<div class='control'> 
						  <input type='number' name='qty' id='qty' value='".$defaultqty * 1 ."' title='Qty' class='input-text qty' />
					   </div>";
		 } 
		return $html.="</select>";		
	}
	public function getQtyGroupData($defaultqty,$_product)
	{
		//$_product = $this->_registry->registry('current_product');
		$html="";
		if($_product->getDropdownQtyValue()!=3)
		{
			$html .= "<select name='super_group[".$_product->getId()."]' id='qty' title='Qty' class='qty group-qty-drop'>";

			if($_product->getDropdownQtyValue()==0)
			{
				if($this->_helperData->getQtyType()==1)
				{
					$i=0;
					$values=$this->_helperData->getCustomValue();
					while( $i < count($values))
					{
						$html.="<option value=".$defaultqty * $values[$i].">".$defaultqty * $values[$i]." for ".$this->getCurrencyIcon().$values[$i]*$_product->getPrice()."</option>";
						$i++;
					}
				}
				else
				{
					$i = $this->_helperData->getMinimumQty() ;
					while( $i <= $this->_helperData->getMaximumQty())
					{
						$html.="<option value=".$defaultqty * $i.">".$defaultqty * $i." for ".$this->getCurrencyIcon().$i*$_product->getPrice()."</option>";
						$i=$i+$this->_helperData->getIncrementValue();
					}
				}
			}
			else if($_product->getDropdownQtyValue()==2)
			{
				$i=0;
				$values=explode(",",$_product->getCustomQtyValue());
				while( $i < count($values))
				{
					$html.="<option value=".$defaultqty * $values[$i].">".$defaultqty * $values[$i]." for ".$this->getCurrencyIcon().$values[$i]*$_product->getPrice()."</option>";
					$i++;
				}
			}
			else
			{
				$i = $this->_helperData->getMinimumQty();
				while( $i <= $this->_helperData->getMaximumQty())
				{
					$html.="<option value=".$defaultqty * $i.">".$defaultqty * $i." for ".$this->getCurrencyIcon().$i*$_product->getPrice()."</option>";
					$i=$i+$this->_helperData->getIncrementValue();
				}
			}
			$html.="</select>";
		}
		else
		{
			$html.= "<div class='control'>
						  <input type='number' name='qty' id='qty' value='".$defaultqty * 1 ."' title='Qty' class='input-text qty' />
					   </div>";
		}
		return $html.="</select>";
	}

	public function getConfigQtyData($qty)
	{
		$html="";
		if($this->_helperData->getQtyType()==1)
		{
			 $i=0;
			 $values=$this->_helperData->getCustomValue();
			 while( $i < count($values)) 
			 { 
			  if($values[$i]==$qty)
			   $select="selected";
			  else
			   $select="";
				
			  $html.="<option value=".$values[$i]." $select >".$values[$i]."</option>";
			  $i++;
			 } 
		}
	    else
	    {
		 $i = $this->_helperData->getMinimumQty();
		 while( $i <= $this->_helperData->getMaximumQty()) 
		 { 
		  if($i==$qty)
		   $select="selected";
		  else
		   $select="";
			$html.="<option value=".$i." $select >".$i."</option>";
			$i=$i+$this->_helperData->getIncrementValue();
		 } 
	   }
	   return $html;
	}
	public function getProductIncrementQtyData($qty)
	{
		$html="";
		$i = $this->_helperData->getMinimumQty();
		while( $i <= $this->_helperData->getMaximumQty()) 
		{ 
		  if($i==$qty)
		   $select="selected";
		  else
		   $select=""; 
		  $html.="<option value=".$i." $select >".$i."</option>";
		  $i=$i+$this->_helperData->getIncrementValue();
		} 
		 return $html;
	}
	public function getListQtyData($dropdownqtyvalue,$price)
	{
		 $html="";
		 $html.='<select name="qty" id="qty" title="Qty" class="qty  magecomp-dropdown">';

		 if($dropdownqtyvalue==0)
		 {
			  if($this->_helperData->getQtyType()==1)
			   {	
				   $i=0;
				   $values=$this->_helperData->getCustomValue();
				   while( $i < count($values)) 
				   { 
					$html.="<option value=".$values[$i].">".$values[$i]." for ".$this->getCurrencyIcon().$values[$i]*$price."</option>";
					$i++;
				   } 
			   }
			   else
			   {
				$i = $this->_helperData->getMinimumQty();
				
				 while( $i <= $this->_helperData->getMaximumQty()) 
				 { 
					$html."<option value=".$i.">".$i." for ".$this->getCurrencyIcon().$i*$price."</option>";
					$i=$i+$this->_helperData->getIncrementValue(); 
				 } 
			   }
		   } 
		   else if($dropdownqtyvalue==1)
		   {
			  	 $i = $this->_helperData->getMinimumQty() ; 
				 while( $i <= $this->_helperData->getMaximumQty()) 
				 { 
					$html.="<option value=".$i.">".$i." for ".$this->getCurrencyIcon().$i*$price."</option>";
					$i=$i+$this->_helperData->getIncrementValue();
				} 
		   } 
		   $html.="</select>";
		   return $html;
	}
	public function getCurrencyIcon()
	{
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$currencysymbol = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
		$currencyCode = $currencysymbol->getStore()->getCurrentCurrencyCode();
		$currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode); 
		return $currencySymbol = $currency->getCurrencySymbol();
	}
}