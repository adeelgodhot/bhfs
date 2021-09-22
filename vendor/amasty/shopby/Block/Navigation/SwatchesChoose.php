<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


declare(strict_types=1);

namespace Amasty\Shopby\Block\Navigation;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class SwatchesChoose extends Template
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Json
     */
    private $json;

    public function __construct(
        Template\Context $context,
        RequestInterface $request,
        Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->request = $request;
        $this->json = $json;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSwatchesByJson(): string
    {
        $result = [];
        $params = $this->request->getParams() ?: [];
        unset($params['id']);
        unset($params['amshopby']);
        foreach ($params as $code => $appliedValue) {
            if ($appliedValue && is_string($appliedValue)) {
                $appliedValue = $this->validateValues($appliedValue);

                $appliedValue = array_unique($appliedValue);
                foreach ($appliedValue as $value) {
                    $result[] = [$code => $value];
                }
            }
        }

        return $this->json->serialize($result);
    }

    public function validateValues(string $appliedValue): array
    {
        $appliedValue = explode(",", $appliedValue);
        $appliedValue = array_filter($appliedValue);

        return $appliedValue;
    }
}
