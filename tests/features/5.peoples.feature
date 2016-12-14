@peoples
Feature: Página de pessoas

	Background:
		When I login
		Then I should be on "/app"
		When I follow "Pessoas"
		Then I should be on "/app/peoples"

	@javascript
	Scenario: Verificando a grid de pessoas
		
		Then I should see "Adicionar"
		Then I should see "Nome"
		Then I should see "Sem registros."

	@javascript
	Scenario: Cadastrando uma pessoa

		When I add the person "Pessoa 001"
		Then I should be on "/app/peoples"
		Then I should see "Sucesso!"
		Then I should see "Pessoa 001"

	@javascript
	Scenario: Verificando log

		When I follow "Logs"
		Then I should be on "/app/logs"
		Then I should see "Adicionou 'Pessoa 001' em pessoas."

	@javascript
	Scenario: Não permitir duas pessoas com o mesmo nome

		When I add the person "Pessoa 001"
		Then I should be on "/app/peoples/form"
		Then I should see "Name must be unique"