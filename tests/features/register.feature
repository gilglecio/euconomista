Feature: Página de cadastro

	Background:
		Given I am on "/" visit
		When I follow "Register"
		Then I devo esta em "/register"
	
	@javascript
	Scenario: Verificando se os itens da página de cadastro estão presentes
		
		Then I should see "Cadastro"
		Then I should see "Cadastrar"