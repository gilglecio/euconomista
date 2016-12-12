<?php

/**
 * UserLog model
 * @uses App\Auth\AuthSession
 */

use App\Auth\AuthSession;

/**
 * Esta classe faz referencia a tabela `user_logs` no banco de dados.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
class UserLog extends Model
{
	/**
	 * Define os relacionamentos 1:1.
	 * @var array
	 */
	public static $belongs_to = [
		['user']
	];

	/**
	 * Faz a restauração de um backup.
	 * 
	 * @param integer $user_log_id Identificador do log
	 * @throws \Exception Backup empty.
	 * @throws \Exception Os logs devem ser restaurados do último para o primeiro.
	 * @return Model
	 */
	public static function restore($user_log_id)
	{
		/**
		 * @var UserLog
		 */
		$log = self::find($user_log_id);

		if (is_null($log->backup_json)) {
			throw new \Exception('Backup empty.');
		}

		if (! $log->isLastLog()) {
			throw new \Exception('Os logs devem ser restaurados do último para o primeiro.');
		}

		/**
		 * @var string
		 */
		$model = $log->class_name;

		/**
		 * @var backup
		 */
		$backup = (array) json_decode($log->backup_json);

		try {
			$connection = static::connection();
            $connection->transaction();

            if ($object = $model::find($backup['id'])) {
            	
            	foreach ($backup as $key => $value) {
            		if (! in_array($key, ['id', 'entity'])) {
            			$object->$key = $value;
            		}
            	}

            	$object->save();
            } else {

            	/**
            	 * @var Model
            	 */
            	$object = $model::create($backup);
            }

			if ($object->is_invalid()) {
	            throw new \Exception($object->errors->full_messages()[0]);
	        }

	        $object->afterRestored();

	        /**
	         * @var \Datetime
	         */
	        $log->restored_at = new \Datetime();

	        $log->save();

	        if ($log->is_invalid()) {
	            throw new \Exception($log->errors->full_messages()[0]);
	        }

            $connection->commit();

		} catch (\Exception $e) {
            $connection->rollback();
            throw $e;
		}

        return $object;
	}

	/**
	 * Cadastra um log do tipo `login`.
	 * 
	 * @return UserLog
	 */
	public static function logout()
	{
		if (! User::find(AuthSession::getUserId())) {
			return null;
		}

		$log = self::create([
			'action' => 'logout',
			'class_name' => AuthSession::class,
			'row_id' => AuthSession::getUserId(),
			'description' => 'Desconectou-se'
		]);

		if ($log->is_invalid()) {
            throw new \Exception($log->errors->full_messages()[0]);
        }

        return $log;
	}

	/**
	 * Cadastra um log do tipo `logout`.
	 * 
	 * @return UserLog
	 */
	public static function login()
	{
		$log = self::create([
			'action' => 'login',
			'class_name' => AuthSession::class,
			'row_id' => AuthSession::getUserId(),
			'description' => 'Conectou-se'
		]);

		if ($log->is_invalid()) {
            throw new \Exception($log->errors->full_messages()[0]);
        }

        return $log;
	}

	/**
	 * Registra um log no banco de dados.
	 * 
	 * @param array $data Informações do log.
	 * @return UserLog
	 */
	public static function register($data)
	{
		/**
		 * Descrição personalizada para cada action.
		 * @var string
		 */
		$description = $data['model']->getLogDescription($data['action']);

		if (is_null($description)) {
			return;
		}

		if (is_null($data['model']->user_id)) {
			return;
		}

		/**
		 * Nome do model
		 * @var string
		 */
		$class_name = get_class($data['model']);

		/**
		 * Evita looping infinito.
		 */
		if ($class_name == self::class) {
			return;
		}

		/**
		 * @var null|string
		 */
		$backup_json = null;

		if (in_array($data['action'], ['update', 'destroy'])) {
			$backup_json = json_encode($data['model']->backup_for_log->to_array());
		}

		/**
		 * @var UserLog
		 */
		$log = self::create([
			'action' => $data['action'],
			'class_name' => $class_name,
			'row_id' => $data['model']->id,
			'backup_json' => $backup_json,
			'description' => $description,
		]);

		if ($log->is_invalid()) {
            throw new \Exception($log->errors->full_messages()[0]);
        }

        return $log;
	}

	/**
	 * Verifica se o log é o último, para isso é verificado se existe algum log que a coluna `class_name` e `row_id` possua o id maior que o log atual.
	 * @return boolean Retorna TRUE se o log for o último.
	 */
	public function isLastLog()
	{
		$find = self::find('last', [
			'conditions' => [
				'`row_id` = ? and `class_name` = ?',
				$this->row_id,
				$this->class_name
			]
		]);

		return $find->id == $this->id;
	}

	/**
	 * Apenas logs com backups não restaurado podem ser restaurado.
	 * 
	 * @return boolean
	 */
	public function canRestore()
	{
		if (is_null($this->restored_at) && ! is_null($this->backup_json)) {
			return true;
		}

		return false;
	}
}