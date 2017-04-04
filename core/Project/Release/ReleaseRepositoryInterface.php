<?php

namespace Project\Release;

class ReleaseRepositoryInterface
{
    public function __construct(ReleaseInterface $release);

    public function getLastLog();

    public function getSumLiquidation();
}
