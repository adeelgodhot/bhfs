<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Policy;

use Amasty\Gdpr\Model\Config;
use Amasty\Gdpr\Model\Consent\DataProvider\ConsentPolicyContentResolver;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class PolicyText extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var ConsentPolicyContentResolver
     */
    private $policyContentResolver;

    public function __construct(
        Context $context,
        Config $configProvider,
        FilterProvider $filterProvider,
        ConsentPolicyContentResolver $policyContentResolver
    ) {
        parent::__construct($context);
        $this->configProvider = $configProvider;
        $this->filterProvider = $filterProvider;
        $this->policyContentResolver = $policyContentResolver;
    }

    public function execute()
    {
        $policyData = [];
        $consentId = (int)$this->getRequest()->getParam('consent_id');

        if ($this->configProvider->isModuleEnabled()) {
            $policyData = $consentId
                ? $this->policyContentResolver->getConsentPolicyData($consentId)
                : $this->policyContentResolver->getGeneralPolicyData();

            if (isset($policyData[ConsentPolicyContentResolver::DATA_CONTENT])) {
                $policyData[ConsentPolicyContentResolver::DATA_CONTENT] = $this->filterProvider->getPageFilter()
                    ->filter((string)$policyData[ConsentPolicyContentResolver::DATA_CONTENT]);
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($policyData);
    }
}
