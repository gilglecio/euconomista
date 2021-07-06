@register
Feature: Página de cadastro

	Background:
		Given I am on "/" visit
		When I follow "Fazer meu cadastro"
		Then I should be on "/register"
	
	@javascript
	Scenario: Cadastrando o usuário Tester

		Then I should see "Cadastre-se"
		Then I should see "Cadastrar"

		When registering a user
		Then I should be redirected to the login page
		And view the message of success

	@javascript
	Scenario: Verificando validação que não permite cadastrar dois usuários com o mesmo e-mail
		
		When registering a user
		Then I should be on "/register"
		Then I should see "Usuário já cadastrado no sistema."

	@javascript
	Scenario: Apenas usuários com email confirmado podem acessar o sistema

		When I log in I should see unconfirmed message

	@javascript
	Scenario: Acessando url de confirmação de cadastro com um token que não existe

		Given I am on "/register/confirm_email/my_invalid_token_test" visit
		Then I should be on "/login"
		Then I should see "Token não localizado."

	@javascript
	Scenario: Acessando url de confirmação de cadastro com o token correto

		Given I am on confirm email page
		Then I should see confirm register message in login page