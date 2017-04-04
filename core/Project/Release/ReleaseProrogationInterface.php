<?php

namespace Project\Release;

use Datetime;

interface ReleaseProrogationInterface
{
    /**
     * Starts the object with the instance of the posting that will be settled.
     *
     * @param  ReleaseInterface $release Release to prorrogate
     */
    public function __construct(ReleaseInterface $release);

    /**
     * It makes the validation necessary to allow the posting to be extended.
     *
     * @return boolean
     */
    public function isValid();

    /**
     * The date of extend.
     *
     * @param  Datetime $extend_date Liquidate date
     */
    public function setExtendDate(Datetime $extend_date);

    /**
     * Reports the new launch value.
     *
     * @param float $value Liquidate value
     */
    public function setExtendValue($value);
}
