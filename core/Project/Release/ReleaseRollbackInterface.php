<?php

namespace Project\Release;

/**
 * ReleaseRollbackInterface class
 */
class ReleaseRollbackInterface
{
    /**
     * Inject release instance to rollback last log.
     *
     * @param ReleaseInterface $release
     */
    public function __construct(ReleaseInterface $release);
}
