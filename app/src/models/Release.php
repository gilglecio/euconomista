<?php

use App\Util\Toolkit;

final class Release extends Model
{
    const STATUS_ABERTO = 1;
    const STATUS_LIQUIDADO = 2;
    const STATUS_EM_ATRASO = 3;
    const STATUS_GROUPED = 4;
    const STATUS_PARCELADO = 5;

    const RECEITA = 1;
    const DESPESA = 2;

    // Column [parent_id]
    // É preenchida com o id do lançamento que foi parcaldo,
    // onde os lançamentos gerados recebem o id do lançamento de origem.

    // Column [child_id]
    // O utilizado pelos lançamentos que foram agrupados,
    // onde os lançamentos agrupados recebe o id do lançamento resultante.

    public static $belongs_to = [
        ['user'],
        ['parcelado', 'class_name' => 'Release', 'foreign_key' => 'parent_id'],
        ['people'],
        ['category']
    ];

    public static $has_many = [
        ['logs', 'class_name' => 'ReleaseLog'],
        ['releases', 'foreign_key' => 'child_id'],
        ['parcelamento_parcelas', 'class_name' => 'Release', 'foreign_key' => 'parent_id']
    ];

    public static $has_one = [
        [
            'log_emissao',
            'class_name' => 'ReleaseLog',
            [
                'conditions' => [
                    'action = ?',
                    ReleaseLog::ACTION_EMISSAO
                ]
            ]
        ]
    ];

    public static $validates_presence_of = [
        ['category_id', 'message' => 'Favor informar a categoria'],
        ['people_id', 'message' => 'Favor informar a pessoa'],
        ['value', 'message' => 'Favor informar um valor'],
        ['natureza', 'message' => 'Favor informar a natureza'],
        ['data_vencimento', 'Favor informar a data de vencimento']
    ];

    public static $validates_uniqueness_of = [
        [['entity', 'natureza', 'data_vencimento', 'people_id', 'number']]
    ];

    public static $before_destroy = [
        'deleteAllLogs',
        'saveBackup'
    ];

    public function deleteAllLogs()
    {
        return ReleaseLog::delete_all([
            'conditions' => [
                'release_id = ?',
                $this->id
            ]
        ]);
    }

