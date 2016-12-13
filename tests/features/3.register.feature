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

		Given When I fill in "name" with "Tester"
		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given When I fill in "confirm_password" with "123456"
		Given I press "Cadastrar" button

		Then I should be on "/login"
		Then I should see "Cadastrado! Acesso liberado."

	@javascript
	Scenario: Verificando validação que não permite cadastrar dois usuários com o mesmo e-mail
		
		Given When I fill in "name" with "Tester"
		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given When I fill in "confirm_password" with "123456"
		Given I press "Cadastrar" button

		Then I should be on "/register"
		Then I should see "Usuário já cadastrado no sistema."