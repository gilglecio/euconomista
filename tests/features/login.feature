Feature: Slim Hello World!

	Background:
		Given I am on "/login" visit
		Then I should see "Login"
	
	@javascript
	Scenario: Check Login 
		
		Then I should see "Entrar"