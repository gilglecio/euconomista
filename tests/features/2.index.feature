@index
Feature: Página index
	
	@javascript
	Scenario: Verificando se os links para acessar a página de login e cadastro estão presentes 
		
		Then I should see "HmGestor"
		Then I should see "Login"
		Then I should see "Cadastro"
		Then I should see "Todos os direitos reservados"