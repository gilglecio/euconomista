@peoples
Feature: Página de lançamentos

	Background:
		When I login
		Then I should be on "/app"
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
	Scenario: Lançando três despesas

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
		Then the "value" field should contain "1000"
		Then I should see "Liquidar"

		Given When I fill in "value" with "350"
		Given I press "Liquidar" button
		Then I should see "Sucesso!"
		Then I should see "Recebimento"
		Then I should see "350,00"
		Then I should not see "Editar"

	@javascript
	Scenario: Liquidação de um lançamento com encargos

		Then I follow "1/1"
		Then I follow "Liquidar"

		Then the "value" field should contain "650"
		Given When I fill in "value" with "712"
		Given I press "Liquidar" button
		Then I should see "Sucesso!"

		Then I should see "Emissão"
		Then I should see "1.000,00"

		Then I should see "Recebimento"
		Then I should see "350,00"

		Then I should see "Encargos"
		Then I should see "62,00"
		Then I should see "712,00"

	@javascript
	Scenario: Verificando logs das liquidações

		Then I follow "Logs"
		Then I should be on "/app/logs"
		
		Then I should see "Recebimento receita nº 1/1 'Pessoa 001' R$ 350,00"
		Then I should see "Recebimento receita nº 1/1 'Pessoa 001' R$ 712,00"
		Then I should see "Encargos receita nº 1/1 'Pessoa 001' R$ 62,00"

	@javascript
	Scenario: Desfazendo liquidação com encargos
		
		Then I follow "Todas"
		Then I follow "1/1"
		Then I follow "Desfazer"
		Then I should see "Sucesso!"

		Then I should not see "Encargos"
		Then I should not see "62,00"
		Then I should not see "712,00"

		Then I should see "Recebimento"
		Then I should see "350,00"

	@javascript
	Scenario: Liquidação de um lançamento com desconto

		Then I follow "1/1"
		Then I follow "Liquidar"

		Then the "value" field should contain "650"
		Given When I fill in "value" with "585"
		Then I check with click on "desconto"
		Given I press "Liquidar" button
		Then I should see "Sucesso!"

		Then I should see "Emissão"
		Then I should see "1.000,00"

		Then I should see "Recebimento"
		Then I should see "350,00"

		Then I should see "Desconto"
		Then I should see "65,00"
		Then I should see "585,00"

		Then I follow "Lançamentos"
		Then I should not see "1/1"
		Then I follow "Todas"
		Then I should see "1/1"
		Then I should see "Pago"
		Then I should see "935,00"

	@javascript
	Scenario: Verificando logs das liquidações

		Then I follow "Logs"
		Then I should be on "/app/logs"
		
		Then I should see "Recebimento receita nº 1/1 'Pessoa 001' R$ 585,00"
		Then I should see "Desconto receita nº 1/1 'Pessoa 001' R$ 65,00"

	@javascript
	Scenario: Desfazendo liquidação com desconto

		Then I follow "Todas"
		Then I follow "1/1"
		Then I follow "Desfazer"
		Then I should see "Sucesso!"

		Then I should not see "Desconto"
		Then I should not see "65,00"
		Then I should not see "585,00"

		Then I should see "Recebimento"
		Then I should see "350,00"

		Then I follow "Lançamentos"
		Then I should see "650,00"

	@javascript
	Scenario: Tentando apagar um lançamento que sofreu liquidação

		Then I follow "1/1"
		
		Then I follow "Apagar este lançamento"
		Then I should see "O lançamento '1/1' foi movimentado."
		Then I follow "O lançamento '1/1' foi movimentado."
		Then I should not see "O lançamento '1/1' foi movimentado."

		Then I follow "Apagar todos os lançamentos deste documento"
		Then I should see "O lançamento '1/1' foi movimentado."
		Then I follow "O lançamento '1/1' foi movimentado."
		Then I should not see "O lançamento '1/1' foi movimentado."		

	@javascript
	Scenario: Desfazendo a liquidação parcial
		
		Then I follow "1/1"
		Then I follow "Desfazer"
		Then I should see "Sucesso!"

		Then I should not see "Recebimento"
		Then I should not see "350,00"
		Then I should see "Editar"
		Then I follow "Lançamentos"
		Then I should see "1/1"
		Then I should see "1.000,00"

	@javascript
	Scenario: Tentando apagar a pessoa usada nos lançamentos
		
		Then I follow "Pessoas"
		Then I should be on "/app/peoples"
		Then I follow "delete-person-1"
		Then I should see "Este registro está em uso no sistema."

	@javascript
	Scenario: Tentando apagar a categoria usada nos lançamentos
		
		Then I follow "Categorias"
		Then I should be on "/app/categories"
		Then I follow "delete-category-1"
		Then I should see "Este registro está em uso no sistema."

	@javascript
	Scenario: Apagando o lançamento de mil reais
		
		Then I follow "1/1"
		Then I follow "Apagar este lançamento"
		Then I should see "Sucesso!"
		Then I should not see "1/1"

		Then I follow "Todas"
		Then I should not see "1/1"

		Then I should see "1/2"
		Then I should see "2/2"
		Then I should see "1/3"
		Then I should see "2/3"
		Then I should see "3/3"