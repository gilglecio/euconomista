@reset
Feature: Zera o banco de dados
	
	@javascript
	Scenario: Acessar rota para apagar todos os dados do banco de dados.
		
		When resetting the database I should see the text OK on screen