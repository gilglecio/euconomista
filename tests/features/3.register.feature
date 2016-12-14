@register
Feature: Página de cadastro

	Background:
		Given I am on "/" visit
		When I follow "Cadastro"
		Then I should be on "/register"
	
	@javascript
	Scenario: Cadastrando o usuário Tester
		
		Then I should see "Cadastre-se"
		Then I should see "Cadastrar"

		When I register user

		Then I should be on "/login"
		Then I should see "Cadastrado! Acesso liberado."

	@javascript
	Scenario: Verificando validação que não permite cadastrar dois usuários com o mesmo e-mail
		
		When I register user

		Then I should be on "/register"
		Then I should see "Usuário já cadastrado no sistema."