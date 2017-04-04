<?php

namespace Project\Release;

use Datetime;

/**
 * ReleaseLogInterface class
 */
class ReleaseLogInterface
{
    /**
     * Inject release instance to logger.
     *
     * @param ReleaseInterface $release
     */
    public function __construct(ReleaseInterface $release);

    /**
     * Set to log action.
     *
     * @param integer $action Log action
     */
    public function setAction($action);

    /**
     * Set to log date.
     *
     * @param Datetime $date Log date and hours
     */
    public function setDate(Datetime $date);

    /**
     * Set valur for log.
     *
     * @param float $value Log value
     */
    public function setValue($value);
}
