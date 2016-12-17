<?php

use Behat\MinkExtension\Context\MinkContext;

include __DIR__ . '/PeopleContext.php';
include __DIR__ . '/CategoryContext.php';

/**
 * App Context
 */
class AppContext extends MinkContext
{
    use \PeopleContext;
    use \CategoryContext;

    /**
     * @var array
     */
    public $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * @Given /^When I fill in "([^"]*)" with "([^"]*)"$/
     */
    public function whenIFillInWith($name, $value)
    {
        $this->getSession()->getPage()->fillField($name, $value);
    }

    /**
     * @beforeScenario
     */
    public function before($args)
    {
        $this->getSession()->resizeWindow($this->params['width'], $this->params['heigth']);
        $this->visit($this->params['base_url']);
    }

    /**
     * @Given /^I press "([^"]*)" button$/
     */
    public function stepIPressButton($button)
    {
        $el = $this->getSession()->getPage()->find('named', ['link_or_button', "'$button'"]);
 
        if (is_null($el)) {
            throw new Exception("'$button' button not found");
        }

        $el->click();
    }

    /**
     * @BeforeStep @javascript
     */
    public function beforeStep($event)
    {
        if (preg_match('/(follow|press|click|submit)/i', $event->getStep()->getText())) {
            $this->ajaxClickHandler_before();
        }
    }
 
    /**
     * @AfterStep @javascript
     */
    public function afterStep($event)
    {
        if (preg_match('/(follow|press|click|submit)/i', $event->getStep()->getText())) {
            $this->ajaxClickHandler_after();
        }
    }
 
    /**
     * Hook into jQuery ajaxStart and ajaxComplete events.
     * Prepare __ajaxStatus() functions and attach them to these events.
     * Event handlers are removed after one run.
     */
    public function ajaxClickHandler_before()
    {
        $javascript = <<<JS
window.jQuery(document).one('ajaxStart.ss.test', function(){
    window.__ajaxStatus = function() {
        return 'waiting';
    };
});
window.jQuery(document).one('ajaxComplete.ss.test', function(){
    window.__ajaxStatus = function() {
        return 'no ajax';
    };
});
JS;
        $this->getSession()->executeScript($javascript);
    }
 
    /**
     * Wait for the __ajaxStatus()to return anything but 'waiting'.
     * Don't wait longer than 5 seconds.
     */
    public function ajaxClickHandler_after()
    {
        $this->getSession()->wait(0, "(typeof window.__ajaxStatus !== 'undefined' ? window.__ajaxStatus() : 'no ajax') !== 'waiting'");
    }

    /**
     * @Given /^(?:|I )am on "(?P<page>[^"]+)" visit$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)" visit$/
     */
    public function visitPage($page)
    {
        $this->getSession()->visit($this->params['base_url'] . $this->locatePath($page));
    }

    /**
     * @Then /^"([^"]*)" in "([^"]*)" should be selected$/
     */
    public function inShouldBeSelected($optionValue, $select)
    {
        $select = $this->getSession()->getPage()->find('named', array('select', "\"{$select}\""));
        $option = $select->find('named', array('option', "\"{$optionValue}\""));

        //it should have the attribute selected and it should be set to selected
        if (!$option->hasAttribute("selected")) {
            throw new Exception("'$select' has no selected attribute");
        }

        if ($option->getAttribute("selected") != "true") {
            throw new Exception("'$select' has no selected attribute iquals selected");
        }
    }

    /**
     * @Then /^I check with click on "([^"]*)"$/
     */
    public function checkWithClickOn($checkbox)
    {
        $el = $this->getSession()->getPage()->find('named', ['checkbox', "\"{$checkbox}\""]);
        
        if (!$el) {
            throw new Exception("checkbox {$checkbox} not found");
        }

        $el->click();
    }

    /**
     * @Then /^I select option "([^"]*)" from "([^"]*)"$/
     */
    public function selectOptionFrom($option, $select)
    {
        $el = $this->getSession()->getPage()->find('named', array('select', "\"{$select}\""));
        $options = $el->findAll('css', 'option');
        
        if (!$options) {
            throw new Exception("Select not {$select} found");
        }

        $el->selectOption($options[$option]->getValue());
    }

    /**
     * @Then /^the element "(?P<element>[^"]*)" contain "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function elementHasContains($element, $text)
    {
        $el = $this->assertSession()->elementExists('css', $element)->getText();
        
        foreach (explode('|', $text) as $t) {
            if (!preg_match('/' . preg_quote($t, '/') . '/ui', $el)) {
                throw new Exception("The element \"{$element}\" not contains \"{$t}\"");
            }
        }
    }

    /**
     * @When /^I log in I should be inside the application$/
     */
    public function login()
    {
        $this->visit($this->params['base_url'] . '/login');
        $this->getSession()->getPage()->fillField('email', $this->params['user_email']);
        $this->getSession()->getPage()->fillField('password', $this->params['user_password']);
        $this->getSession()->getPage()->find('named', array('button', "\"Entrar\""))->click();
        $this->assertPageAddress('/app');
    }

    /**
     * @Given /^I am on confirm email page$/
     */
    public function iAmOnConfirmEmailPage()
    {
        $this->visit($this->params['base_url'] . '/register/confirm_email/' . sha1($this->params['user_email']));
    }

    /**
     * @Then /^I should see confirm register message in login page$/
     */
    public function iShouldSeeConfirmRegisterMessageInLoginPage()
    {
        $this->assertPageAddress('/login');
        $this->assertPageContainsText($this->params['user_name'] . ', e-mail confirmado.');
    }


    /**
     * @When /^I log in I should see unconfirmed message$/
     */
    public function loginUnconfirmed()
    {
        $this->visit($this->params['base_url'] . '/login');
        $this->getSession()->getPage()->fillField('email', $this->params['user_email']);
        $this->getSession()->getPage()->fillField('password', $this->params['user_password']);
        $this->getSession()->getPage()->find('named', array('button', "\"Entrar\""))->click();
        $this->assertPageContainsText('E-mail não confirmado.');
    }

    /**
     * @When /^I logout$/
     */
    public function logout()
    {
        $this->visit($this->params['base_url'] . '/logout');
    }

    /**
     * @When /^resetting the database I should see the text OK on screen$/
     */
    public function resettingTheDatabaseIShouldSeeTheTextOKOnScreen()
    {
        $this->visit($this->params['base_url'] . '/reset');
        $this->assertPageContainsText('OK');
    }

    /**
     * @When /^registering a user$/
     */
    public function registeringAUser()
    {
        $this->visit($this->params['base_url'] . '/register');
        $this->getSession()->getPage()->fillField('name', $this->params['user_name']);
        $this->getSession()->getPage()->fillField('email', $this->params['user_email']);
        $this->getSession()->getPage()->fillField('password', $this->params['user_password']);
        $this->getSession()->getPage()->fillField('confirm_password', $this->params['user_password']);
        $this->checkWithClickOn('accept_terms');
        $this->getSession()->getPage()->find('named', array('button', "\"Cadastrar\""))->click();
    }

    /**
     * @Then /^I should be redirected to the login page$/
     */
    public function iShouldBeRedirectedToTheLoginPage()
    {
        $this->assertPageAddress('/login');
    }

    /**
     * @Given /^view the message of success$/
     */
    public function viewTheMessageOfSuccess()
    {
        $this->assertPageContainsText('Sucesso!');
    }
}
