@peoples
Feature: Página de categorias

	Background:
		When I login
		Then I should be on "/app"
		When I follow "Pessoas"
		Then I should be on "/app/peoples"

	@javascript
	Scenario: Verificando a grid de categorias
		
		Then I should see "Adicionar"
		Then I should see "Nome"
		Then I should see "Sem registros."

	@javascript
	Scenario: Cadastrando uma categoria

		When I follow "Adicionar"
		Then I should be on "/app/peoples/form"
		Then I should see "Salvar"

		Given When I fill in "name" with "Pessoa 001"
		Given I press "Salvar" button
		Then I should be on "/app/peoples"
		Then I should see "Sucesso!"
		Then I should see "Pessoa 001"

	@javascript
	Scenario: Verificando log

		When I follow "Logs"
		Then I should be on "/app/logs"
		Then I should see "Adicionou 'Pessoa 001' em pessoas."