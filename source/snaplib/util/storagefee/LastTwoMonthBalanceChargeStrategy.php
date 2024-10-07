<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\util\storagefee;

Use Snap\IStorageFeeChargeStrategy;

/**
 * Class LastTwoMonthBalanceChargeStrategy
 * 
 * This class implements the IStorageFeeChargeStrategy interface and calculates
 * the chargeable weight in grams based on the account holder's gold balance from the last two months.
 */
class LastTwoMonthBalanceChargeStrategy implements IStorageFeeChargeStrategy
{
	/**
     * Calculate the chargeable weight in grams using the account holder's gold balance 
     * from two months prior to the charge date.
     * 
     * @param mixed $app             The application context, providing configuration and timezone settings.
     * @param mixed $accountHolder   The account holder whose balance is used for the calculation.
     * @param mixed $partner         The partner entity involved in the transaction (not used in this method).
     * @param DateTime $chargedon    The date when the charge is applied.
     * 
     * @return int The calculated chargeable weight in grams based on the last two months' balance.
     */
    public function calculateChargeableGram($app, $accountHolder, $partner, $chargedon)
    {
        // Clone the charge date to avoid modifying the original DateTime object
        $last2Month = clone $chargedon;
        $last2Month->setTime(0, 0, 0);
        
        // Modify the date to get the balance of two months prior
        $last2Month->modify("- 1 month -1 second"); // Example: 1/9/2024 charge will get 31/7/2024 balance
        $last2Month->setTimezone($app->getServerTimezone());

        // Return the current gold balance for the account holder as of the last 2-month date
        return $accountHolder->getCurrentGoldBalance($last2Month);
    }
}