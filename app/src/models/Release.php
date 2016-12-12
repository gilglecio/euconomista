<?php

/**
 * Model Release.
 * 
 * @uses App\Util\Toolkit
 */

use App\Util\Toolkit;

/**
 * Esta classe faz referencia a tabela `releases` no banco de dados.
 * 
 * @author Gilglécio Santos de Oliveira <gilglecio.dev@gmail.com>
 */
final class Release extends Model
{
    const STATUS_ABERTO = 1;
    const STATUS_LIQUIDADO = 2;

    /**
     * Registra os relacionamentos 1:1.
     * @var array
     */
    public static $belongs_to = [
        ['user'],
        ['people'],
        ['category']
    ];

    /**
     * Registra os relacionamentos 1:N.
     * @var array
     */
    public static $has_many = [
        ['logs', 'class_name' => 'ReleaseLog']
    ];

    /**
     * Validação de campos obrigatŕios.
     * @var array
     */
    public static $validates_presence_of = [
        ['category_id'],
        ['people_id'],
        ['value'],
        ['natureza'],
        ['data_vencimento']
    ];

    /**
     * Não permite que exista um lançamento na entidade, da mesma pessoa com natureza, data de vencimento e número iguais.
     * 
     * @var array
     */
    public static $validates_uniqueness_of = [
        [['entity', 'natureza', 'data_vencimento', 'people_id', 'number']]
    ];

    /**
     * Callbacks executados antes de um lançamento ser apagado.
     *
     * - O callback `deleteAllLogs` é utilizado para remover os logs de emissão do lançamento.
     * - O callback `saveBackup` é utilizado para armazenar o registro para que seja possível restaurar caso necessário.
     * 
     * @var array
     */
    public static $before_destroy = [
        'deleteAllLogs',
        'saveBackup'
    ];

    /**
     * Apaga todos os logs do lançamento.
     *
     * @return integer Número de registros apagados
     */
    public function deleteAllLogs()
    {
        /**
         * Número de linhas afetadas.
         *
         * @var integer
         */
        return ReleaseLog::delete_all([
            'conditions' => [
                'release_id = ?',
                $this->id
            ]
        ]);
    }

