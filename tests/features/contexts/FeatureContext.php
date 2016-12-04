<?php

use Behat\Behat\Exception\PendingException;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    public $parameters;

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @beforeScenario
     */
    public function before($args)
    {
        $this->getSession()->resizeWindow(800, 600);
        $this->visit($this->parameters['base_url']);
    }

    /**
     * @Then /^I should see an error modal "([^"]*)"$/
     */
    public function iShouldSeeAnErrorModal($args)
    {
        $time = 2000; // time should be in milliseconds
        $this->getSession()->wait($time, '(0 === jQuery.active)');
        // asserts below
    }

    /**
     * Checks, that page contains specified text.
     *
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" multiple$/
     */
    public function assertPageContainsTextMultiple($texts)
    {
        foreach (explode(';', $texts) as $text) {
            $this->assertSession()->pageTextContains($this->fixStepArgument($text));
        }
    }

    /**
     * Checks, that current page PATH is equal to specified.
     *
     * @Then /^(?:|I )devo esta em "(?P<page>[^"]+)"$/
     */
    public function assertPageAddress($page)
    {
        $time = 100; // time should be in milliseconds
        $this->getSession()->wait($time, '(0 === jQuery.active)');

        $this->assertSession()->addressEquals($this->locatePath($page));
    }

    /**
     * @Given /^When I fill in "([^"]*)" with "([^"]*)"$/
     */
    public function whenIFillInWith($arg1, $arg2)
    {
        // throw new PendingException();
        $this->getSession()->getPage()->fillField($arg1, $arg2);
    }


    /**
     * @When  /^(?:|I )will wait "([0-9])"$/
     */
    public function iWillWaitSeconds($seconds)
    {
        sleep($seconds);
    }


    /**
     * @Then /^I should be redirected$/
     */
    public function iShouldBeRedirected()
    {
        throw new PendingException();
    }

    /**
     * @BeforeStep @javascript
     */
    public function beforeStep($event)
    {
        $text = $event->getStep()->getText();
        if (preg_match('/(follow|press|click|submit)/i', $text)) {
            $this->ajaxClickHandler_before();
        }
    }
 
    /**
     * @AfterStep @javascript
     */
    public function afterStep($event)
    {
        $text = $event->getStep()->getText();
        if (preg_match('/(follow|press|click|submit)/i', $text)) {
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
        // sleep(1);

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
        // sleep(1);
        $this->getSession()->wait(100, "(typeof window.__ajaxStatus !== 'undefined' ? window.__ajaxStatus() : 'no ajax') !== 'waiting'");
    }

    /**
     * @Given /^I press "([^"]*)" button$/
     */
    public function stepIPressButton($button)
    {
        // $this->iWillWaitSeconds(2);
        
        $page = $this->getSession()->getPage();
 
        $button_selector = array('link_or_button', "'$button'");
        $button_element = $page->find('named', $button_selector);
 
        if (null === $button_element) {
            throw new Exception("'$button' button not found");
        }
 
        $this->ajaxClickHandler_before();
        $button_element->click();
        $this->ajaxClickHandler_after();
    }

    /**
     * @Then /^"([^"]*)" in "([^"]*)" should be selected$/
     */
    public function inShouldBeSelected($optionValue, $select)
    {
        $selectElement = $this->getSession()->getPage()->find('named', array('select', "\"{$select}\""));
        $optionElement = $selectElement->find('named', array('option', "\"{$optionValue}\""));

        //it should have the attribute selected and it should be set to selected
        if (!$optionElement->hasAttribute("selected")) {
            throw new Exception("'$select' has no selected attribute");
        }

        if ($optionElement->getAttribute("selected") != "true") {
            throw new Exception("'$select' has no selected attribute iquals selected");
        }
    }

    /**
     * @Then /^I check with click on "([^"]*)"$/
     */
    public function checkWithClickOn($checkbox)
    {
        $element = $this->getSession()->getPage()->find('named', array('checkbox', "\"{$checkbox}\""));
        
        if (!$element) {
            throw new Exception("checkbox {$checkbox} not found", 1);
        }

        $element->click();
    }

    /**
     * @Then /^I select option "([^"]*)" from "([^"]*)"$/
     */
    public function selectOptionFrom($option, $select)
    {
        $element = $this->getSession()->getPage()->find('named', array('select', "\"{$select}\""));
        $optionElements = $element->findAll('css', 'option');
        
        if (!$optionElements) {
            throw new Exception("Select not {$select} found", 1);
        }

        $element->selectOption($optionElements[$option]->getValue());
    }

    /**
     * @Then /^I search by "([^"]*)" with value "([^"]*)"$/
     */
    public function searchByWithValues($fields, $values)
    {
        $fields = explode(',', $fields);
        $values = explode(',', $values);

        $element = $this->getSession()->getPage()->find('named', array('button', "\"search\""));

        if (count($fields) != count($values)) {
            throw new Exception("Number of fields not match with the number of values");
        }

        $element->click();
        $i = 0;
        foreach ($fields as $field) {
            $field = explode(':', $field);
            $value = trim($values[$i]);
            if (count($field) > 1) {
                switch ($field[0]) {
                    case 'select':
                        $this->getSession()->getPage()->selectFieldOption(trim($field[1]), $value);
                        break;

                    default:
                        $this->getSession()->getPage()->fillField(trim($field[1]), $value);
                        break;
                }
            } else {
                $this->getSession()->getPage()->fillField(trim($field[0]), $value);
            }

            $i++;
        }

        $submit = $this->getSession()->getPage()->find('named', array('button', "\"grid-pesquisar\""));
        $submit->click();
    }

    /**
     * @Given /^(?:|I )am on "(?P<page>[^"]+)" visit$/
     * @When /^(?:|I )go to "(?P<page>[^"]+)" visit$/
     */
    public function visitPage($page)
    {
        $this->getSession()->visit($this->parameters['base_url'] . $this->locatePath($page));
    }

    /**
     * @When /^I login with user "([^"]*)" and pass "([^"]*)" and must go "([^"]*)"$/
     */
    public function login($user, $pass, $go)
    {
        $this->visit($this->parameters['base_url'] . '/logout');
        $entrar = $this->getSession()->getPage()->find('named', array('button', "\"Logar\""));

        $this->getSession()->getPage()->fillField('username', $user);
        $this->getSession()->getPage()->fillField('password', $pass);
        $entrar->click();

        $this->assertPageAddress($go);
    }

    /**
     * Verify, if a element contains one or more occurrences
     * Usage: Then the element ".tbody_tr:nth-child(1)" contain "13455|NF|John Snash|receita|QUITADO|450,00"
     *
     * @Then /^the element "(?P<element>[^"]*)" contain "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function elementHasContains($element, $text)
    {
        $arr = explode('|', $text);

        $elementHTML = $this->assertSession()->elementExists('css', $element);
        $actual  = $elementHTML->getText();
        
        foreach ($arr as $text) {
            $regex   = '/'.preg_quote($text, '/').'/ui';

            if (!preg_match($regex, $actual)) {
                throw new Exception("The element \"{$element}\" not contains \"{$text}\"");
            }
        }
    }

    /**
     * Double click link with specified id|title|alt|text.
     *
     * @When /^(?:|I )double follow "(?P<link>(?:[^"]|\\")*)"$/
     */
    public function doubleClickLink($link)
    {
        $page = $this->getSession()->getPage();
        $element = $page->find('css', "a:contains('" . $link . "')");
        $element->doubleClick();
    }
}
