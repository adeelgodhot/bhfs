<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Followup
 */


namespace Amasty\Followup\Model\Mail\MessageBuilder;

use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\EmailMessageInterfaceFactory;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Zend\Mail\Message as ZendMail;

class MessageBuilder
{
    private $legacyBuild = true;

    /**
     * @var EmailMessageInterfaceFactory|null
     */
    protected $emailMessageInterfaceFactory = null;

    /**
     * @var MimeMessageInterfaceFactory|null
     */
    protected $mimeMessageInterfaceFactory = null;

    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        if (interface_exists(EmailMessageInterface::class)) {
            $this->legacyBuild = false;
            $this->emailMessageInterfaceFactory = $objectManager->get(EmailMessageInterfaceFactory::class);
            $this->mimeMessageInterfaceFactory = $objectManager->get(MimeMessageInterfaceFactory::class);
        }
    }

    /**
     * Build email message
     *
     * @param EmailMessageInterface|MessageInterface $message
     *
     * @return EmailMessageInterface
     */
    public function build($message)
    {
        if (!$this->legacyBuild) {
            $messageData['body'] = $this->mimeMessageInterfaceFactory->create(
                ['parts' => $message->getBody()->getParts()]
            );
            $oldMessage = ZendMail::fromString($message->getRawMessage());
            $messageData['from'][] = $oldMessage->getFrom()->current();
            $messageData['to'][] = $oldMessage->getTo()->current();
            $messageData['subject'] = $oldMessage->getSubject();
            $message = $this->emailMessageInterfaceFactory->create($messageData);
        }

        return $message;
    }
}
