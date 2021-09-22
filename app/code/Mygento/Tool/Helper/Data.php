<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mygento\Tool\Helper;

use Mygento\Tool\Block\LayoutStructure;
use Magento\Framework\Registry;
use Magento\Framework\View\Layout as Layout;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\RendererFactory as PageConfigRendererFactory;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory as ReadFactory;
use Magento\Framework\RequireJs\Config\File\Collector\Aggregated as RequireJsFileSource;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Request\Http as Request;
use Magento\Backend\Model\Auth\Session;

/**
 * Mygendo Tool data helper
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_coreRegistry;
    protected $_renderedElements;
    protected $_containerElements;
    private $tree;
    private $layout;
    private $fileManager;
    private $blockGenerator;
    private $pageConfigRendererFactory;
    private $pageConfig;
    private $pageConfigRenderer;
    private $readFactory;
    private $requireJsFileSource;
    private $design;
    private $htmlContent;
    private $request;
    private $backendSession;

    const PARTIAL_CONFIG_TEMPLATE = <<<config
(function() {
%config%
require.config(config);
})();

config;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Registry $registry,
        Layout $layout,
        \Mygento\Tool\Model\FileManager $fileManager,
        \Magento\Framework\View\Layout\Generator\Block $blockGenerator,
        PageConfigRendererFactory $pageConfigRendererFactory,
        PageConfig $pageConfig,
        ReadFactory $readFactory,
        RequireJsFileSource $requireJsFileSource,
        \Magento\Framework\View\DesignInterface $design,
        Request $request,
        Session $backendSession
    ) {
        $this->_coreRegistry = $registry;
        $this->_renderedElements = [];
        $this->_containerElements = [];
        $this->tree = [];
        $this->htmlContent = '';
        $this->addNodeToTree(['id' => '0', 'text' => 'Layout', 'parent' => "#", 'data' => []]);
        //$this->tree[] = ['id' => 'head.additional', 'text' => 'additional', 'parent' => "#", 'data' => []];
        //$this->tree[] = ['id' => 'require.js', 'text' => 'require.js', 'parent' => "#", 'data' => []];

        $this->layout = $layout;
        $this->fileManager = $fileManager;
        $this->blockGenerator = $blockGenerator;
        $this->pageConfig = $pageConfig;
        $this->pageConfigRendererFactory = $pageConfigRendererFactory;
        $this->backendSession = $backendSession;

        $this->readFactory = $readFactory;
        $this->requireJsFileSource = $requireJsFileSource;
        $this->design = $design;
        $this->request = $request;

        parent::__construct($context);
    }

    public function isNoHint()
    {
        return $this->backendSession->isLoggedIn() || $this->request->getParam('nohint') != '';
    }
    public function isHint()
    {
        return $this->request->getParam('hint') != '';
    }
    protected function initPageConfigReader()
    {
        $this->pageConfigRenderer = $this->pageConfigRendererFactory->create(['pageConfig' => $this->pageConfig]);
    }

    private function getElementType($name)
    {
        if ($this->layout->isUiComponent($name)) {
            return 'UiComponent';
        } elseif ($this->layout->isBlock($name)) {
            return 'Block';
        } else {
            return 'Container';
        }

        return 'Container';
    }

    public function stripScript($html)
    {
        $scriptRegExp = '/\<script(.*?)\>((\W|\w)+)\<\/script\>/i';

        return preg_replace( $scriptRegExp, '', $html );
    }

    public function setHtmlContent($content)
    {
        $this->htmlContent = $content;
    }
    public function addNodeToRenderedList($nodeName)
    {
        $this->_renderedElements[] = $nodeName;
    }
    public function appendHtmlTreeByIdx($treeIdx , $html)
    {
        $node = $this->tree[$treeIdx];
        $node['data']['html'] .= $html;
        $this->tree[$treeIdx] = $node;
    }
    public function appendNodeToLayoutTree($data){
        $name = $data['name'];

        $type = $this->getElementType($name);
        $parent = $this->layout->getParentName($name);

        if(in_array($parent, array_keys($this->_containerElements)) && $data['html'] != ''){
            $this->appendHtmlTreeByIdx($this->_containerElements[$parent], $data['html']);
        }
        if($parent != 'root' && !in_array($parent, $this->_renderedElements) &&
            !$this->isNodeInBlockList($parent)){
            $treeIdx = $this->appendNodeToLayoutTree(['name' => $parent, 'html' => '']);
            $this->_containerElements[$parent] = $treeIdx;
        }

        $object = null;
        switch ($type){
            case 'Block':
                $object = $this->layout->getBlock($name);
                break;
            case 'UiComponent':
                $object = $this->layout->getUiComponent($name);
                break;
            default:
                $object = null;
        }

        $class = $object ? get_class($object) : '';
        $template = $object ? $object->getTemplate() : '';
        $templateFile = $object ? $object->getTemplateFile() : '';
        $html = $data['html'];
        $alias = $this->layout->getElementAlias($name);

        $parent = $parent != '' ? $parent : "0";

        $text = "{$name} [{$type}] [{$alias}]";

        $node = [];
        $node['id'] = $name;
        $node['text'] = $text;
        $node['parent'] = $parent;
        $node['data'] = [
            'html' => $html,
            'class' => $class . '<br/>' . $text,
            'template' => $template,
            'template_file' => $templateFile
        ];

        if(in_array($name, $this->_renderedElements)){
            return $this->addNodeToTree($node, true);

        }

        return $this->addNodeToTree($node);
    }

    protected function collectRequireJsConfig()
    {
        $this->addNodeToTree([
            'id' => 'dev_requirejs_config',
            'text' => 'RequireJS Config',
            'parent' => "0",
            'data' => [
                'html' => '', 'class' => '', 'template' => '', 'template_file' => ''
            ]
        ]);

        $distributedConfig = '';
        $idx = 0;
        $customConfigFiles = $this->requireJsFileSource->getFiles($this->design->getDesignTheme(), "requirejs-config.js");
        foreach ($customConfigFiles as $file) {
            /** @var $fileReader \Magento\Framework\Filesystem\File\Read */
            $fileReader = $this->readFactory->create($file->getFilename(), DriverPool::FILE);
            $config = $fileReader->readAll($file->getName());
            $distributedConfig = str_replace(
                ['%config%', '%context%'],
                [$config, $file->getModule()],
                self::PARTIAL_CONFIG_TEMPLATE
            );

            $this->addNodeToTree([
                'id' => "dev_requirejs_config_{$idx}",
                'text' => $file->getFilename(),
                'parent' => "dev_requirejs_config",
                'data' => [
                    'html' => $distributedConfig, 'class' => '', 'template' => '', 'template_file' => ''
                ]
            ]);
            $idx++;
        }
    }

    protected function beforeMakeLayoutTree()
    {
        $this->initPageConfigReader();

        if($this->layout->hasElement('require.js')){
            $this->layout->renderElement('require.js');
        }
        if($this->layout->hasElement('head.additional')){
            $this->layout->renderElement('head.additional');
        }

        $config = $this->pageConfig;
        $headContent = $this->pageConfigRenderer->renderHeadContent();
        $htmlAttributes = $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HTML);
        $headAttributes = $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_HEAD);
        $bodyAttributes = $this->pageConfigRenderer->renderElementAttributes($config::ELEMENT_TYPE_BODY);

        $this->addNodeToTree([
            'id' => 'dev_head_content',
            'text' => 'Head Content',
            'parent' => "0",
            'data' => [
                'html' => $headContent, 'class' => '', 'template' => '', 'template_file' => ''
            ]
        ]);

        $this->addNodeToTree([
            'id' => 'dev_html_attributes',
            'text' => 'Html Attributes',
            'parent' => "0",
            'data' => [
                'html' => $htmlAttributes, 'class' => '', 'template' => '', 'template_file' => ''
            ]
        ]);

        $this->addNodeToTree([
            'id' => 'dev_head_attributes',
            'text' => 'Head Attributes',
            'parent' => "0",
            'data' => [
                'html' => $headAttributes, 'class' => '', 'template' => '', 'template_file' => ''
            ]
        ]);

        $this->addNodeToTree([
            'id' => 'dev_body_attributes',
            'text' => 'Body Attributes',
            'parent' => "0",
            'data' => [
                'html' => $bodyAttributes, 'class' => '', 'template' => '', 'template_file' => ''
            ]
        ]);

        $this->collectRequireJsConfig();
    }

    public function addNodeToTree($node, $reset = false)
    {
        //$node['data']['html'] = $node['data']['raw_html'] = '';
        if($reset){
            foreach($this->tree as $key => $item){
                if($item['id'] == $node['id']){
                    $this->tree[$key] = $node;
                    unset($this->_containerElements[$node['id']]);
                    return $key;
                }
            }
        }
        $this->tree[] = $node;

        $this->addNodeToRenderedList($node['id']);

        return count($this->tree) - 1;
    }

    protected function validateTree()
    {
        foreach($this->tree as $key => $node){
            if($node['parent'] == '#')
                continue;
            if(!in_array($node['parent'], $this->_renderedElements)){
                $node['parent'] = '0';
                $this->tree[$key] = $node;
            }
        }
    }

    public function makeLayoutTree()
    {
        $this->beforeMakeLayoutTree();
        $this->validateTree();
        $fullActionName = $this->request->getFullActionName();

        $jsonTree = json_encode($this->tree);
        $blockLayoutStructure = $this->blockGenerator->createBlock(
            \Mygento\Tool\Block\LayoutStructure::class,
            'layout_structure',
            [
                'data' => [
                    'json_tree' => $jsonTree,
                    'tree_id' => 'layout_tree',
                    'full_action_name' => $fullActionName,
                    'dev_hint_prefix' => 'dev_hint_layout_'
                ]
            ]
        );

        $content = $blockLayoutStructure->toHtml();
        $this->fileManager->touchLayoutFile($content);
        $this->fileManager->touchContentFile($this->htmlContent);
    }

    public function isNodeInBlockList($elementName)
    {
        $blockList = ['require.js', 'head.additional'];

        while(!in_array($elementName, $blockList)){
            $elementName = $this->layout->getParentName($elementName);
            if($elementName == ''){
                return false;
            }
        }

        return true;
    }

}
