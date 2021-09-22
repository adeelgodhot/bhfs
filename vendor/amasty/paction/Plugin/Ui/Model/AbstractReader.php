<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Paction
 */


declare(strict_types=1);

namespace Amasty\Paction\Plugin\Ui\Model;

use Amasty\Paction\Model\CommandResolver;
use Amasty\Paction\Model\ConfigProvider;
use Magento\Catalog\Model\Product\AttributeSet\Options;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Ui\Component\Action;

class AbstractReader
{
    const UI_COMPONENT = 'Amasty_Paction/js/grid/tree-massactions';
    const ACTIONS_WITHOUT_INPUT = ['amdelete', 'removeimg', 'updateadvancedprices', 'removeoptions'];
    const ACTIONS_WITH_SELECT = ['unrelated', 'unupsell', 'uncrosssell'];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var array|null
     */
    private $attributeSets;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var CommandResolver
     */
    private $commandResolver;

    public function __construct(
        ConfigProvider $configProvider,
        Options $attributeSets,
        Visibility $visibility,
        CommandResolver $commandResolver
    ) {
        $this->configProvider = $configProvider;
        $this->attributeSets = $attributeSets->toOptionArray();
        $this->visibility = $visibility;
        $this->commandResolver = $commandResolver;
    }

    /**
     * @param array $result
     *
     * @return array
     */
    protected function addMassactions(array $result): array
    {
        if (isset($result['children']['listing_top']['children']['listing_massaction']['children'])
            && isset($result['children']['product_listing_data_source'])
        ) {
            $children = &$result['children']['listing_top']['children']['listing_massaction']['children'];
            $availableActions = $this->configProvider->getCommands();

            if (!empty($availableActions)) {
                foreach ($availableActions as $item) {
                    if (array_key_exists($item, $children)) {
                        continue;
                    }

                    $children[$item] = $this->generateElement($item);
                }
                $component = &$result['children']['listing_top']['children']['listing_massaction']['arguments']
                ['data']['item']['config']['item']['component']['value'];

                if ($component !== self::UI_COMPONENT) {
                    $component = self::UI_COMPONENT;
                }
            }
        }

        return $result;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    private function generateElement(string $name): array
    {
        $data = $this->commandResolver->getCommandDataByName($name);
        $placeholder = (array_key_exists('placeholder', $data)) ? $data['placeholder'] : '';

        $result = [
            'arguments' => [
                'data' => [
                    "name" => "data",
                    "xsi:type" => "array",
                    "item" => [
                        'config' => [
                            "name" => "config",
                            "xsi:type" => "array",
                            "item" => [
                                "component" => [
                                    "name" => "component",
                                    "xsi:type" => "string",
                                    "value" => "uiComponent"
                                ],
                                "amasty_actions" => [
                                    "name" => "component",
                                    "xsi:type" => "string",
                                    "value" => 'true'
                                ],
                                "confirm" => [
                                    "name" => "confirm",
                                    "xsi:type" => "array",
                                    "item" => [
                                        "title" => [
                                            "name" => "title",
                                            "xsi:type" => "string",
                                            "translate" => "true",
                                            "value" => $data['confirm_title']
                                        ],
                                        "message" => [
                                            "name" => "message",
                                            "xsi:type" => "string",
                                            "translate" => "true",
                                            "value" => $data['confirm_message']
                                        ]
                                    ]
                                ],
                                "type" => [
                                    "name" => "type",
                                    "xsi:type" => "string",
                                    "value" => 'amasty_' . $data['type']
                                ],
                                "label" => [
                                    "name" => "label",
                                    "xsi:type" => "string",
                                    "translate" => "true",
                                    "value" => $data['label']
                                ],
                                "url" => [
                                    "name" => "url",
                                    "xsi:type" => "url",
                                    "path" => $data['url']
                                ]

                            ]
                        ]
                    ]
                ],
                'actions' => [
                    "name" => "actions",
                    "xsi:type" => "array",
                    'item' => [
                        0 => [
                            "name" => "0",
                            "xsi:type" => "array",
                            "item" => [
                                "typefield" => [
                                    "name" => "type",
                                    "xsi:type" => "string",
                                    "value" => "textbox"
                                ],
                                "fieldLabel" => [
                                    "name" => "fieldLabel",
                                    "xsi:type" => "string",
                                    "value" => $data['fieldLabel']
                                ],
                                "placeholder" => [
                                    "name" => "placeholder",
                                    "xsi:type" => "string",
                                    "value" => $placeholder
                                ],
                                "label" => [
                                    "name" => "label",
                                    "xsi:type" => "string",
                                    "translate" => "true",
                                    "value" => ""
                                ],
                                "url" => [
                                    "name" => "url",
                                    "xsi:type" => "url",
                                    "path" => $data['url']
                                ],
                                "type" => [
                                    "name" => "type",
                                    "xsi:type" => "string",
                                    "value" => 'amasty_' . $data['type']
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'attributes' => [
                'class' => Action::class,
                'name' => $name
            ],
            'children' => []

        ];

        if (array_key_exists('hide_input', $data)) {
            $result['arguments']['actions']['item'][0]['item']['hide_input'] = [
                "name" => "hide_input",
                "xsi:type" => "string",
                "value" => '1'
            ];
        }

        if (strlen($name) <= 2
            || in_array($name, self::ACTIONS_WITHOUT_INPUT)
            || (isset($data['hide_input']) && $data['hide_input'] == 1)
        ) {
            unset($result['arguments']['actions']);
        }

        if (in_array($name, self::ACTIONS_WITH_SELECT)) {
            $result['arguments']['actions']['item'][0]['item']['typefield']['value'] = 'select';
            $result['arguments']['actions']['item'][0]['item']['child'] = [
                "name" => "child",
                "xsi:type" => "array",
                'item' => [
                    0 => [
                        "name" => "0",
                        "xsi:type" => "array",
                        "item" => [
                            "label" => [
                                "name" => "label",
                                "xsi:type" => "string",
                                "value" => __('Remove relations between selected products only')->render()
                            ],
                            "fieldvalue" => [
                                "name" => "fieldvalue",
                                "xsi:type" => "string",
                                "value" => '1'
                            ],
                        ]
                    ],
                    1 => [
                        "name" => "1",
                        "xsi:type" => "array",
                        "item" => [
                            "label" => [
                                "name" => "label",
                                "xsi:type" => "string",
                                "value" => __('Remove selected products from ALL relations in the catalog')->render()
                            ],
                            "fieldvalue" => [
                                "name" => "fieldvalue",
                                "xsi:type" => "string",
                                "value" => '2'
                            ],
                        ]
                    ],
                    2 => [
                        "name" => "2",
                        "xsi:type" => "array",
                        "item" => [
                            "label" => [
                                "name" => "label",
                                "xsi:type" => "string",
                                "value" => __('Remove all relations from selected products')->render()
                            ],
                            "fieldvalue" => [
                                "name" => "fieldvalue",
                                "xsi:type" => "string",
                                "value" => '3'
                            ],
                        ]
                    ]
                ]
            ];
        }

        if ($name == 'changeattributeset') {
            $result['arguments']['actions']['item'][0]['item']['typefield']['value'] = 'select';
            $result['arguments']['actions']['item'][0]['item']['child'] = [
                "name" => "child",
                "xsi:type" => "array",
                'item' => []
            ];
            $itemIndex = 0;

            foreach ($this->attributeSets as $attributeSet) {
                $result['arguments']['actions']['item'][0]['item']['child']['item'][$itemIndex] = [
                    "name" => $itemIndex,
                    "xsi:type" => "array",
                    "item" => [
                        "label" => [
                            "name" => "label",
                            "xsi:type" => "string",
                            "value" => $attributeSet['label']
                        ],
                        "fieldvalue" => [
                            "name" => "fieldvalue",
                            "xsi:type" => "string",
                            "value" => $attributeSet['value']
                        ],
                    ]
                ];
                $itemIndex++;
            }
        }

        if ($name == 'changevisibility') {
            $result['arguments']['actions']['item'][0]['item']['typefield']['value'] = 'select';
            $result['arguments']['actions']['item'][0]['item']['child'] = [
                "name" => "child",
                "xsi:type" => "array",
                'item' => []
            ];
            $itemIndex = 0;

            foreach ($this->visibility->getOptionArray() as $key => $visibility) {
                $result['arguments']['actions']['item'][0]['item']['child']['item'][$itemIndex] = [
                    "name" => $itemIndex,
                    "xsi:type" => "array",
                    "item" => [
                        "label" => [
                            "name" => "label",
                            "xsi:type" => "string",
                            "value" => $visibility->getText()
                        ],
                        "fieldvalue" => [
                            "name" => "fieldvalue",
                            "xsi:type" => "string",
                            "value" => (string)$key
                        ],
                    ]
                ];
                $itemIndex++;
            }
        }

        return $result;
    }
}
