<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Controller\Adminhtml\Rule;

use Amasty\Base\Model\Serializer;
use Amasty\Followup\Helper\Data;
use Amasty\Followup\Model\RuleFactory;
use Amasty\Followup\Model\SalesRuleFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Psr\Log\LoggerInterface;

class Save extends \Amasty\Followup\Controller\Adminhtml\Rule
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        DateTime $date,
        Serializer $serializer,
        LoggerInterface $logger,
        RuleFactory $ruleFactory,
        SalesRuleFactory $salesRuleFactory,
        Data $helper
    ) {
        $this->date = $date;
        $this->serializer = $serializer;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->ruleFactory = $ruleFactory;

        parent::__construct(
            $context,
            $coreRegistry,
            $salesRuleFactory
        );
    }

    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();

            try {
                $model = $this->ruleFactory->create();
                $id = $this->getRequest()->getParam('rule_id');

                if ($id) {
                    $model->load($id);

                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong rule is specified.'));
                    }
                } else {
                    $data['schedule'] = [$this->getDefaultScheduleValue($data)];
                }

                if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                    $salesRule = $this->salesRuleFactory->create();
                    $salesRule->loadPost($data);
                    $data['conditions_serialized'] = $this->serializer->serialize(
                        $salesRule->getConditions()->asArray()
                    );
                    unset($data['conditions']);
                }

                if (isset($data['store_ids']) && is_array($data['store_ids'])) {
                    $data['stores'] = implode(',', $data['store_ids']);
                } else {
                    $data['stores'] = '';
                }

                if (isset($data['segments_ids']) && is_array($data['segments_ids'])) {
                    $data['segments'] = implode(',', $data['segments_ids']);
                } else {
                    $data['segments'] = '';
                }

                if (isset($data['customer_group_ids']) && is_array($data['customer_group_ids'])) {
                    $data['cust_groups'] = implode(',', $data['customer_group_ids']);
                } else {
                    $data['cust_groups'] = '';
                }

                if (isset($data['customer_date_event'])) {
                    $data['customer_date_event'] = $this->date->date("Y-m-d H:i:s", $data['customer_date_event']);
                }

                $model->setData($data);
                $this->prepareForSave($model);
                $this->_session->setPageData($model->getData());
                $model->save();
                $id = $model->getRuleId();

                if (isset($data['schedule'])) {
                    $model->setSchedule($data['schedule']);
                }

                if ($model->getSchedule()) {
                    $model->saveSchedule();
                } elseif ($id) {
                    $this->messageManager->addWarningMessage(
                        __('Please set Schedule.')
                    );
                    $this->_session->setPageData($data);

                    return $this->_redirect('amasty_followup/*/edit', ['id' => $model->getId()]);
                }

                $this->messageManager->addSuccess(__('You saved the rule.'));
                $this->_session->setPageData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_followup/*/edit', ['id' => $model->getId()]);

                    return;
                }

                $this->_redirect('amasty_followup/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('rule_id');

                if (!empty($id)) {
                    $this->_redirect('amasty_followup/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_followup/*/new');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->_session->setPageData($data);
                $this->_redirect('amasty_followup/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);

                return;
            }
        }
        $this->_redirect('amasty_followup/*/');
    }

    public function prepareForSave($model)
    {
        $fields = ['methods', 'cancel_event_type'];

        foreach ($fields as $f) {
            // convert data from array to string
            $val = $model->getData($f);
            $model->setData($f, '');

            if (is_array($val)) {
                // need commas to simplify sql query
                $model->setData($f, ',' . implode(',', $val) . ',');
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @return array
     */
    private function getDefaultScheduleValue($data)
    {
        $emailTemplateId = isset($data['start_event_type'])
            ? $this->helper->getEmailTemplatesCollection($data['start_event_type'])->getFirstItem()->getId() : '';

        return [
            'schedule_id' => '',
            'email_template_id' => $emailTemplateId,
            'delivery_time' => [
                'days' => '',
                'hours' => '',
                'minutes' => '5'
            ],
            'coupon' => [
            ]
        ];
    }
}
