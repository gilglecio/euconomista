@categories
Feature: Página de categorias

	Background:
		When I login
		Then I should be on "/app"
		When I follow "Categorias"
		Then I should be on "/app/categories"

	@javascript
	Scenario: A grid de categorias deve está vazia
		
		Then I should see "Adicionar"
		Then I should see "Nome"
		Then I should see "Sem registros."

	@javascript
	Scenario: Cadastrando uma categoria

		When I add the person "Categoria 001"
		Then I should see "Categoria 001"

	@javascript
	Scenario: Verificando log

		When I follow "Logs"
		Then I should be on "/app/logs"
		Then I should see "Criou a categotia 'Categoria 001'."