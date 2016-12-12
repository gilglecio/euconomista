@login
Feature: Página de login

	Background:
		Given I am on "/" visit
		When I follow "Login"
		Then I devo esta em "/login"
	
	@javascript
	Scenario: Verificando se os itens da página de login estão presentes 
		
		Then I should see "Login"
		Then I should see "Entrar"

	@javascript
	Scenario: Acessando o sistema com o usuário Tester

		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		
		Then I devo esta em "/app"
		Then I should see "Tester"
		Then I should see "Sair"

	@javascript
	Scenario: Todas as grid devem está vazias

		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		Then I devo esta em "/app"

		When I follow "Lançamentos"
		Then I devo esta em "/app/releases"
		Then I should see "Adicionar"
		Then I should see "Abertas"
		Then I should see "Todas"
		Then I should see "Sem registros."

		When I follow "Todas"
		Then I devo esta em "/app/releases/i/all"
		Then I should see "Sem registros."

		When I follow "Abertas"
		Then I devo esta em "/app/releases"
		Then I should see "Sem registros."

		When I follow "Pessoas"
		Then I devo esta em "/app/peoples"
		Then I should see "Adicionar"
		Then I should see "Sem registros."

		When I follow "Categorias"
		Then I devo esta em "/app/categories"
		Then I should see "Adicionar"
		Then I should see "Sem registros."

		When I follow "Usuários"
		Then I devo esta em "/app/users"
		Then I should see "test@hmgestor.com"

		When I follow "Extrato"
		Then I devo esta em "/app/extract"
		Then I should see "Sem registros."

		When I follow "Logs"
		Then I devo esta em "/app/logs"
		Then I should see "Conectou-se"
		Then I should see "Tester"

		When I follow "Me"
		Then I devo esta em "/app/me"
		Then I should see "Baixar backup"
		Then I should see "Apagar minha conta"