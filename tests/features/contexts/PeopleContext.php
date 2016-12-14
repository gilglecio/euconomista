<?php

/**
 * People Context
 */
trait PeopleContext
{
    /**
     * @When /^I add the person "([^"]*)"$/
     */
    public function createPerson($name)
    {
        $this->clickLink('Pessoas');
        $this->assertPageAddress('/app/peoples');
        $this->clickLink('Adicionar');
        $this->assertPageAddress('/app/peoples/form');
        $this->getSession()->getPage()->fillField('name', $name);
        $this->getSession()->getPage()->find('named', ['button', "\"Salvar\""])->click();
        $this->assertPageAddress('/app/peoples');
        $this->assertPageContainsText('Sucesso!');
    }
}