    /**
     * Salva um lançamento no banco de dados.
     *
     * @param array $fields
     * @throws \Exception A soma dos lançamentos não confere com o total do documento.
     * @throws \Exception Mensagem de erro do model.
     * @throws \Exception Lançamento não localizado.
     * @return array Lançamentos gerados
     */
    public static function generate($fields)
    {
        /**
         * Se a quantidade não for infromada, o padrão é 1.
         *
         * @var integer
         */
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
         * Armazena a diferença, quando houver.
         * esta diferença é somada no último lançamento.
         *
         * @var float
         */
        $diff = $full_value - ($value * $quantity);

        /**
         * @var float
         */
        $total = $diff + ($value * $quantity);

        if ($total != $full_value) {
            throw new \Exception('A soma dos lançamentos não confere com o total do documento.');
        }

        /**
         * @var \Datetime
         */
        $vencimento = new \Datetime($fields['data_vencimento']);
        
        /**
         * @var string
         */
        $process = Toolkit::uniqHash();

        /**
         * Armazena os lançamentos criados para retornar.
         *
         * @var array
         */
        $releases = [];

        try {
            $connection = static::connection();
            $connection->transaction();

            /**
             * Se o ID foi passado, é porque se trata de uma edição.
             */
            if (isset($fields['id']) && is_numeric($fields['id'])) {
                
                /**
                 * @var Release
                 */
                if (! $release = self::find($fields['id'])) {
                    throw new \Exception('Lançamento não localizado.');
                }

                /**
                 * Se o usuário não alterou a quantidade de parcelas
                 * o lançamento vai contnuar com o mesmo numero de processo.
                 */
                if ($quantity == 1) {
                    $process = $release->process;
                }

                /**
                 * O lançamento e completamente apagado
                 * para dar lugar ao novo lançamento.
                 */
                if (! $release->delete()) {
                    throw new \Exception('Falha ao apagar o lançamento.');
                }
            }

            for ($i=0; $i < $quantity; $i++) {
                if ($i == ($quantity - 1)) {
                    $value += $diff;
                }

                /**
                 * @var Release
                 */
                $row = self::create([
                    'number' => ($i + 1) . '/' . $quantity,
                    'value' => $value,
                    'natureza' => $fields['natureza'],
                    'data_vencimento' => clone $vencimento,
                    'people_id' => $fields['people_id'],
                    'category_id' => $fields['category_id'],
                    'description' => $fields['description'],
                    'process' => $process
                ]);
                
                if ($row->is_invalid()) {
                    throw new \Exception($row->errors->full_messages()[0]);
                }

                /**
                 * Gera o log de emissão.
                 *
                 * @var ReleaseLog
                 */
                $log = ReleaseLog::create([
                    'release_id' => $row->id,
                    'date' => date('Y-m-d'),
                    'action' => ReleaseLog::ACTION_EMISSAO,
                    'value' => $row->value
                ]);

                if ($log->is_invalid()) {
                    throw new \Exception($log->errors->full_messages()[0]);
                }

                $releases[] = $row;

                /**
                 * Adiciona 1 mês a data de vencimento a cada loop.
                 */
                $vencimento->add(new \DateInterval('P1M'));
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return $releases;
    }

    /**
     * Liquida um lançamento.
     * Aceita liquidação parcial.
     * Aceita pagamento maior que o valor do lançamento,
     * neste caso entende-se que seja encargos.
     *
     * @param array $fields
     * @throws \Exception Mensagem de erro do model.
     * @throws \Exception Lançamento não localizado.
     * @return boolean
     */
    public static function liquidar($fields)
    {
        try {
            $connection = static::connection();
            $connection->transaction();

            /**
             * @var Release
             */
            if (! $release = self::find($fields['release_id'])) {
                throw new \Exception('Lançamento não localizado.');
            }

            /**
             * Todo o registro do lançamento em formato JSON.
             *
             * @var string
             */
            $backup = $release->to_json();

            /**
             * Quando o usuário liquida um vlaor menor que o valor do lançamento.
             *
             * @var boolean
             */
            $partial = $fields['value'] < $release->value;

            /**
             * Quando a liquidação é parcial,
             * o valor do lançamento é atualizado.
             * O novo valor do lançamento é o valor atual menos o valor pago.
             *
             * Quando a liquidação não é parcial,
             * O valor do lançamento é atualizado para
             * a soma de todas as liquidações feitas.
             */
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

            /**
             * @var ReleaseLog
             */
            $log = ReleaseLog::create([
                'action' => ReleaseLog::ACTION_LIQUIDACAO,
                'release_id' => $release->id,
                'date' => $fields['date'],
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

        return true;
    }

    /**
     * Cancela o ultimo log do lançamento.
     *
     * @param integer $release_id
     * @throws \Exception Não foi possível cancelar o ultimo log do lançamento.
     * @throws \Exception Lançamento não localizado.
     * @return boolean
     */
    public static function rollback($release_id)
    {
        if (! $release = self::find($release_id)) {
            throw new \Exception('Lançamento não localizado.');
        }

        if (! $release->canDesfazer()) {
            throw new \Exception('Não foi possível cancelar o ultimo log do lançamento.');
        }

        return $release->getLastLog()->rollback();
    }

    /**
     * Apaga um lançamento específico,
     * caso o lançamento foi lançado parcelado
     * ao alterar $delete_all_by_process_key para TRUE
     * todos os lançamentos serão apagados.
     *
     * @param integer $release_id
     * @param boolean $delete_all_by_process_key Se TRUE, apaga todos os lançamentos que possui o mesmo numero de processo que o lançamento passado pelo $release_id.
     * @throws \Exception O lançamento '{$release->number}' foi movimentado.
     * @throws \Exception Falha ao apagar o lançamento '{$relase->number}'.
     * @throws \Exception Lançamento não localizado.
     * @return void
     */
    public static function remove($release_id, $delete_all_by_process_key = false)
    {
        /**
         * @var Release
         */
        if (! $release = self::find($release_id)) {
            throw new \Exception('Lançamento não localizado.');
        }

        if ($delete_all_by_process_key) {
            try {
                $connection = static::connection();
                $connection->transaction();
                
                /**
                 * @var array
                 */
                $releases = self::find('all', [
                    'conditions' => [
                        'process = ?',
                        $release->process
                    ]
                ]);

                foreach ($releases as $release) {
                    if (! $release->canDelete()) {
                        throw new \Exception("O lançamento '{$release->number}' foi movimentado.");
                    }

                    if (! $release->delete()) {
                        throw new \Exception("Falha ao apagar o lançamento '{$relase->number}'.");
                    }
                }

                $connection->commit();
            } catch (\Exception $e) {
                $connection->rollback();
                throw $e;
            }

            return;
        }

        if (! $release->canDelete()) {
            throw new \Exception("O lançamento '{$release->number}' foi movimentado.");
        }

        if (! $release->delete()) {
            throw new \Exception("Falha ao apagar o lançamento '{$relase->number}'.");
        }

        return true;
    }

    /**
     * Verifica se o lançamento pode ser apagado. quanquer lançamento pode ser apagado basta ele não ter nenhum log de quitação.
     * 
     * @return boolean
     */
    public function canDelete()
    {
        return is_null($this->getLastLog());
    }

    /**
     * Retorna o ultimo log do lançamento.
     * Não leva em consideração o log de emissão do lançamento.
     *
     * @return ReleaseLog
     */
    public function getLastLog()
    {
        return ReleaseLog::find('last', [
            'conditions' => [
                'action <> ? and release_id = ?',
                ReleaseLog::ACTION_EMISSAO,
                $this->id
            ]
        ]);
    }

    /**
     * Retorna a soma de todas as liquidações
     * fetias para o lançamento.
     *
     * @return float
     */
    public function getSumLiquidacoes()
    {
        return (float) ReleaseLog::find('first', [
            'conditions' => [
                'release_id = ? and action = ?',
                $this->id,
                ReleaseLog::ACTION_LIQUIDACAO
            ],
            'select' => 'sum(value) as total'
        ])->total;
    }

    /**
     * Retorna o valor da coluna natureza por extenso.
     *
     * @return string
     */
    public function getNaturezaName()
    {
        return [
            1 => 'Receita',
            2 => 'Despesa'
        ][$this->natureza];
    }

    /**
     * Recadastra o log de emissão do lançamento.
     * 
     * @return void
     */
    public function afterRestored()
    {
        $log = ReleaseLog::create([
            'release_id' => $this->id,
            'date' => $this->created_at,
            'action' => ReleaseLog::ACTION_EMISSAO,
            'value' => $this->value
        ]);

        if ($log->is_invalid()) {
            throw new \Exception($log->errors->full_messages()[0]);
        }
    }

    /**
     * Retorna o valor da coluna status por extenso.
     *
     * @return string
     */
    public function getStatusName()
    {
        $status = $this->status;

        if (! $this->isLiquidado() && $this->data_vencimento < (new \Datetime(date('Y-m-d')))) {
            $status = 3;
        }

        return [
            self::STATUS_ABERTO => 'Aberto',
            self::STATUS_LIQUIDADO => 'Pago',
            3 => 'Vencido'
        ][$status];
    }

    /**
     * Formata os logs de quitação para exibição o extrato.
     * 
     * @return array
     */
    public static function extract()
    {
        $rows = ReleaseLog::find('all', [
            'order' => 'date asc',
            'conditions' => [
                'action <> ?',
                ReleaseLog::ACTION_EMISSAO
            ]
        ]);

        $saldo = 0;

        return array_map(function ($r) use (&$saldo) {
            $value = $r->value;

            if ($r->release->natureza == 2) {
                $value *= -1;
            }

            $saldo += $value;

            return [
                'date' => $r->date->format('d/m/Y'),
                'saldo' => Toolkit::showMoney($saldo),
                'value' => Toolkit::showMoney($value),
                'color' => $value < 0 ? 'red' : 'blue'
            ];
        }, $rows);
    }

    /**
     * Retorna uma cor de identifcação
     * com base na natureza do lanamento.
     *
     * @return string
     */
    public function getColor()
    {
        return [
            1 => 'blue',
            2 => 'red'
        ][$this->natureza];
    }

    /**
     * Apenas lançamentos abertos podem ser liquidados.
     *
     * @return boolean
     */
    public function canLiquidar()
    {
        return ! $this->isLiquidado();
    }

    /**
     * Só é possivel desfazer lançamentos com liquidações.
     *
     * @return boolean
     */
    public function canDesfazer()
    {
        return $this->getLastLog()->action == ReleaseLog::ACTION_LIQUIDACAO;
    }

    /**
     * Só é possivel ediar lançamentos sem movimentação.
     *
     * @return boolean
     */
    public function canEditar()
    {
        $count = ReleaseLog::count([
            'conditions' => [
                'release_id = ?',
                $this->id
            ]
        ]);

        return $count <= 1;
    }

    /**
     * Verifica se o lançamento está liquidado.
     *
     * @return boolean
     */
    public function isLiquidado()
    {
        return $this->status == self::STATUS_LIQUIDADO;
    }

    /**
     * Personalizando a descrição do log de usuário.
     * 
     * @param string $action
     * @return string Description
     */
    public function getLogDescription($action)
    {
        if ($action == 'destroy') {
            return 'Apagou o lançamento nº ' . $this->number . ', #' . $this->id . '.';    
        }

        return null;
    }
}
