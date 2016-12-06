<?php

class Test_RuleType_Model_Observer
{

    const PRODUCT_ATTRIBUTE_CODE = 'product_attribute_code';

    public function newOptionForSelectSimpleAction(Varien_Event_Observer $observer)
    {
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();
        $dataOptions = array();

        foreach ($attributes as $attribute){
            $dataOptions[$attribute->getAttributeCode()] = $attribute->getAttributeCode();
        }

        $form = $observer->getForm();
        $simpleAction = $form->getElement('simple_action');
        $values = $simpleAction->getValues();
        $values[] = array(
            'value' => 'by_product_attribute',
            'label' => 'Fixed amount from product attribute',
        );
        $simpleAction->setValues($values);
        $fieldset = $form->getElement('action_fieldset');
        $field = $fieldset->addField(self::PRODUCT_ATTRIBUTE_CODE, 'select', array(
            'name' => self::PRODUCT_ATTRIBUTE_CODE,
            'required' => false,
            'label' => Mage::helper('salesrule')->__('Choose Attribute'),
            'options'    => $dataOptions,
        ), $form->getElement('simple_action')->getId());
        $field->setAfterElementHtml('<script>
            //< ![C
            
            if($("rule_simple_action").value == "by_product_attribute") {
                $("rule_product_attribute_code").parentElement.parentElement.show();
            } else {
                $("rule_product_attribute_code").parentElement.parentElement.hide();
            }
            $("rule_simple_action").on("change", function() {
                if($("rule_simple_action").value == "by_product_attribute") {
                    $("rule_product_attribute_code").parentElement.parentElement.show();
                } else { 
                    $("rule_product_attribute_code").parentElement.parentElement.hide(); 
                }
            });
            
            //]]>
            </script>');
    }

    public function caseForRuleTypeDiscountByProductAttribute(Varien_Event_Observer $observer)
    {
        $rule = $observer->getRule();
        $item = $observer-> getItem();
        $result = $observer-> getResult();
        $quote = $observer-> getQuote();

        $qty = $this->getItemQty($item, $rule);

        $itemPrice              = $this->getItemPrice($item);
        $baseItemPrice          = $this->getItemBasePrice($item);

        if ($rule->getSimpleAction() == 'by_product_attribute') {
            $atrName = $rule->getData(self::PRODUCT_ATTRIBUTE_CODE);
            $product_id = $item->getData('product')->getData('entity_id');
            $product=Mage::getModel('catalog/product')->load($product_id);
            $disAm = $product->getData($atrName);
            $quoteAmount = $quote->getStore()->convertPrice($disAm);
            $discountAmount    = $qty * ($quoteAmount);
            $baseDiscountAmount = $qty * ($baseItemPrice-$disAm);

            $result->setDiscountAmount($discountAmount);
            $result->setBaseDiscountAmount($baseDiscountAmount);
        }
    }

    private function getItemPrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        $calcPrice = $item->getCalculationPrice();
        return ($price !== null) ? $price : $calcPrice;
    }


    private function getItemBasePrice($item)
    {
        $price = $item->getDiscountCalculationPrice();
        return ($price !== null) ? $item->getBaseDiscountCalculationPrice() : $item->getBaseCalculationPrice();
    }


    private function getItemQty($item, $rule)
    {
        $qty = $item->getTotalQty();
        return $rule->getDiscountQty() ? min($qty, $rule->getDiscountQty()) : $qty;
    }
}
