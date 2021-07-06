<?php

/**
 * Release Context
 */
trait ReleaseContext
{
    /**
     * @Then /^I should be on current month releases$/
     */
    public function iShouldBeOnCurrentMonthReleases()
    {
        $this->assertPageAddress('/app/releases/in/' . date('Y-m'));
    }
}
