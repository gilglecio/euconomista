<?php

use App\Util\Toolkit;

class Release extends Model
{
	const STATUS_ABERTO = 1;
	const STATUS_LIQUIDADO = 2;

	public static $belongs_to = [
		['user'],
        ['people'],
        ['category']
    ];

    public static $has_many = [
        ['logs', 'class_name' => 'ReleaseLog']
    ];

	public static $validates_presence_of = [
		['number'],
		['category_id'],
		['people_id'],
		['value'],
		['natureza'],
		['data_vencimento']
	];

	public static $validates_uniqueness_of = [
		[['entity', 'number', 'data_vencimento', 'people_id']]
	];

	public static $validates_length_of = [
		['number', 'within' => [1, 15]],
	];

	/**
	 * Salva um pessoa no banco de dados.
	 * 
	 * @param array $fields
	 * @return People
	 */
	public static function generate($fields)
	{
		$quantity = (isset($fields['quantity']) && is_numeric($fields['quantity'])) ? (int) $fields['quantity'] : 1;

		/**
		 * @var float
		 */
		$full_value = (float) $fields['value'];

		/**
		 * @var float
		 */
		$value = (float) number_format($full_value / $quantity, 2, '.', '');

		/**
		 * @var float
		 */
		$diff = $full_value - ($value * $quantity);

		/**
		 * @var float
		 */
		$total = $diff + ($value * $quantity);

		if ($total != $full_value) {
			throw new \Exception('Total not match');
		}

		/**
		 * @var \Datetime
		 */
		$vencimento = new \Datetime($fields['data_vencimento']);
		
		/**
		 * @var string
		 */
		$process = Toolkit::uniqHash();

		$releases = [];

		try {
			$connection = static::connection();
			$connection->transaction();

			for ($i=0; $i < $quantity; $i++) {

				if ($i == ($quantity - 1)) {
					$value += $diff;
				}

				/**
				 * @var Release
				 */
				$row = Release::create([
					'number' => $fields['number'] . ($quantity > 1 ? '/' . ($i+1) : ''),
					'value' => $value,
					'natureza' => $fields['natureza'],
					'data_vencimento' => clone $vencimento,
					'people_id' => $fields['people_id'],
					'category_id' => $fields['category_id'],
					'process' => $process
				]);
				
				if ($row->is_invalid()) {
					throw new \Exception($row->errors->full_messages()[0]);
				}

				/**
				 * @var ReleaseLog
				 */
				$log = ReleaseLog::create([
					'release_id' => $row->id,
					'action' => ReleaseLog::ACTION_EMISSAO,
					'value' => $row->value
				]);

				if ($log->is_invalid()) {
					throw new \Exception($log->errors->full_messages()[0]);
				}

				$releases[] = $row;

				$vencimento->add(new \DateInterval('P1M'));
			}

			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}

		return $releases;
	}

	static function liquidar($fields)
	{
		try {

			$connection = static::connection();
			$connection->transaction();

			$release = self::find($fields['release_id']);
			$backup = $release->to_json();

			$partial = $fields['value'] < $release->value;

			if ($partial) {

				$release->value = $release->value - $fields['value'];
				$release->save();

				if ($release->is_invalid()) {
					throw new \Exception($release->errors->full_messages()[0]);
				}
			} else {
				$release->value = $fields['value'] + $release->getSumLiquidacoes();
				$release->status = self::STATUS_LIQUIDADO;
				$release->save();
			}

			$log = ReleaseLog::create([
				'action' => ReleaseLog::ACTION_LIQUIDACAO,
				'release_id' => $release->id,
				'created_at' => $fields['created_at'],
				'value' => $fields['value'],
				'backup' => $backup,
			]);

			if ($log->is_invalid()) {
				throw new \Exception($log->errors->full_messages()[0]);
			}

			$connection->commit();
		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}

	static function rollback($release_id)
	{
		$release = self::find($release_id);

		if (! $release->canDesfazer()) {
			throw new \Exception('Release log empty');
		}

		return $release->getLastLog()->rollback();
	}

	public function getLastLog()
	{
		return ReleaseLog::find('last', ['conditions' => [
			'action = ? and release_id = ?', 
			ReleaseLog::ACTION_LIQUIDACAO, 
			$this->id]
		]);
	}

	public function getSumLiquidacoes()
	{
		return (int) ReleaseLog::find('first', [
			'conditions' => [
				'release_id = ? and action = ?', 
				$this->id, 
				ReleaseLog::ACTION_LIQUIDACAO
			], 
			'select' => 'sum(value) as total'
		])->total;
	}

	public function getNaturezaName()
	{
		return [
            1 => 'Receita', 
            2 => 'Despesa'
        ][$this->natureza];
	}

	public function getStatusName()
	{
		return [
	        self::STATUS_ABERTO => 'Aberto',
	        self::STATUS_LIQUIDADO => 'Pago',
	        3 => 'Vencido'
	    ][$this->status];
	}

	public function getColor()
	{
		return [
            1 => 'blue', 
            2 => 'red'
        ][$this->natureza];
	}

	public function canLiquidar()
	{
		return ! $this->isLiquidado();
	}

	public function canDesfazer()
	{
		return $this->getLastLog()->action == ReleaseLog::ACTION_LIQUIDACAO;
	}

	public function isLiquidado()
	{
		return $this->status == self::STATUS_LIQUIDADO;
	}
}