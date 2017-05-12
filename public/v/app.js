var pessoa;
var pessoas = ['fernando', 'maria', 'daniel', 'joão']
var dates = {
    'anteontem': Date.today().addDays(-2).toString('yyyy-MM-dd'),
    'ontem': Date.today().addDays(-1).toString('yyyy-MM-dd'),
    'hoje': Date.today().toString('yyyy-MM-dd'),
    'amanhã': Date.today().addDays(1).toString('yyyy-MM-dd'),
    'depois de amanhã': Date.today().addDays(2).toString('yyyy-MM-dd')
}

var fields = {
    natureza: $('[name=natureza]'),
    pessoa: $('[name=pessoa]'),
    valor: $('[name=valor]'),
    vencimento: $('[name=vencimento]')
}

start()

function start() {
    ask('Receita ou Despesa?', askNatureza)
    fields.natureza.focus()
}

function askNatureza(error, r) {

    if (error) {
        ask('Tente de novo', askPessoa)
    } else {

        if (r.out == 'receita' || r.out == 'despesa') {
            fields.natureza.val(r.out)
            ask('Quem é a pessoa?', askPessoa)
            fields.pessoa.focus()
        } else {
            ask('Responda "Receita" ou "Despesa"', askNatureza)
        }
    }
}

function askPessoa(error, r) {

    if (error) {
        ask('Tente de novo', askPessoa)
    } else {

        pessoa = r.out;

        if ($.inArray(pessoa, pessoas) == -1) {
            ask(pessoa + ' não está cadastrado. Deseja cadastrar?', askAddPessoa)
        } else {
            askValor(pessoa)
        }
    }
}

function askValor(pessoa) {
    fields.pessoa.val(pessoa)
    ask('Qual o valor do documento?', askValorDocumento)
    fields.valor.focus()
}

function askAddPessoa(error, r) {
    if (error) {
        ask('Tente de novo', askAddPessoa)
    } else {
        if (r.out == 'sim') {
            speak(pessoa + ' foi cadastrada.')
            askValor(pessoa)
        } else if (r.out == 'não') {
            ask('Diga o nome de outra pessoa', askPessoa)
        } else {
            ask('Responda "sim" ou "não"', askAddPessoa)
        }
    }
}

function askValorDocumento(error, r) {
    if (error) {
        ask('Tente de novo', askValorDocumento)
    } else {
        var value = parseFloat(r.out.replace('r$ ', ''))
        console.log('valor', value)
        fields.valor.val(value)
        ask('Qual a data de vencimento?', askDataVencimento)
        fields.vencimento.focus()
    }
}

function askDataVencimento(error, r) {
    if (error) {
        ask('Tente de novo', askDataVencimento)
    } else {

        var date = r.out

        if (inDataList(date)) {
            fields.vencimento.val(dates[date])
            submit()
        } else {
            date = date.replace(/ de /gi, ' ')
            date = Date.parse(date)

            if (date) {
                fields.vencimento.val(date.toString('yyyy-MM-dd'))
                submit()
            } else {
                ask('A data de vencimento pode ser: "' + Object.keys(dates).join('", "') + '". Ou, por exemplo "10 de janeiro de 2002"', askDataVencimento)
            }
        }
    }
}

function askSubmit(error, r) {
    if (error) {
        ask('Tente de novo', askSubmit)
    } else {
        if (r.out == 'sim') {
            speak('Lançamento salvo com sucesso')
            ask('Deseja fazer outro lançamento?', askStart)
            clearForm()
        } else if (r.out == 'não') {
            ask('Deseja fazer outro lançamento?', askStart)
            clearForm()
        } else {
            ask('Vai salvar esta misera "sim" ou "não".', askSubmit)
        }
    }
}

function askStart(error, r) {
    if (error) {
        ask('Tente de novo', askStart)
    } else {
        if (r.out == 'sim') {
            start()
        } else if (r.out == 'não') {
            speak('Fico a disposição, obrigada!')
        } else {
            ask('Deseja fazer outro lançamento?', askStart)
        }
    }
}

function clearForm() {
    fields.natureza.val('')
    fields.pessoa.val('')
    fields.valor.val('')
    fields.vencimento.val('')
}

function submit() {
    ask('Confirmar ' + strNatureza(fields.natureza.val(), fields.pessoa.val()) + ', no valor de ' + fields.valor.val() + ' para ' + Date.parse(fields.vencimento.val()).toString('dd/MM/yyyy'), askSubmit)
}

function strNatureza(natureza, pessoa) {
    if (natureza == 'receita') {
        return 'Recebimento de ' + pessoa
    }

    return 'Pagamento para ' + pessoa
}

function inDataList(date) {
    return $.inArray(date, Object.keys(dates)) != -1
}