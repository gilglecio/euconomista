<?php

namespace Project\Release;

use Datetime;

interface ReleaseLiquidationInterface
{
    /**
     * Starts the object with the instance of the posting that will be settled.
     *
     * @param  ReleaseInterface $release Release to liquidate
     */
    public function __construct(ReleaseInterface $release);

    /**
     * Does the necessary validation to allow the posting to be liquidate.
     *
     * @return boolean
     */
    public function isValid();

    /**
     * Returns true only if the settlement value is less than the open amount
     * of the posting and the difference has not been applied as a discount.
     *
     * @return boolean
     */
    public function isPartial();

    /**
     * The date of settlement.
     *
     * @param  Datetime $settlement_date Liquidate date
     * @return ReleaseLiquidationInterface
     */
    public function setSettlementDate(Datetime $settlement_date);

    /**
     * Informs the amount of the settlement.
     *
     * @param float $value Liquidate value
     * @return ReleaseLiquidationInterface
     */
    public function setSettlementValue($value);

    /**
     * If the settlement amount is less than the open amount of the posting, you must make a discount settlement,
     * by default a partial settlement is made.
     *
     * @param boolean $bool
     * @return ReleaseLiquidationInterface
     */
    public function setPostDifferenceAsDiscount($bool);
}