    public static function generateGroup($fields)
    {
        if (! isset($fields['releases'])) {
            $fields['releases'] = [];
        }

        if (count($fields['releases']) < 2) {
            throw new \Exception('Favor selecionar pelo menos dois lançamentos.');
        }

        try {
            $connection = static::connection();
            $connection->transaction();

            $value = 0;
            $natureza = [];
            $releases = [];

            foreach ($fields['releases'] as $release_id) {
                $release = self::find($release_id);
                $natureza[$release->natureza] = true;
                $releases[] = $release;
                $value += $release->value;
            }


            if (count($natureza) > 1) {
                throw new \Exception('Favor selecionar lançamentos da mesma natureza.');
            }

            $row = self::create([
                'number' => $fields['number'],
                'value' => $value,
                'natureza' => key($natureza),
                'data_vencimento' => $fields['data_vencimento'],
                'people_id' => $fields['people_id'],
                'category_id' => $fields['category_id'],
                'description' => $fields['description']
            ]);
            
            if ($row->is_invalid()) {
                throw new \Exception($row->getFisrtError());
            }

            $emissao = ReleaseLog::create([
                'release_id' => $row->id,
                'date' => $fields['data_emissao'],
                'action' => ReleaseLog::ACTION_EMISSAO,
                'value' => $value
            ]);
            
            if ($emissao->is_invalid()) {
                throw new \Exception($emissao->getFisrtError());
            }

            if (!! $fields['data_liquidacao']) {

                /**
                 * Faz a liquidação automática do lançamento.
                 */
                $liquidacao = ReleaseLog::create([
                    'release_id' => $row->id,
                    'backup' => json_encode($row->to_array()),
                    'date' => $fields['data_liquidacao'],
                    'action' => ReleaseLog::ACTION_LIQUIDACAO,
                    'value' => $row->value
                ]);

                if ($liquidacao->is_invalid()) {
                    throw new \Exception($liquidacao->getFisrtError());
                }

                $row->status = self::STATUS_LIQUIDADO;
                $row->save();
            }

            foreach ($releases as $release) {

                $backup = json_encode($release->to_array());

                $release->child_id = $row->id;
                $release->status = self::STATUS_GROUPED;
                $release->save();

                if ($release->is_invalid()) {
                    throw new \Exception($release->getFisrtError());
                }

                $grouped = ReleaseLog::create([
                    'release_id' => $release->id,
                    'backup' => $backup,
                    'date' => $fields['data_emissao'],
                    'action' => ReleaseLog::ACTION_GROUPED,
                    'value' => $release->value
                ]);
                
                if ($grouped->is_invalid()) {
                    throw new \Exception($grouped->getFisrtError());
                }
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }
    }

    public static function generate($fields, $connection = null)
    {
        /**
         * Se a quantidade não for infromada, o padrão é 1.
         */
        $quantity = (isset($fields['quantity']) && is_numeric($fields['quantity'])) ? (int) $fields['quantity'] : 1;

        if ($quantity < 1) {
            throw new \Exception('A quantidade deve ser maior igual a 1');
        }

        $full_value = (float) $fields['value'];

        $value = (float) number_format($full_value / $quantity, 2, '.', '');

        /**
         * Armazena a diferença, quando houver.
         * esta diferença é somada no último lançamento.
         */
        $diff = $full_value - ($value * $quantity);

        $total = $diff + ($value * $quantity);

        if ($total != $full_value) {
            throw new \Exception('A soma dos lançamentos não confere com o total do documento.');
        }

        $vencimento = new \Datetime($fields['data_vencimento']);
        $dia_vencimento = $vencimento->format('d');
        $vencimento = new \Datetime(date($vencimento->format('Y-m-15')));
        
        $process = Toolkit::uniqHash();

        /**
         * Armazena os lançamentos criados para retornar.
         */
        $releases = [];

        /**
         * Este método aceita transação externa.
         * Flag para indicar que a transação foi iniciada neste método.
         */
        $inner_connection = false;

        try {

            /**
             * Se a conexão não foi passada, ela é devidamente criada.
             */
            if (! $connection) {
                $inner_connection = true;
                $connection = static::connection();
                $connection->transaction();
            }

            $isEdit = isset($fields['id']) && is_numeric($fields['id']);

            $release_number = null;

            /**
             * Se o ID foi passado, é porque se trata de uma edição.
             */
            if ($isEdit) {
                
                if (! $release = self::find($fields['id'])) {
                    throw new \Exception('Lançamento não localizado.');
                }

                /**
                 * Se o usuário não alterou a quantidade de parcelas
                 * o lançamento vai continuar com o mesmo numero de processo.
                 */
                if ($quantity == 1) {
                    $process = $release->process;
                    $release_number = $release->number;
                }

                /**
                 * O lançamento e completamente apagado
                 * para dar lugar ao novo lançamento.
                 */
                if (! $release->delete()) {
                    throw new \Exception('Falha ao apagar o lançamento.');
                }
            }

            /**
             * Se não for uma edição de lançamento e quantidade for uma.
             * O número do processo só se faz necessário em lançamentos parcelados,
             * pois é a única forma de saber quais lançamentos estão envolvidos.
             */
            if (! $isEdit && $quantity == 1) {
                $process = null;
            }

            /**
             * Sera TRUE se a data de liquidação for informada.
             */
            $liquidar = !! $fields['data_liquidacao'];

            for ($i=0; $i < $quantity; $i++) {

                /**
                 * Numero sequencial / quantidade de parcelas.
                 */
                $number = str_pad(($i + 1) . '/' . $quantity, 5, '0', STR_PAD_LEFT);

                /**
                 * Se for uma edição na qual o usuário não redividiu o lançamento,
                 * o numeração do lançamento não deve ser alterada.
                 */
                if ($isEdit && $quantity == 1) {
                    $number = $release_number;
                }

                /**
                 * Adiciona a diferença da dízima na última parcela.
                 */
                if ($i == ($quantity - 1)) {
                    $value += $diff;
                }

                $dia = $dia_vencimento;
                $day_of_month = $vencimento->format('t');

                if ($dia_vencimento > $day_of_month) {
                    $dia = $day_of_month;
                }

                $row = self::create([
                    'number' => $number,
                    'value' => $value,
                    'natureza' => $fields['natureza'],
                    'data_vencimento' => new \Datetime(date($vencimento->format('Y-m-' . $dia))),
                    'people_id' => $fields['people_id'],
                    'category_id' => $fields['category_id'],
                    'description' => $fields['description'],
                    'parent_id' => isset($fields['parent_id']) ? $fields['parent_id'] : null,
                    'process' => $process
                ]);
                
                if ($row->is_invalid()) {
                    throw new \Exception($row->getFisrtError());
                }

                /**
                 * Gera o log de emissão.
                 */
                $emissao = ReleaseLog::create([
                    'release_id' => $row->id,
                    'date' => $fields['data_emissao'],
                    'action' => ReleaseLog::ACTION_EMISSAO,
                    'value' => $row->value
                ]);
                
                if ($emissao->is_invalid()) {
                    throw new \Exception($emissao->getFisrtError());
                }
                
                if ($liquidar) {
                    $backup = json_encode($row->to_array());

                    /**
                     * Faz a liquidação automática do lançamento.
                     */
                    $liquidacao = ReleaseLog::create([
                        'release_id' => $row->id,
                        'backup' => $backup,
                        'date' => $fields['data_liquidacao'],
                        'action' => ReleaseLog::ACTION_LIQUIDACAO,
                        'value' => $row->value
                    ]);

                    if ($liquidacao->is_invalid()) {
                        throw new \Exception($liquidacao->getFisrtError());
                    }

                    /**
                     * Altera o status do lançamento para liquidado.
                     */
                    $row->status = self::STATUS_LIQUIDADO;
                    $row->save();
                }

                $releases[] = $row;

                /**
                 * Adiciona 1 mês a data de vencimento a cada loop.
                 */
                $vencimento->add(new \DateInterval('P1M'));
            }

            if ($inner_connection) {
                $connection->commit();
            }
        } catch (\Exception $e) {
            if ($inner_connection) {
                $connection->rollback();
            }

            throw $e;
        }


        return $releases;
    }

    /**
     * Liquida um lançamento.
     * Aceita liquidação partial.
     * Aceita pagamento maior que o valor do lançamento,
     * neste caso entende-se que seja encargos.
     */
    public static function liquidar($fields)
    {
        try {
            $connection = static::connection();
            $connection->transaction();

            if (! $release = self::find($fields['release_id'])) {
                throw new \Exception('Lançamento não localizado.');
            }

            /**
             * Todo o registro do lançamento em formato JSON.
             */
            $backup = $release->to_json();

            /**
             * Quando o usuário liquida um vlaor menor que o valor do lançamento.
             */
            $partial = $fields['value'] < $release->value;

            /**
             * Quando o valor liquidado é maior que o valor aberto,
             * entende-se que a diferença são encargos.
             */
            $encargos = $fields['value'] - $release->value;

            $log_desconto = $log_encargo = null;

            /**
             * Quando a liquidação é partial,
             * o valor do lançamento é atualizado.
             * O novo valor do lançamento é o valor atual menos o valor pago.
             *
             * Quando a liquidação não é partial,
             * O valor do lançamento é atualizado para
             * a soma de todas as liquidações feitas.
             */
            if ($partial && ! $fields['desconto']) {
                $release->value = $release->value - $fields['value'];
                $release->save();
            } elseif ($partial && $fields['desconto']) {
                $log_desconto = ReleaseLog::create([
                    'action' => ReleaseLog::ACTION_DESCONTO,
                    'release_id' => $release->id,
                    'date' => $fields['date'],
                    'value' => $release->value - $fields['value']
                ]);

                if ($log_desconto->is_invalid()) {
                    throw new \Exception($log_desconto->getFisrtError());
                }

                $release->value = $fields['value'] + $release->getSumLiquidacoes();
                $release->status = self::STATUS_LIQUIDADO;
                $release->save();
            } elseif ($encargos > 0) {
                $release->value = $fields['value'] + $release->getSumLiquidacoes();
                $release->status = self::STATUS_LIQUIDADO;
                $release->save();

                $log_encargo = ReleaseLog::create([
                    'action' => ReleaseLog::ACTION_ENCARGO,
                    'release_id' => $release->id,
                    'date' => $fields['date'],
                    'value' => $encargos,
                    'backup' => $backup,
                ]);

                if ($log_encargo->is_invalid()) {
                    throw new \Exception($log_encargo->getFisrtError());
                }
            } else {
                $release->value = $fields['value'] + $release->getSumLiquidacoes();
                $release->status = self::STATUS_LIQUIDADO;
                $release->save();
            }

            $log_quitacao = ReleaseLog::create([
                'action' => ReleaseLog::ACTION_LIQUIDACAO,
                'release_id' => $release->id,
                'date' => $fields['date'],
                'value' => $fields['value'],
                'backup' => $backup,
            ]);

            if ($log_quitacao->is_invalid()) {
                throw new \Exception($log_quitacao->getFisrtError());
            }

            if ($log_encargo) {
                $log_encargo->parent_id = $log_quitacao->id;
                $log_encargo->save();
            }

            if ($log_desconto) {
                $log_desconto->parent_id = $log_quitacao->id;
                $log_desconto->save();
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * Retorna a data de prorrogação com base na data de vencimento do registro.
     */
    public function getProrrogarDate()
    {
        $now = new \Datetime(date('Y-m-d'));

        if ($this->data_vencimento < $now) {
            return $now->format('Y-m-d');
        }

        return $this->data_vencimento->add(new \Dateinterval('P1D'))->format('Y-m-d');
    }

    public static function prorrogar($fields)
    {
        try {
            $connection = static::connection();
            $connection->transaction();

            if (! $release = self::find($fields['release_id'])) {
                throw new \Exception('Lançamento não localizado.');
            }

            if ($fields['value'] < $release->value) {
                throw new \Exception('Favor informar um valor maior ou igual ao valor do lançamento.');
            }

            $date = new \Datetime($fields['date']);

            if ($date <= $release->data_vencimento) {
                throw new \Exception('Favor informar uma data de vencimento maior que a data de vencimento atual.');
            }
            
            $backup = $release->to_json();

            /**
             * Valor dos encargos, quando o valor é alterado.
             */
            $encargos = $fields['value'] - $release->value;

            $log_encargo = null;

            if ($encargos > 0) {
                $log_encargo = ReleaseLog::create([
                    'action' => ReleaseLog::ACTION_ENCARGO,
                    'release_id' => $release->id,
                    'date' => date('Y-m-d'),
                    'value' => $encargos,
                    'backup' => $backup,
                ]);

                if ($log_encargo->is_invalid()) {
                    throw new \Exception($log_encargo->getFisrtError());
                }
            }

            $release->data_vencimento = $fields['date'];
            $release->value = $fields['value'];

            $release->save();

            if ($release->is_invalid()) {
                throw new \Exception($release->getFisrtError());
            }

            $log = ReleaseLog::create([
                'action' => ReleaseLog::ACTION_PRORROGAR,
                'release_id' => $release->id,
                'date' => date('Y-m-d'),
                'value' => $fields['value'],
                'backup' => $backup,
            ]);

            if ($log->is_invalid()) {
                throw new \Exception($log->getFisrtError());
            }

            if ($log_encargo) {
                $log_encargo->parent_id = $log->id;
                $log->save();
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return true;
    }

    public static function parcelar($fields)
    {
        try {
            $connection = static::connection();
            $connection->transaction();

            $data_emissao = date('Y-m-d');

            if (! $release = self::find($fields['release_id'])) {
                throw new \Exception('Lançamento não localizado.');
            }

            /**
             * A quantidade de parcela não pode ser menor que 2.
             * Caso a quantidade de parcelas for 1, a melhor opção para o usuário é a prorrogação.
             */
            if ($fields['quantity'] < 2) {
                throw new \Exception('Favor informar uma quantidade menor ou igual a 2.');
            }

            $backup = $release->to_json();

            $release->status = self::STATUS_PARCELADO;
            $release->save();

            if ($release->is_invalid()) {
                throw new \Exception($release->getFisrtError());
            }

            $encargos = $fields['encargos'];

            if ($encargos > 0) {

                /**
                 * Caso o parcelamento seja com encargos, o log de encargos é gerado.
                 */
                $log_encargo = ReleaseLog::create([
                    'action' => ReleaseLog::ACTION_ENCARGO,
                    'release_id' => $release->id,
                    'date' => $data_emissao,
                    'value' => $encargos,
                    'backup' => $backup,
                ]);

                if ($log_encargo->is_invalid()) {
                    throw new \Exception($log_encargo->getFisrtError());
                }
            }
            
            /**
             * Valor total do documento mais encargos
             */
            $total = $encargos + $release->value;

            /**
             * Adiciona o log de parcelamento com o valor do documento + encargos
             */
            $log = ReleaseLog::create([
                'action' => ReleaseLog::ACTION_PARCELAR,
                'release_id' => $release->id,
                'date' => $data_emissao,
                'value' => $total,
                'backup' => $backup,
            ]);

            if ($log->is_invalid()) {
                throw new \Exception($log->getFisrtError());
            }

            if ($log_encargo) {
                $log_encargo->parent_id = $log->id;
                $log_encargo->save();
            }

            $generate = [
                'category_id' => $release->category_id,
                'people_id' => $release->people_id,
                'quantity' => $fields['quantity'],
                'natureza' => $release->natureza,
                'value' => $total,
                'data_emissao' => $data_emissao,
                'data_vencimento' => $fields['primeiro_vencimento'],
                'description' => 'Parcelamento',
                'parent_id' => $release->id
            ];

            self::generate($generate, $connection);
            
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * Cancela o ultimo log do lançamento.
     */
    public static function rollback($release_id, $connection = null, $action = null)
    {
        try {
            if (! $release = self::find($release_id)) {
                throw new \Exception('Lançamento não localizado.');
            }

            if (! $release->canDesfazer()) {
                throw new \Exception('Não foi possível cancelar o ultimo log do lançamento.');
            }

            $last = $release->getLastLog();

            if ($action && $last->action != $action) {
                throw new \Exception('A ação do log que será cancelado é diferente da ação especificada.');
            }

            $inner_connection = false;

            if (! $connection) {
                $connection = static::connection();
                $connection->transaction();

                $inner_connection = true;
            }
            
            $last->rollback();

            if ($inner_connection) {
                $connection->commit();
            }
        } catch (\Exception $e) {
            if ($inner_connection) {
                $connection->rollback();
            }
            throw $e;
        }

        return true;
    }

    /**
     * Cancela o agrupamento do lançamento.
     */
    public static function ungroup($release_id)
    {
        if (! $release = self::find($release_id)) {
            throw new \Exception('Lançamento não localizado.');
        }

        if (! $release->canUngroup()) {
            throw new \Exception('Não foi possível desagrupar os lançamentos.');
        }

        try {
            $connection = static::connection();
            $connection->transaction();

            foreach ($release->releases as $row) {
                
                self::rollback($row->id, $connection, ReleaseLog::ACTION_GROUPED);
                
                $row->child_id = null;
                $row->save();
            }

            $release->delete();

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * Apaga um lançamento específico,
     * caso o lançamento foi lançado parcelado
     * ao alterar $delete_all_by_process_key para TRUE
     * todos os lançamentos serão apagados.
     */
    public static function remove($release_id, $delete_all_by_process_key = false)
    {
        if (! $release = self::find($release_id)) {
            throw new \Exception('Lançamento não localizado.');
        }

        if ($delete_all_by_process_key) {
            try {
                $connection = static::connection();
                $connection->transaction();
                
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
     * Verifica se o lançamento pode ser apagado. 
     * quanquer lançamento pode ser apagado basta ele não ter nenhum log de quitação.
     */
    public function canDelete()
    {
        return is_null($this->getLastLog());
    }

    /**
     * Retorna o ultimo log do lançamento.
     * Não leva em consideração o log de emissão do lançamento.
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
     * feitas para o lançamento.
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

    public function getNaturezaName()
    {
        return [
            1 => 'Receita',
            2 => 'Despesa'
        ][$this->natureza];
    }

    /**
     * Recadastra o log de emissão do lançamento.
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
            throw new \Exception($log->getFisrtError());
        }
    }

    public function isParcelamento()
    {
        if ($parcelado = $this->parcelado) {
            if ($parcelado->status == self::STATUS_PARCELADO) {
                return true;
            }
        }

        return false;
    }

    public function isGrouped()
    {
        return !! $this->child_id;
    }

    public function emAtraso()
    {
        return $this->status == self::STATUS_ABERTO && $this->data_vencimento < (new \Datetime(date('Y-m-d')));
    }

    public function getStatusName()
    {
        $status = $this->status;

        /**
         * Se o lançamento não estiver sido agrupado
         * e estiver em atraso, o status é dinamicamente alterado para 'Vencido'.
         */
        if (! $this->isGrouped() && $this->emAtraso()) {
            $status = self::STATUS_EM_ATRASO;
        }

        return [
            self::STATUS_ABERTO => 'Aberto',
            self::STATUS_EM_ATRASO => 'Vencido',
            self::STATUS_GROUPED => 'Agrupada',
            self::STATUS_LIQUIDADO => 'Liquidado',
        ][$status];
    }

    /**
     * Formata os logs de quitação para exibição o extrato.
     */
    public static function extract($year_month = null)
    {
        $conditions = [
            'action = ?',
            ReleaseLog::ACTION_LIQUIDACAO
        ];

        if ($year_month) {
            $month = new \Datetime(date($year_month . '-15'));

            $conditions[0] .= ' and date >= ? and date <= ?';
            $conditions[2] = $month->format('Y-m-01');
            $conditions[3] = $month->format('Y-m-t');
        }

        $rows = ReleaseLog::find('all', [
            'order' => 'date asc',
            'conditions' => $conditions
        ]);

        $saldo = 0;

        return array_map(function ($r) use (&$saldo) {
            $value = $r->value;

            if ($r->release->natureza == 2) {
                $value *= -1;
            }

            $saldo += $value;

            return [
                'release_id' => $r->release_id,
                'number' => $r->release->number,
                'date' => $r->date->format('d/m/Y'),
                'saldo' => Toolkit::showMoney($saldo),
                'people' => $r->release->people->name,
                'desc' => $r->release->description,
                'value' => Toolkit::showMoney($value),
                'color_saldo' => $saldo < 0 ? 'red' : 'blue',
                'color' => $value < 0 ? 'red' : 'blue'
            ];
        }, $rows);
    }

    public function getFormatValue()
    {
        return number_format($this->value, 2, ',', '.');
    }

    /**
     * Formata os lançamentos com o padrão que a grid de lançamentos necessita.
     */
    public static function gridFormat($rows, $include_data_emissao = false)
    {
        return array_map(function ($r) use ($include_data_emissao) {
            $row = $r->to_array();

            $row['people'] = $r->people->name;
            $row['category'] = $r->category->name;
            $row['natureza'] = $r->getNaturezaName();
            $row['vencimento'] = $r->data_vencimento->format('d/m/Y');
            $row['status'] = $r->getStatusName();
            $row['color'] = $r->getColor();
            $row['signal'] = $r->natureza == self::RECEITA ? '+' : '-';
            $row['desc'] = $r->description;
            $row['_value'] = $row['value'];
            $row['value_abs'] = $row['value'];
            $row['valor'] = $r->getFormatValue();

            if ($r->natureza == self::DESPESA) {
                $row['_value'] *= -1;
            }

            if ($include_data_emissao) {
                $row['emissao'] = $r->log_emissao->date->format('d/m/Y');
            }

            return $row;
        }, $rows);
    }

    /**
     * Seleciona e formata os lançamentos para apresentação no formulário de agrupamento de lançamentos.
     */
    public static function gridGroupFormat($return_qtd_rows = false)
    {
        $releases = self::find('all', [
            'order' => 'data_vencimento asc',
            'conditions' => [
                'status = ? and data_vencimento < ?',
                self::STATUS_ABERTO,
                (new \Datetime(date('Y-m-d')))->add(new \Dateinterval('P1M'))->format('Y-m-d')
            ]
        ]);
        
        if ($return_qtd_rows) {
            return count($releases);
        }

        return self::gridFormat($releases, true);
    }

    /**
     * Verifica se o lançamento foi originado de um agrupamento de lançamentos.
     */
    public function isGroup()
    {
        return !! count($this->releases);
    }

    /**
     * Retorna uma cor de identifcação
     * com base na natureza do lanamento.
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
     */
    public function canLiquidar()
    {
        return $this->status == self::STATUS_ABERTO;
    }

    public function canDesfazer()
    {
        if (! $last = $this->getLastLog()) {
            return false;
        }

        /**
         * Se for um lançamento que foi parcelado.
         * É verificado se alguma das parcelas geradas foi movimentada.
         */
        if ($this->status == self::STATUS_PARCELADO) {
            foreach ($this->parcelamento_parcelas as $release) {
                if ($release->getLastLog()) {
                    return false;
                }
            }
        }

        return $last->action != ReleaseLog::ACTION_EMISSAO;
    }

    /**
     * Só é possivel ediar lançamentos sem movimentação.
     */
    public function canEditar()
    {
        if ($this->isParcelamento()) {
            return false;
        }

        $count = ReleaseLog::count([
            'conditions' => [
                'release_id = ?',
                $this->id
            ]
        ]);

        return $count <= 1;
    }

    /**
     * Verifica se um originado de um agrupamento pode ser desagrupado.
     */
    public function canUngroup()
    {
        return $this->isGroup() && $this->canEditar();
    }

    public function isLiquidado()
    {
        return $this->status == self::STATUS_LIQUIDADO;
    }

    public function getLogDescription($action)
    {
        if ($action == 'destroy') {
            return 'Apagou o lançamento nº ' . $this->number . ', #' . $this->id . '.';
        }

        return null;
    }
}
