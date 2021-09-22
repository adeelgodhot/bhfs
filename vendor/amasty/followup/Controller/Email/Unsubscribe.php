<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */

namespace Amasty\Followup\Controller\Email;

class Unsubscribe extends \Amasty\Followup\Controller\Email\Url
{
    public function execute()
    {
        $history = $this->_getHistory();

        if ($history){
            $blacklist = $this->_objectManager->create('Amasty\Followup\Model\Blacklist')
                ->load($history->getEmail(), 'email');

            if (!$blacklist->getId())
            {
                $blacklist->addData([
                    'email' => $history->getEmail()
                ]);

                $blacklist->save();
            }

            $this->messageManager->addSuccess(__('You have been unsubscribed'));

        }

        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }
}