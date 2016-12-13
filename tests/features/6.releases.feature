@peoples
Feature: Página de lançamentos

	Background:
		When I login
		When I follow "Lançamentos"
		Then I should be on "/app/releases"

	@javascript
	Scenario: Verificando a grid de lançamentos
		
		Then I should see "Adicionar"
		Then I should see "Abertas"
		Then I should see "Todas"
		Then I should see "Sem registros."

	@javascript
	Scenario: Lançando uma receita

		When I follow "Adicionar"
		Then I should be on "/app/releases/form"
		Then I should see "Salvar"

		When I select "Pessoa 001" from "people_id"
		When I select "Categoria 001" from "category_id"
		Given When I fill in "value" with "1000,00"
		Given When I fill in "description" with "Receita de R$ 1.000,00 em 1x"

		Given I press "Salvar" button
		Then I should be on "/app/releases"
		Then I should see "Sucesso!"

		Then I should see "Pessoa 001"
		Then I should see "Categoria 001"
		Then I should see "1/1"
		Then I should see "1.000,00"
		Then I should see "Receita de R$ 1.000,00 em 1x"

	@javascript
	Scenario: Lançando duas despesas

		When I follow "Adicionar"
		Then I should be on "/app/releases/form"
		Then I should see "Salvar"

		When I select "Despesa" from "natureza"
		When I select "Pessoa 001" from "people_id"
		When I select "Categoria 001" from "category_id"
		Given When I fill in "quantity" with "2"
		Given When I fill in "value" with "100,00"
		Given When I fill in "description" with "Receita de R$ 100,00 em 2x"

		Given I press "Salvar" button
		Then I should be on "/app/releases"
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
		Then I should be on "/app/releases/form"
		Then I should see "Salvar"

		When I select "Despesa" from "natureza"
		When I select "Pessoa 001" from "people_id"
		When I select "Categoria 001" from "category_id"
		Given When I fill in "quantity" with "3"
		Given When I fill in "value" with "500,00"
		Given When I fill in "description" with "Receita de R$ 500,00 em 3x"

		Given I press "Salvar" button
		Then I should be on "/app/releases"
		Then I should see "Sucesso!"

		Then I should see "Pessoa 001"
		Then I should see "Categoria 001"
		Then I should see "1/3"
		Then I should see "2/3"
		Then I should see "3/3"
		Then I should see "166,67"
		Then I should see "166,66"
		Then I should see "Receita de R$ 500,00 em 3x"

	@javascript
	Scenario: Verificando logs dos lançamentos emitidos
	
		Then I follow "Logs"
		Then I should be on "/app/logs"
		
		Then I should see "Emissão receita nº 1/1 'Pessoa 001' R$ 1.000,00"
		
		Then I should see "Emissão despesa nº 1/2 'Pessoa 001' R$ 50,00"
		Then I should see "Emissão despesa nº 2/2 'Pessoa 001' R$ 50,00"

		Then I should see "Emissão despesa nº 1/3 'Pessoa 001' R$ 166,67"
		Then I should see "Emissão despesa nº 2/3 'Pessoa 001' R$ 166,67"
		Then I should see "Emissão despesa nº 3/3 'Pessoa 001' R$ 166,66"

		Then I should not see "Restaurar"

	@javascript
	Scenario: Liquidação parcial de um lançamento

		Then I follow "1/1"
		Then I should see "Extrato de lançamento"
		Then I should see "Emissão"
		Then I should see "Lançamento nº 1/1"
		Then I should see "Liquidar"
		Then I should see "Editar"
		Then I should see "Apagar este lançamento"
		Then I should see "Apagar todos os lançamentos deste documento"
		Then I should see "1.000,00"

		Then I follow "Liquidar"

		Then I should see "Lista de ações da parcela"
		Then I should see "1000"
		Then I should see "Liquidar"

		Given When I fill in "value" with "350"
		Given I press "Liquidar" button
		Then I should see "Sucesso!"
		Then I should see "Recebimento"
		Then I should see "350,00"