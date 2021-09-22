<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Model;

class Urlmanager extends \Magento\Framework\DataObject
{
    const URL_FOLLOW_UP_PREFIX = 'amasty_followup/email/';

    protected $_history;

    protected $_rule;

    /**
     * @var array
     */
    protected $_googleAnalyticsParams = array(
        'utm_source', 'utm_medium', 'utm_term',
        'utm_content', 'utm_campaign'
    );

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Url\Encoder
     */
    protected $encoder;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\Url
     */
    private $frontUrlModel;

    /**
     * Urlmanager constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Url\Encoder $encoder
     * @param RuleFactory $ruleFactory
     * @param \Magento\Framework\Url $frontUrlModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Url\Encoder $encoder,
        \Amasty\Followup\Model\RuleFactory $ruleFactory,
        \Magento\Framework\Url $frontUrlModel,
        array $data = []
    )
    {
        parent::__construct($data);
        $this->objectManager = $objectManager;
        $this->encoder = $encoder;
        $this->ruleFactory = $ruleFactory;
        $this->frontUrlModel = $frontUrlModel;
    }

    /**
     * @param $history
     * @return $this
     */
    public function init($history)
    {
        $this->_history = $history;
        $this->_rule = $this->ruleFactory->create()->load($history->getRuleId());
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRule()
    {
        return $this->_rule;
    }

    /**
     * @param array $params
     * @return array
     */
    protected function getParams(array $params = [])
    {
        $params["id"] = $this->_history->getId();
        $params["key"] = $this->_history->getPublicKey();

        foreach($this->_googleAnalyticsParams as $param){
            $val = $this->_rule->getData($param);

            if (!empty($val)){
                $params[$param] = $val;
            }
        }

        return $params;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function mageUrl($url)
    {
        return $this->frontUrlModel->getUrl(
            self::URL_FOLLOW_UP_PREFIX . 'url',
            $this->getParams([
                'mageUrl' => $this->encoder->encode($url),
            ])
        );
    }

    /**
     * @return mixed
     */
    public function unsubscribeUrl()
    {
        return $this->frontUrlModel->getUrl(
            self::URL_FOLLOW_UP_PREFIX . 'unsubscribe',
            $this->getParams()
        );
    }

    /**
     * @param $url
     * @return mixed
     */
    public function get($url)
    {
        return $this->frontUrlModel->getUrl(
            self::URL_FOLLOW_UP_PREFIX . 'url',
            $this->getParams([
                'url' => $this->encoder->encode($url),
            ])
        );
    }

}
