@categories
Feature: Página de categorias

	Background:
		Given I am on "/login" visit
		Then I devo esta em "/login"
		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		Then I devo esta em "/app"
		When I follow "Categorias"
		Then I devo esta em "/app/categories"

	@javascript
	Scenario: A grid de categorias deve está vazia
		
		Then I should see "Adicionar"
		Then I should see "Nome"
		Then I should see "Sem registros."

	@javascript
	Scenario: Cadastrando uma categoria

		When I follow "Adicionar"
		Then I devo esta em "/app/categories/form"
		Then I should see "Salvar"

		Given When I fill in "name" with "Categoria 001"
		Given I press "Salvar" button
		Then I devo esta em "/app/categories"
		Then I should see "Sucesso!"
		Then I should see "Categoria 001"

	@javascript
	Scenario: Verificando log

		When I follow "Logs"
		Then I devo esta em "/app/logs"
		Then I should see "Criou a categotia 'Categoria 001'."