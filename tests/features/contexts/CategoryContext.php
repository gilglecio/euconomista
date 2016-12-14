<?php

/**
 * Category Context
 */
trait CategoryContext
{
    /**
     * Nome da categoria cadastrada.
     * @var string
     */
    public $category_name;

    /**
     * @When /^I register the category "([^"]*)"$/
     */
    public function iRegisterTheCategory($name)
    {
        $this->category_name = $name;

        $this->clickLink('Categorias');
        $this->assertPageAddress('/app/categories');
        $this->clickLink('Adicionar');
        $this->assertPageAddress('/app/categories/form');
        $this->getSession()->getPage()->fillField('name', $name);
        $this->getSession()->getPage()->find('named', ['button', "\"Salvar\""])->click();
    }

    /**
     * @Then /^I need to see the success message in the category grid$/
     */
    public function iNeedToSeeTheSuccessMessageInTheCategoryGrid()
    {
        $this->assertPageAddress('/app/categories');
        $this->assertPageContainsText('Sucesso!');
    }

    /**
     * @Given /^the category must be present in the list of categories$/
     */
    public function theCategoryMustBePresentInTheListOfCategories()
    {
        $this->assertPageAddress('/app/categories');
        $this->assertPageContainsText($this->category_name);
    }

    /**
     * @Given /^in the user log must be registered the creation of this category$/
     */
    public function inTheUserLogMustBeRegisteredTheCreationOfThisCategory()
    {
        $this->clickLink('Logs');
        $this->assertPageAddress('/app/logs');
        $this->assertPageContainsText('Criou a categotia \'' . $this->category_name . '\'.');
    }
}
