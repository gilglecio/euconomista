Feature: Página de categorias

	Background:
		Given I am on "/login" visit
		Then I devo esta em "/login"
		Given When I fill in "email" with "user@mail.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		Then I devo esta em "/app"
		When I follow "Categorias"
		Then I devo esta em "/app/categories"

	@javascript
	Scenario: Verificando a grid de categorias
		
		Then I should see "Adicionar Categoria"
		Then I should see "Nome"
		Then I should see "Sem registros."

	@javascript
	Scenario: Cadastrando uma categoria

		When I follow "Adicionar Categoria"
		Then I devo esta em "/app/categories/form"
		Then I should see "Salvar"

		Given When I fill in "name" with "Categoria 001"
		Given I press "Salvar" button
		Then I devo esta em "/app/categories"
		Then I should see "Categoria 001"

	@javascript
	Scenario: Apagando a categoria cadastrada

		Then I should see "Categoria 001"
		When I follow "Apagar"
		Then I should see "Deseja realmente APAGAR 'Categoria 001'?"
		# When I press "OK"
		# Then I should not see "Categoria 001"
