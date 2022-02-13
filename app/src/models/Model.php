<?php

use App\Auth\AuthSession;
use ActiveRecord\SQLBuilder;

use UserLog;

abstract class Model extends ActiveRecord\Model
{
    public $backup_for_log;

    public static $before_update = [
        'saveBackup'
    ];

    public static $before_destroy = [
        'saveBackup'
    ];

    public static $before_create = [
        'setUserAndEntity'
    ];

    public static $after_create = [
        'userLogCreate'
    ];

    public static $after_update = [
        'userLogUpdate'
    ];

    public static $after_destroy = [
        'userLogDestroy'
    ];

    public static $has_many = [];

    public static $has_one = [];

    public function saveBackup()
    {
        $this->backup_for_log = static::find($this->id);
    }

    public function afterRestored()
    {
    }

    public function userLogCreate()
    {
        $this->userLog('create');
    }

    public function userLogUpdate()
    {
        $this->userLog('update');
    }

    public function userLogDestroy()
    {
        $this->userLog('destroy');
    }

    public function userLog($action)
    {
        UserLog::register([
            'model' => $this,
            'action' => $action
        ]);
    }

    public function getFisrtError()
    {
        $errors = $this->errors->get_raw_errors();
        $message = $errors[key($errors)][0];
        return $message;
    }

    public function setUserAndEntity()
    {
        $this->entity = AuthSession::getEntity();
        $this->user_id = AuthSession::getUserId();
    }

    public function inUsed()
    {
        $relations = array_merge(static::$has_many, static::$has_one);

        foreach ($relations as $relation) {

            $relation = $relation[0];

            if (count($this->{$relation})) {
                throw new \Exception('Este registro está em uso no sistema.');
            }
        }
    }

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
