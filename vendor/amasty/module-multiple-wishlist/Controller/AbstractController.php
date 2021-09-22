<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_MWishlist
 */


declare(strict_types=1);

namespace Amasty\MWishlist\Controller;

use Amasty\MWishlist\Model\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\NotFoundException;

abstract class AbstractController implements ActionInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @throws NotFoundException
     */
    public function validateConfiguration()
    {
        if (!$this->getContext()->getConfigProvider()->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }
    }

    /**
     * @return Context
     */
    protected function getContext(): Context
    {
        return $this->context;
    }
}
