@peoples
Feature: Página de categorias

	Background:
		Given I am on "/login" visit
		Then I devo esta em "/login"
		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		Then I devo esta em "/app"
		When I follow "Pessoas"
		Then I devo esta em "/app/peoples"

	@javascript
	Scenario: Verificando a grid de categorias
		
		Then I should see "Adicionar"
		Then I should see "Nome"
		Then I should see "Sem registros."

	@javascript
	Scenario: Cadastrando uma categoria

		When I follow "Adicionar"
		Then I devo esta em "/app/peoples/form"
		Then I should see "Salvar"

		Given When I fill in "name" with "Pessoa 001"
		Given I press "Salvar" button
		Then I devo esta em "/app/peoples"
		Then I should see "Sucesso!"
		Then I should see "Pessoa 001"

	@javascript
	Scenario: Verificando log

		When I follow "Logs"
		Then I devo esta em "/app/logs"
		Then I should see "Adicionou 'Pessoa 001' em pessoas."