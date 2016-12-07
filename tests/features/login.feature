Feature: Página de login

	Background:
		Given I am on "/" visit
		When I follow "Login"
		Then I devo esta em "/login"
	
	@javascript
	Scenario: Verificando se os itens da página del login estão presentes 
		
		Then I should see "Login"
		Then I should see "Entrar"

	@javascript
	Scenario: Acessando o sistema com o usuário padrão

		Given When I fill in "email" with "user@mail.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		
		Then I devo esta em "/app"
		Then I should see "User"
		Then I should see "Sair"