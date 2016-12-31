<?php

/**
 * Abstracti Model class.
 *
 * @uses App\Auth\AuthSession
 * @uses ActiveRecord\SQLBuilder
 * @uses UserLog
 */

use App\Auth\AuthSession;
use ActiveRecord\SQLBuilder;

use UserLog;

/**
 * Classe abstrata para o models.
 *
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
abstract class Model extends ActiveRecord\Model
{
    /**
     * Quando um registro e apagado ou editado, o registro fica armzenado para ser backpiado em log.
     *
     * @var ActiveRecord\Model
     */
    public $backup_for_log;

    /**
     * Callbacks invocados antes do registro ser atualizado.
     *
     * @var array
     */
    public static $before_update = [
        'saveBackup'
    ];

    /**
     * Callbacks invocados antes do registro ser apagado.
     *
     * @var array
     */
    public static $before_destroy = [
        'saveBackup'
    ];

    /**
     * Callbacks invocados antes do registro ser criado.
     *
     * @var array
     */
    public static $before_create = [
        'setUserAndEntity'
    ];

    /**
     * Callbacks invocados depois que o registro é criado.
     *
     * @var array
     */
    public static $after_create = [
        'userLogCreate'
    ];

    /**
     * Callbacks invocados depois que o registro é atualizado.
     *
     * @var array
     */
    public static $after_update = [
        'userLogUpdate'
    ];

    /**
     * Callbacks invocados depois que o registro é apagado.
     *
     * @var array
     */
    public static $after_destroy = [
        'userLogDestroy'
    ];

    /**
     * Define os relacionamentos 1:N.
     *
     * @var array
     */
    public static $has_many = [];

    /**
     * Define os reacionamentos 1:1.
     *
     * @var array
     */
    public static $has_one = [];

    /**
     * Salva o registro na propriedade `backup_for_log`.
     *
     * @return void
     */
    public function saveBackup()
    {
        $this->backup_for_log = static::find($this->id);
    }

    /**
     * Este método é invocado após uma restauração de backup de registro.
     *
     * @return void
     */
    public function afterRestored()
    {
    }

    /**
     * Generate user log after create.
     *
     * @return void
     */
    public function userLogCreate()
    {
        $this->userLog('create');
    }

    /**
     * Generate user log after update.
     *
     * @return void
     */
    public function userLogUpdate()
    {
        $this->userLog('update');
    }

    /**
     * Generate user log after destroy.
     *
     * @return void
     */
    public function userLogDestroy()
    {
        $this->userLog('destroy');
    }

    /**
     * Generate user log by action.
     *
     * @param string $action
     * @return void
     */
    public function userLog($action)
    {
        UserLog::register([
            'model' => $this,
            'action' => $action
        ]);
    }

    /**
     * Retorna o primeiro erro ocorrido.
     * @return string
     */
    public function getFisrtError()
    {
        $errors = $this->errors->get_raw_errors();

        dd($errors);

        return $errors ? $errors[0] : 'Error';
    }

    /**
     * Adiciona automaticamente a entity
     * e o usuário que está criando o registro.
     *
     * @return void
     */
    public function setUserAndEntity()
    {
        /**
         * @var integer
         */
        $this->entity = AuthSession::getEntity();

        /**
         * @var integer
         */
        $this->user_id = AuthSession::getUserId();
    }

    /**
     * Pecorre os relacionamentos 1:N e 1:1
     * e verifica se o registro está em us por algum deles.
     *
     * @throws \Exception <called class> está send usado por '<relation>'
     * @return void
     */
    public function inUsed()
    {
        /**
         * Relacionamentos
         *
         * @var array
         */
        $relations = array_merge(static::$has_many, static::$has_one);

        foreach ($relations as $relation) {

            /**
             * Nome do relacionamento um para muitos.
             *
             * @var string
             */
            $relation = $relation[0];

            if (count($this->{$relation})) {
                throw new \Exception('Este registro está em uso no sistema.');
            }
        }
    }

    /**
     * Persnalizado para quando exista um usuário autenticado, as consultas ao banco de dados seja feita incluindo o id da `entity` do usuário logado.
     *
     * @return any
     */
    public static function find()
    {
        $class = get_called_class();

        if (func_num_args() <= 0) {
            throw new RecordNotFound("Couldn't find $class without an ID");
        }

        $args = func_get_args();
        $options = static::extract_and_validate_options($args);
        $num_args = count($args);
        $single = true;

        if ($num_args > 0 && ($args[0] === 'all' || $args[0] === 'first' || $args[0] === 'last')) {
            switch ($args[0]) {
                case 'all':
                    $single = false;
                    break;

                case 'last':
                    if (!array_key_exists('order', $options)) {
                        $options['order'] = join(' DESC, ', static::table()->pk) . ' DESC';
                    } else {
                        $options['order'] = SQLBuilder::reverse_order($options['order']);
                    }
                case 'first':
                    $options['limit'] = 1;
                    $options['offset'] = 0;
                    break;
            }

            $args = array_slice($args, 1);
            $num_args--;
        } elseif (1 === count($args) && 1 == $num_args) { //find by pk
            $args = $args[0];
        }

        if (AuthSession::isAuthenticated()) {
            $entity = AuthSession::getEntity();

            if ($num_args > 0 && !isset($options['conditions'])) {
                $options = [
                    'conditions' => [
                        'id = ? and entity = ?',
                        $args,
                        $entity
                    ]
                ];
            } elseif (! isset($options['conditions'])) {
                $options['conditions'] = ['entity = ?', $entity];
            } else {
                $options['conditions'][0] .= ' and entity = ?';
                $options['conditions'][] = $entity;
            }
        }

        $options['mapped_names'] = static::$alias_attribute;
        $list = static::table()->find($options);

        return $single ? (!empty($list) ? $list[0] : null) : $list;
    }

    /**
     * Generic description for user log.
     *
     * @param string $action
     * @return string Description
     */
    public function getLogDescription($action)
    {
        $model = get_called_class();

        return [
            'create' => "Created new row in {$model} model.",
            'update' => "Row #{$this->id} of {$model} updated.",
            'destroy' => "Row #{$this->id} of {$model} deleted.",
        ][$action];
    }
}
