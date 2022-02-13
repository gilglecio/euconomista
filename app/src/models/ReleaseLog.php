<?php

use App\Util\Toolkit;

final class ReleaseLog extends Model
{
    const ACTION_EMISSAO = 1;
    const ACTION_LIQUIDACAO = 2;
    const ACTION_ENCARGO = 3;
    const ACTION_DESCONTO = 4;
    const ACTION_GROUPED = 5;
    const ACTION_PRORROGAR = 6;
    const ACTION_PARCELAR = 7;

    public static $belongs_to = [
        ['release'],
        ['user']
    ];

    public static $has_many = [
        ['childs', 'class_name' => 'ReleaseLog', 'foreign_key' => 'parent_id']
    ];

    public static $validates_presence_of = [
        ['action'],
        ['value'],
        ['date'],
        ['release_id']
    ];

    public function getActionName()
    {
        return [
            self::ACTION_EMISSAO => 'Emissão',
            self::ACTION_ENCARGO => 'Encargos',
            self::ACTION_PARCELAR => 'Parcelamento',
            self::ACTION_GROUPED => 'Agrupamento',
            self::ACTION_DESCONTO => 'Desconto',
            self::ACTION_PRORROGAR => 'Prorrogação',
            self::ACTION_LIQUIDACAO => $this->release->natureza == 1 ? 'Recebimento' : 'Pagamento'
        ][$this->action];
    }

    /**
     * Aplica o backup no lançamento.
     * Logo após o log é apagado.
     */
    public function rollback()
    {
        /**
         * Estado do lançamento antes do log ser realizado.
         */
        $backup = (array) json_decode($this->backup);

        if (! $release = Release::find($backup['id'])) {
            throw new \Exception('Lançamento não localizado.');
        }

        foreach ($backup as $key => $value) {
            $release->$key = $value;
        }

        $release->save();

        if ($release->is_invalid()) {
            throw new \Exception($release->getFisrtError());
        }

        $this->deleteChilds();

        if (! $this->delete()) {
            throw new \Exception("Falha ao apagar o log #{$this->id}.", 1);
        }

        /**
         * Se a ação a ser cancelada é uma ação de parcelamento de lançamento,
         * além derestaurar o lançamento, os lançamentos/parcelas emitidos devem ser apagados.
         */
        if ($this->action == self::ACTION_PARCELAR) {
            foreach ($this->release->parcelamento_parcelas as $release) {
                Release::remove($release->id);
            }
        }

        return true;
    }

    public function deleteChilds()
    {
        foreach ($this->childs as $log) {
            $log->delete();
        }
    }

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
