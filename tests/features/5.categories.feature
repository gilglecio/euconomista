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

	    When I register the category "Categoria 001"
	    Then I need to see the success message in the category grid
	    And the category must be present in the list of categories
	    And in the user log must be registered the creation of this category

	@javascript
	Scenario: Não permitir duas categorias com o mesmo nome

		When I add category "Categoria 001"
		Then I should be on "/app/categories/form"
		Then I should see "Name must be unique"