<?php

use App\Util\Toolkit;

final class ReleaseLog extends Model
{
	const ACTION_EMISSAO = 1;
	const ACTION_LIQUIDACAO = 2;

	/**
     * @var array
     */
	public static $belongs_to = [
        ['release'],
        ['user']
    ];

    /**
     * @var array
     */
	public static $validates_presence_of = [
		['action'],
		['value'],
		['date'],
		['release_id']
	];

	/**
	 * Retorna o valor da coluna `action` por extenso.
	 * Em caso de log de liquidação, é feito um tratamento com base na natureza do lançamento. 
	 * 
	 * @return string
	 */
	public function getActionName()
	{
		return [
			self::ACTION_EMISSAO => 'Emissão',
			self::ACTION_LIQUIDACAO => $this->release->natureza == 1 ? 'Recebimento' : 'Pagamento'
		][$this->action];
	}

	/**
	 * Aplica o backup no lançamento.
	 * Logo após o log é apagado.
	 *
	 * @throws \Exception Falha ao apagar o log #{$this->id}.
	 * @throws \Exception Mensagem de erro do model.
	 * @throws \Exception Lançamento não localizado.
	 * @return boolean
	 */
	public function rollback()
	{
		try {

			$connection = static::connection();
			$connection->transaction();
			
			/**
			 * Estado do lançamento antes do log ser realizado.
			 * 
			 * @var array
			 */
			$backup = (array) json_decode($this->backup);

			/**
			 * @var Release
			 */
			if (! $release = Release::find($backup['id'])) {
				throw new \Exception('Lançamento não localizado.');
			}

			foreach ($backup as $key => $value) {
				$release->$key = $value;
			}

			$release->save();

			if ($release->is_invalid()) {
				throw new \Exception($release->errors->full_messages()[0]);
			}

			if (! $this->delete()) {
				throw new \Exception("Falha ao apagar o log #{$this->id}.", 1);
			}

			$connection->commit();

		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}

		return true;
	}

	/**
	 * Personaliza a descrição do log.
	 * 
	 * @param string $action
	 * @return string Description
	 */
	public function getLogDescription($action) 
	{
		$natureza = strtolower($this->release->getNaturezaName());
        $value = Toolkit::showMoney($this->value);

        $name = $this->getActionName();

        $destroy = "Apagou o lançamento nº {$this->release->number}, #{$this->id}.";

        if ($this->action == self::ACTION_LIQUIDACAO) {
        	$destroy = 'Cancelou o ' . strtolower($name) . ' de R$ ' . $value . ' do lançamento nº ' . $this->release->number . ', #' . $this->id;
        }

        return [
            'create' => "{$name} {$natureza} nº {$this->release->number} '{$this->release->people->name}' R$ {$value}, #{$this->id}.",
            'destroy' => $destroy,
        ][$action];
	}
}