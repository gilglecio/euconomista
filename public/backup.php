<?php

$pdo = new \PDO('mysql:host=localhost;dbname=fitmicro', 'root', '123');

$entidade = 51;
$categoria = 1;

$pessoas = $pdo->query('select id, nome from pessoas where status <> 0 and entidade_id = ' . $entidade)->fetchAll(\PDO::FETCH_ASSOC);
$lancamentos = $pdo->query('select l.id, l.natureza, l.data_emissao, l.fin_lancamento_documento_id, l.situacao, l.data_vencimento, l.numero, l.valor, l.pessoa_id from fin_lancamentos l 
	join fin_lancamento_documentos ld on ld.id = l.fin_lancamento_documento_id and ld.status <> 0
	where l.status <> 0 and l.entidade_id = ' . $entidade)->fetchAll(\PDO::FETCH_ASSOC);
$logs = $pdo->query('select ll.id, 2 as acao, ll.data, ll.fin_lancamento_id, ll.valor from fin_lancamento_logs ll 
	join fin_lancamentos l on l.id = ll.fin_lancamento_id
	where ll.acao in (1,2) and l.entidade_id = ' . $entidade)->fetchAll(\PDO::FETCH_ASSOC);

$data = [];

foreach ($pessoas as $p) {
    if (! isset($data[$p['id']])) {
        $data[$p['id']] = $p;
    }

    if (! isset($data[$p['id']]['lancamentos'])) {
        $data[$p['id']]['lancamentos'] = [];
    }

    foreach ($lancamentos as $l) {
        if ($l['pessoa_id'] != $p['id']) {
            continue;
        }

        if (! isset($data[$p['id']]['lancamentos'][$l['id']])) {
            $data[$p['id']]['lancamentos'][$l['id']] = $l;
        }

        if (! isset($data[$p['id']]['lancamentos'][$l['id']]['logs'])) {
            $data[$p['id']]['lancamentos'][$l['id']]['logs'] = [];
        }

        foreach ($logs as $ll) {
            if ($ll['fin_lancamento_id'] != $l['id']) {
                continue;
            }

            $data[$p['id']]['lancamentos'][$l['id']]['logs'][$ll['id']] = $ll;
        }
    }
}

$situacoes = [];

foreach ($data as $pessoa_id => $pessoa) {
    foreach ($pessoa['lancamentos'] as $fin_lancamento_id => $lancamento) {
        $data[$pessoa_id]['lancamentos'][$fin_lancamento_id]['logs'][] = [
            'acao' => 1,
            'data' => $lancamento['data_emissao'],
            'fin_lancamento_id' => $fin_lancamento_id,
            'valor' => $lancamento['valor']
        ];

        $situacoes[] = $lancamento['situacao'];

        if ($lancamento['situacao'] == 4 && empty($lancamento['logs'])) {
            $data[$pessoa_id]['lancamentos'][$fin_lancamento_id]['logs'][] = [
                'acao' => 2,
                'data' => $lancamento['data_vencimento'],
                'fin_lancamento_id' => $fin_lancamento_id,
                'valor' => $lancamento['valor']
            ];
        }
    }
}

// print_r(array_flip($situacoes));
// exit;

try {
    $hm = new \PDO('mysql:host=localhost;dbname=hmgestor', 'root', '123', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $hm->beginTransaction();

    $hm->query('insert into categories set name = \'Importados\', entity = 1, user_id = 1, created_at = now(), updated_at = now()');

    foreach ($data as $pessoa_id => $pessoa) {
        $name = addslashes($pessoa['nome']);

        $find = $hm->query('select id, name from peoples where name = \'' . $name . '\' ')->fetch();

        if ($find) {
            $pid = $find['id'];
        } else {
            $q0 = 'insert into peoples set entity = 1, user_id = 1, created_at = now(), updated_at = now(), name = \'' . $name . '\' ';
            $hm->query($q0);
            $pid = $hm->lastInsertId();
        }

        foreach ($pessoa['lancamentos'] as $fin_lancamento_id => $lancamento) {
            $process = sha1($lancamento['fin_lancamento_documento_id']);

            $status = $lancamento['situacao'] == 4 ? 2 : 1;

            $q1 = 'insert into releases set `user_id` = 1, `category_id` = 1, `people_id` = ' . $pid . ', `entity` = 1, `number` = \'' . $lancamento['numero'] . '\', `value` = \'' . $lancamento['valor'] . '\', `natureza` = ' . $lancamento['natureza'] . ', `data_vencimento` = \'' . $lancamento['data_vencimento'] . '\', `status` = ' . $status . ', `created_at` = \'' . $lancamento['data_emissao'] . '\', `updated_at` = \'' . $lancamento['data_emissao'] . '\', `process` = \'' . $process .  '\'';

            $hm->query($q1);

            $lid = $hm->lastInsertId();

            foreach ($lancamento['logs'] as $fin_lancamento_id => $log) {
                $q2 = 'insert into release_logs set `user_id` = 1, `release_id` = ' . $lid . ', `entity` = 1, `action` = ' . $log['acao'] . ', `value` = ' . $log['valor'] . ', `created_at` = \'' . $log['data'] . '\', `date` = \'' . $log['data'] . '\'';

                $hm->query($q2);
            }
        }
    }

    $hm->commit();
} catch (\Exception $e) {
    $hm->rollback();
    die($e->getMessage() . "\n\n" . $q1);
}

die('OK');
