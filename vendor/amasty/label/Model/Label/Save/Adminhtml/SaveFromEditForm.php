<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


declare(strict_types=1);

namespace Amasty\Label\Model\Label\Save\Adminhtml;

use Amasty\Label\Api\Data\LabelFrontendSettingsInterface;
use Amasty\Label\Api\Data\LabelInterface;
use Amasty\Label\Api\Data\LabelInterfaceFactory;
use Amasty\Label\Api\LabelRepositoryInterface;
use Amasty\Label\Model\Label\Parts\Factory as PartFactory;
use Amasty\Label\Model\Label\Parts\MetaProvider;
use Amasty\Label\Model\Label\Save\DataPreprocessorInterface;
use Amasty\Label\Model\ResourceModel\Label\Collection;

class SaveFromEditForm
{
    /**
     * @var DataPreprocessorInterface
     */
    private $dataPreprocessor;

    /**
     * @var MetaProvider
     */
    private $metaProvider;

    /**
     * @var LabelInterfaceFactory
     */
    private $labelFactory;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var PartFactory
     */
    private $partFactory;

    public function __construct(
        DataPreprocessorInterface $dataPreprocessor,
        MetaProvider $metaProvider,
        LabelInterfaceFactory $labelFactory,
        LabelRepositoryInterface $labelRepository,
        PartFactory $partFactory
    ) {
        $this->dataPreprocessor = $dataPreprocessor;
        $this->metaProvider = $metaProvider;
        $this->labelFactory = $labelFactory;
        $this->labelRepository = $labelRepository;
        $this->partFactory = $partFactory;
    }

    public function execute(array $postData): LabelInterface
    {
        $processedData = $this->dataPreprocessor->process($postData);
        $label = $this->labelFactory->create();
        $label->setData($processedData);
        $label->unsExtensionAttributes();
        $this->hydrateExtensionAttributes($processedData, $label);

        foreach ([Collection::MODE_PDP, Collection::MODE_LIST] as $frontendSettingsMode) {
            if (isset(
                $processedData['extension_attributes'][MetaProvider::FRONTEND_SETTINGS_PART][$frontendSettingsMode]
            )) {
                /** @var LabelFrontendSettingsInterface $frontendSettings **/
                $frontendSettings = $this->createExtensionAttribute(
                    MetaProvider::FRONTEND_SETTINGS_PART,
                    $processedData['extension_attributes'][MetaProvider::FRONTEND_SETTINGS_PART][$frontendSettingsMode]
                );
                $frontendSettings->setType($frontendSettingsMode);
                $this->appendExtensionAttribute(
                    MetaProvider::FRONTEND_SETTINGS_PART,
                    $label,
                    $frontendSettings
                );
                $label->setHasDataChanges(true);
                $this->labelRepository->save($label);
            }
        }

        return $label;
    }

    /**
     * @param string $extensionAttributeCode
     * @param array $data
     *
     * @return object
     */
    private function createExtensionAttribute(string $extensionAttributeCode, array $data)
    {
        $extensionAttribute = $this->partFactory->createPart($extensionAttributeCode);
        $extensionAttribute->setData($data);

        return $extensionAttribute;
    }

    private function appendExtensionAttribute(
        string $extensionAttributePartCode,
        LabelInterface $label,
        $extensionAttribute
    ): void {
        $setter = $this->metaProvider->getSetter($extensionAttributePartCode);
        $labelExtensionAttributes = $label->getExtensionAttributes();

        if (method_exists($labelExtensionAttributes, $setter)) {
            $labelExtensionAttributes->{$setter}($extensionAttribute);
        }
    }

    private function hydrateExtensionAttributes(array $processedData, LabelInterface $label): void
    {
        foreach ($this->metaProvider->getAllPartsCodes() as $extensionAttributePartCode) {
            if ($extensionAttributePartCode !== MetaProvider::FRONTEND_SETTINGS_PART) {
                if (isset($processedData['extension_attributes'][$extensionAttributePartCode])) {
                    $extensionAttribute = $this->createExtensionAttribute(
                        $extensionAttributePartCode,
                        $processedData['extension_attributes'][$extensionAttributePartCode]
                    );
                    $this->appendExtensionAttribute($extensionAttributePartCode, $label, $extensionAttribute);
                }
            }
        }
    }
}
