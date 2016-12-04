<?php

class ReleaseLog extends Model
{
	const ACTION_EMISSAO = 1;
	const ACTION_LIQUIDACAO = 2;

	public static $belongs_to = [
        ['release'],
        ['user']
    ];

	public static $validates_presence_of = [
		['action'],
		['value'],
		['release_id']
	];

	public function getActionName()
	{
		return [
			self::ACTION_EMISSAO => 'Emissão',
			self::ACTION_LIQUIDACAO => $this->release->natureza == 1 ? 'Recebimento' : 'Pagamento'
		][$this->action];
	}

	public function rollback()
	{
		try {

			$connection = static::connection();
			$connection->transaction();
			
			$backup = (array) json_decode($this->backup);

			$release = Release::find($backup['id']);

			foreach ($backup as $key => $value) {
				$release->$key = $value;
			}

			$release->save();

			$this->delete();

			$connection->commit();

		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}

		return true;
	}
}