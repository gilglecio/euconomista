<?php

use App\Util\Toolkit;

class Release extends Model
{
	public static $belongs_to = [
        ['people'],
        ['category']
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

		$connection = static::connection();

		$releases = [];

		try {
			$connection->transaction();

			for ($i=0; $i < $quantity; $i++) {

				if ($i == ($quantity - 1)) {
					$value += $diff;
				}

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

	public static function grid()
	{
		return array_map(function ($r) {

			$row = $r->to_array();

			$row['people'] = $r->people->name;
			$row['category'] = $r->category->name;
			
			$row['natureza'] = [
				1 => 'Receita', 
				2 => 'Despesa'
			][$r->natureza];

			if ($r->data_vencimento < (new \Datetime(date('Y-m-d')))) {
				$row['status'] = 3;
			}

			$row['vencimento'] = $r->data_vencimento->format('d/m/Y');
			$row['value'] = number_format($row['value'], 2, ',', '.');

			$row['status'] = [
				1 => 'Aberto',
				2 => 'Pago',
				3 => 'Vencido'
			][$row['status']];

			$row['color'] = [
				1 => 'blue', 
				2 => 'red'
			][$r->natureza];

			return $row;

		}, self::find('all', ['order' => 'data_vencimento asc']));
	}
}