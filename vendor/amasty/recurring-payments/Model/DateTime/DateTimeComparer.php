<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_RecurringPayments
 */


namespace Amasty\RecurringPayments\Model\DateTime;

class DateTimeComparer
{
    /**
     * @param string $dateA
     * @param string $dateB
     * @return bool
     */
    public function compareDates(string $dateA, string $dateB)
    {
        $onlyDateAObject = $this->getOnlyDate($dateA);
        $onlyDateBObject = $this->getOnlyDate($dateB);

        return $onlyDateAObject == $onlyDateBObject;
    }

    /**
     * @param string $date
     * @return \DateTime
     */
    private function getOnlyDate(string $date)
    {
        $dateObject = $this->getDateObject($date);

        return $this->getDateObject($dateObject->format('Y-m-d'));
    }

    /**
     * @param string $date
     * @return \DateTime
     */
    private function getDateObject(string $date)
    {
        return new \DateTime($date);
    }
}
