@peoples
Feature: Página de lançamentos

	Background:
		Given I am on "/login" visit
		Then I devo esta em "/login"
		Given When I fill in "email" with "test@hmgestor.com"
		Given When I fill in "password" with "123456"
		Given I press "Entrar" button
		Then I devo esta em "/app"
		When I follow "Lançamentos"
		Then I devo esta em "/app/releases"

	@javascript
	Scenario: Verificando a grid de lançamentos
		
		Then I should see "Adicionar"
		Then I should see "Abertas"
		Then I should see "Todas"
		Then I should see "Sem registros."

	@javascript
	Scenario: Lançando uma receita

		When I follow "Adicionar"
		Then I devo esta em "/app/releases/form"
		Then I should see "Salvar"

		When I select "Pessoa 001" from "people_id"
		When I select "Categoria 001" from "category_id"
		Given When I fill in "value" with "1000,00"
		Given When I fill in "description" with "Receita de R$ 1.000,00 em 1x"

		Given I press "Salvar" button
		Then I devo esta em "/app/releases"
		Then I should see "Sucesso!"

		Then I should see "Pessoa 001"
		Then I should see "Categoria 001"
		Then I should see "1/1"
		Then I should see "1.000,00"
		Then I should see "Receita de R$ 1.000,00 em 1x"

	@javascript
	Scenario: Lançando duas despesas

		When I follow "Adicionar"
		Then I devo esta em "/app/releases/form"
		Then I should see "Salvar"

		When I select "Despesa" from "natureza"
		When I select "Pessoa 001" from "people_id"
		When I select "Categoria 001" from "category_id"
		Given When I fill in "quantity" with "2"
		Given When I fill in "value" with "100,00"
		Given When I fill in "description" with "Receita de R$ 100,00 em 2x"

		Given I press "Salvar" button
		Then I devo esta em "/app/releases"
		Then I should see "Sucesso!"

		Then I should see "Pessoa 001"
		Then I should see "Categoria 001"
		Then I should see "1/2"
		Then I should see "2/2"
		Then I should see "50,00"
		Then I should see "Receita de R$ 100,00 em 2x"

	@javascript
	Scenario: Lançando trẽs despesas

		When I follow "Adicionar"
		Then I devo esta em "/app/releases/form"
		Then I should see "Salvar"

		When I select "Despesa" from "natureza"
		When I select "Pessoa 001" from "people_id"
		When I select "Categoria 001" from "category_id"
		Given When I fill in "quantity" with "3"
		Given When I fill in "value" with "500,00"
		Given When I fill in "description" with "Receita de R$ 500,00 em 3x"

		Given I press "Salvar" button
		Then I devo esta em "/app/releases"
		Then I should see "Sucesso!"

		Then I should see "Pessoa 001"
		Then I should see "Categoria 001"
		Then I should see "1/3"
		Then I should see "2/3"
		Then I should see "3/3"
		Then I should see "166,67"
		Then I should see "166,66"
		Then I should see "Receita de R$ 500,00 em 3x"