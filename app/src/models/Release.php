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
    const STATUS_EM_ATRASO = 3;
    const STATUS_GROUPED = 4;

    const RECEITA = 1;
    const DESPESA = 2;

    /**
     * Registra os relacionamentos 1:1.
     *
     * @var array
     */
    public static $belongs_to = [
        ['user'],
        ['people'],
        ['category']
    ];

    /**
     * Registra os relacionamentos 1:N.
     *
     * @var array
     */
    public static $has_many = [
        ['logs', 'class_name' => 'ReleaseLog'],
        ['releases', 'foreign_key' => 'parent_id']
    ];

    /**
     * Registra os relacionamentos 1:1.
     *
     * @var array
     */
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

    /**
     * Validação de campos obrigatŕios.
     *
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
     * Validação para definir a quantidade de caracteres campo a campo.
     *
     * @var array
     */
    public static $validates_length_of = [
        ['number', 'within' => [3, 15]]
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
     * @throws \Exception Favor selecionar lançamentos da mesma natureza.
     * @throws \Exception Mensagem de erro do model.
     * @return array Lançamentos gerados
     */
    public static function generateGroup($fields)
    {
        try {
            $connection = static::connection();
            $connection->transaction();

            /**
             * Sum release values
             * @var float
             */
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

            /**
             * @var string
             */
            $process = Toolkit::uniqHash();

            /**
             * @var Release
             */
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

            /**
             * Gera o log de emissão.
             * @var ReleaseLog
             */
            $emissao = ReleaseLog::create([
                'release_id' => $row->id,
                'date' => $fields['data_emissao'],
                'action' => ReleaseLog::ACTION_EMISSAO,
                'value' => $value
            ]);
            
            if ($emissao->is_invalid()) {
                throw new \Exception($emissao->getFisrtError());
            }

            /**
             * Sera TRUE se a data de liquidação for informada.
             * @var boolean
             */
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

                /**
                 * Altera o status do lançamento para liquidado.
                 */
                $row->status = self::STATUS_LIQUIDADO;
                $row->save();
            }

            foreach ($releases as $release) {

                /**
                 * Backup do lançamento.
                 * @var string
                 */
                $backup = json_encode($release->to_array());

                $release->parent_id = $row->id;
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

            $isEdit = isset($fields['id']) && is_numeric($fields['id']);

            $release_number = null;

            /**
             * Se o ID foi passado, é porque se trata de uma edição.
             */
            if ($isEdit) {
                
                /**
                 * @var Release
                 */
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
             * @var boolean
             */
            $liquidar = !! $fields['data_liquidacao'];

            for ($i=0; $i < $quantity; $i++) {

                /**
                 * Numero sequencial / quantidade de parcelas.
                 * @var string
                 */
                $number = ($i + 1) . '/' . $quantity;

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

                /**
                 * @var Release
                 */
                $row = self::create([
                    'number' => $number,
                    'value' => $value,
                    'natureza' => $fields['natureza'],
                    'data_vencimento' => clone $vencimento,
                    'people_id' => $fields['people_id'],
                    'category_id' => $fields['category_id'],
                    'description' => $fields['description'],
                    'process' => $process
                ]);
                
                if ($row->is_invalid()) {
                    throw new \Exception($row->getFisrtError());
                }

                /**
                 * Gera o log de emissão.
                 *
                 * @var ReleaseLog
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


            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw $e;
        }


        return $releases;
    }

    /**
     * Liquida um lançamento.
     * Aceita liquidação partial.
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
             * @var string
             */
            $backup = $release->to_json();

            /**
             * Quando o usuário liquida um vlaor menor que o valor do lançamento.
             * @var boolean
             */
            $partial = $fields['value'] < $release->value;

            /**
             * Quando o valor liquidado é maior que o valor aberto,
             * entende-se que a diferença são encargos.
             * @var float
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

    public function getProrrogarDate()
    {
        $now = new \Datetime(date('Y-m-d'));

        if ($this->data_vencimento < $now) {
            return $now->format('Y-m-d');
        }

        return $this->data_vencimento->add(new \Dateinterval('P1D'))->format('Y-m-d');
    }

    /**
     * Prorroga um lançamento.
     *
     * @param array $fields
     * @throws \Exception Mensagem de erro do model.
     * @throws \Exception Lançamento não localizado.
     * @return boolean
     */
    public static function prorrogar($fields)
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

            if ($fields['value'] < $release->value) {
                throw new \Exception('Favor informar um valor maior ou igual ao valor do lançamento.');
            }

            $date = new \Datetime($fields['date']);

            if ($date <= $release->data_vencimento) {
                throw new \Exception('Favor informar uma data de vencimento maior que a data de vencimento atual.');
            }
            
            /**
             * Todo o registro do lançamento em formato JSON.
             * @var string
             */
            $backup = $release->to_json();

            /**
             * Valor dos encargos, quando o valor é alterado.
             * @var float
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

    /**
     * Cancela o ultimo log do lançamento.
     *
     * @param integer                  $release_id
     * @param \ActiveRecord\Connection $connection
     * @param integer                  $action
     * @throws \Exception Não foi possível cancelar o ultimo log do lançamento.
     * @throws \Exception Lançamento não localizado.
     * @throws \Exception A ação do log que será cancelado é diferente da ação especificada.
     * @return boolean
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

            /**
             * @var ReleaseLog
             */
            $last = $release->getLastLog();

            if ($action && $last->action != $action) {
                throw new \Exception('A ação do log que será cancelado é diferente da ação especificada.');
            }

            $begin = false;

            if (! $connection) {
                $connection = static::connection();
                $connection->transaction();
                $begin = true;
            }
            
            $last->rollback();

            if ($begin) {
                $connection->commit();
            }
        } catch (\Exception $e) {
            if ($begin) {
                $connection->rollback();
            }
            throw $e;
        }

        return true;
    }

    /**
     * Cancela o agrupamento do lançamento.
     *
     * @param integer $release_id
     * @throws \Exception Não foi possível desagrupar os lançamentos.
     * @throws \Exception Lançamento não localizado.
     * @return boolean
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
                
                /**
                 * Cancela o log de agrupamento.
                 */
                self::rollback($row->id, $connection, ReleaseLog::ACTION_GROUPED);
                
                /**
                 * Limpa o id do lançamento.
                 * @var null
                 */
                $row->parent_id = null;
                $row->save();
            }

            /**
             * Apaga o lançamento.
             */
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
            throw new \Exception($log->getFisrtError());
        }
    }

    /**
     * Verifica se o lançamento foi agrupado.
     *
     * @return boolean
     */
    public function isGrouped()
    {
        return $this->status == self::STATUS_GROUPED;
    }

    /**
     * Verifica se um lançamento aberto está em atraso.
     *
     * @return boolean
     */
    public function emAtraso()
    {
        return $this->status == self::STATUS_ABERTO && $this->data_vencimento < (new \Datetime(date('Y-m-d')));
    }

    /**
     * Retorna o valor da coluna status por extenso.
     *
     * @return string
     */
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
            self::STATUS_LIQUIDADO => 'Pago',
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
                'action = ?',
                ReleaseLog::ACTION_LIQUIDACAO
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
                'people' => $r->release->people->name,
                'desc' => $r->release->description,
                'value' => Toolkit::showMoney($value),
                'color' => $value < 0 ? 'red' : 'blue'
            ];
        }, $rows);
    }

    /**
     * Retorna o valor do lançamento formatado.
     *
     * @return string
     */
    public function getFormatValue()
    {
        return number_format($this->value, 2, ',', '.');
    }

    /**
     * Formata os lançamentos com o padrão que a grid de lançamentos necessita.
     *
     * @param array                         $rows Release list.
     * @param boolean $include_data_emissao Se true, adiciona a data de emissão nos lançamentos.
     * @return array
     */
    public static function gridFormat($rows, $include_data_emissao = false)
    {
        return array_map(function ($r) use ($include_data_emissao) {
            $row = $r->to_array();

            $valor_liquidado = $r->getSumLiquidacoes();
            $valor_aberto = $r->status == self::STATUS_ABERTO ? $r->value : 0;

            $row['people'] = $r->people->name;
            $row['category'] = $r->category->name;
            $row['natureza'] = $r->getNaturezaName();
            $row['vencimento'] = $r->data_vencimento->format('d/m/Y');
            $row['value'] = $r->getFormatValue();
            $row['valor_aberto'] = Toolkit::showMoney($valor_aberto);
            $row['valor_liquidado'] = Toolkit::showMoney($valor_liquidado);
            $row['_valor_aberto'] = $valor_aberto;
            $row['_valor_liquidado'] = $valor_liquidado;
            $row['status'] = $r->getStatusName();
            $row['color'] = $r->getColor();
            $row['signal'] = $r->natureza == self::RECEITA ? '+' : '-';
            $row['desc'] = $r->description;

            if ($include_data_emissao) {
                $row['emissao'] = $r->log_emissao->date->format('d/m/Y');
            }

            return $row;
        }, $rows);
    }

    /**
     * Seleciona e formta os lançamentos para apresentação no formulário de agrupamento de lançamentos.
     *
     * @param boolean $return_qtd_rows Se true, retorna a quantidade de linhas filtradas.
     * @return array
     */
    public static function gridGroupFormat($return_qtd_rows = false)
    {
        $releases = self::find('all', [
            'order' => 'data_vencimento asc',
            'conditions' => [
                'status = ?', //  and data_vencimento < ?
                self::STATUS_ABERTO,
                // (new \Datetime(date('Y-m-d')))->add(new \Dateinterval('P2M'))->format('Y-m-d')
            ]
        ]);

        $filtered = array_filter($releases, function ($r) {
            return ! $r->isGroup();
        });

        if ($return_qtd_rows) {
            return count($filtered);
        }

        return self::gridFormat($filtered, true);
    }

    /**
     * Verifica se o lançamento foi originado de um agrupamento de lançamentos.
     *
     * @return boolean
     */
    public function isGroup()
    {
        return !! count($this->releases);
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
        return $this->status == self::STATUS_ABERTO;
    }

    /**
     * Só é possivel desfazer lançamentos com liquidações.
     *
     * @return boolean
     */
    public function canDesfazer()
    {
        if (! $last = $this->getLastLog()) {
            return false;
        }

        return $last->action != ReleaseLog::ACTION_EMISSAO;
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
     * Verifica se um originado de um agrupamento pode ser desagrupado.
     *
     * @return boolean
     */
    public function canUngroup()
    {
        return $this->isGroup() && $this->canEditar();
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
