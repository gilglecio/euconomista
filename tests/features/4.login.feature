@login
Feature: Página de login

	Background:
		Given I am on "/" visit
		When I follow "Login"
		Then I should be on "/login"
	
	@javascript
	Scenario: Verificando se os itens da página de login estão presentes 
		
		Then I should see "Login"
		Then I should see "Entrar"

	@javascript
	Scenario: Acessando o sistema com o usuário Tester

		When I log in I should be inside the application
		Then I should see "Tester"
		Then I should see "Sair"

	@javascript
	Scenario: Todas as grid devem está vazias

		When I log in I should be inside the application
		When I follow "Lançamentos"
		Then I should be on current month releases
		Then I should see "Adicionar"
		Then I should see "Sem registros."

		When I follow "Pessoas"
		Then I should be on "/app/peoples"
		Then I should see "Adicionar"
		Then I should see "Sem registros."

		When I follow "Categorias"
		Then I should be on "/app/categories"
		Then I should see "Adicionar"
		Then I should see "Sem registros."

		When I follow "Usuários"
		Then I should be on "/app/users"
		Then I should see "test@hmgestor.com"

		When I follow "Extrato"
		Then I should be on "/app/extract"
		Then I should see "Sem registros."

		When I follow "Logs"
		Then I should be on "/app/logs"
		Then I should see "Conectou-se"
		Then I should see "Tester"

		When I follow "Me"
		Then I should be on "/app/me"
		Then I should see "Baixar backup"
		Then I should see "Desativar minha conta"