@index
Feature: Página index
	
	@javascript
	Scenario: Verificando se os links para acessar a página de login e cadastro estão presentes 
		
		Then I should see "EuConomista"
		Then I should see "Entrar"
		Then I should see "Fazer meu cadastro"