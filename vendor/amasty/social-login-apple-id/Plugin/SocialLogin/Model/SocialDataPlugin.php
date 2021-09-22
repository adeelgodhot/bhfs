<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SocialLoginAppleId
 */


namespace Amasty\SocialLoginAppleId\Plugin\SocialLogin\Model;

use Amasty\SocialLogin\Model\SocialData;

class SocialDataPlugin
{
    /**
     * @param SocialData $subject
     * @param array $types
     *
     * @return array
     */
    public function afterGetAllSocialTypes(SocialData $subject, array $types)
    {
        $types[SocialData::APPLE] = 'Apple';
        return $types;
    }

    /**
     * @param SocialData $subject
     * @param array $result
     * @param $type
     *
     * @return array
     */
    public function afterGetSocialConfig(SocialData $subject, array $result, $type)
    {
        if (SocialData::APPLE == $type) {
            $result = ['adapter' => \Amasty\SocialLoginAppleId\Model\Provider::class];
        }

        return $result;
    }
}
