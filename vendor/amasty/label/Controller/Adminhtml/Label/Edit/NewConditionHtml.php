<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Controller\Adminhtml\Label\Edit;

use Amasty\Label\Controller\Adminhtml\Label\Edit;
use Amasty\Label\Model\Rule;
use Amasty\Label\Model\Rule\Condition\Factory as ConditionFactory;
use Amasty\Label\Model\Rule\Factory as RuleFactory;
use Amasty\Label\Ui\DataProvider\Label\Modifiers\Form\AddProductConditionsFormContent;
use InvalidArgumentException as InvalidArgumentException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

class NewConditionHtml extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = Edit::ADMIN_RESOURCE;

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        Context $context,
        ConditionFactory $conditionFactory,
        RuleFactory $ruleFactory
    ) {
        $this->conditionFactory = $conditionFactory;
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode(
            '|',
            str_replace(
                '-',
                '/',
                $this->getRequest()->getParam('type')
            )
        );
        $type = $typeArr[0];

        try {
            $model = $this->conditionFactory->create($type);
            $model->setId($id)->setType($type);
            $rule = $this->ruleFactory->create(Rule::class);
            $model->setRule($rule)->setPrefix('conditions');

            if (!empty($typeArr[1])) {
                $model->setAttribute($typeArr[1]);
            }

            $model->setJsFormObject(
                $this->getRequest()->getParam('form') ?: AddProductConditionsFormContent::CONDITIONS_ID
            );
            $model->setFormName(AddProductConditionsFormContent::FORM_NAME);
            $resultHtml = $model->asHtmlRecursive();
        } catch (InvalidArgumentException $e) {
            $resultHtml = '';
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents($resultHtml);

        return $result;
    }
}
