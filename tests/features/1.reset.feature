@reset
Feature: Zera o banco de dados

	Background:
		Given I am on "/reset" visit
	
	@javascript
	Scenario: Acessar rota para apagar todos os dados do banco de dados.
		
		Then I should see "OK"