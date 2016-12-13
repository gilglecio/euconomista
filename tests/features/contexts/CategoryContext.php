<?php

/**
 * Category Context
 */
trait CategoryContext
{
    /**
     * @When /^I add category "([^"]*)"$/
     */
    public function createCategory($name)
    {
        $this->clickLink('Categorias');
        $this->assertPageAddress('/app/categories');
        $this->clickLink('Adicionar');
        $this->assertPageAddress('/app/categories/form');
        $this->getSession()->getPage()->fillField('name', $name);
        $this->getSession()->getPage()->find('named', ['button', "\"Salvar\""])->click();
        $this->assertPageAddress('/app/categories');
        $this->assertPageContainsText('Sucesso!');
    }
}
