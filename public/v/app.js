var people;
var category;

var peoples = {}
var categories = {}

extractSelect('people_id', peoples)
extractSelect('category_id', categories)

var dates = {
    'anteontem': Date.today().addDays(-2).toString('yyyy-MM-dd'),
    'ontem': Date.today().addDays(-1).toString('yyyy-MM-dd'),
    'hoje': Date.today().toString('yyyy-MM-dd'),
    'amanhã': Date.today().addDays(1).toString('yyyy-MM-dd'),
    'depois de amanhã': Date.today().addDays(2).toString('yyyy-MM-dd')
}

var fields = {
    natureza: $('[name=natureza]'),

    people_id: $('[name=people_id]'),
    people: $('[name=people]'),

    category_id: $('[name=category_id]'),
    category: $('[name=category]'),

    value: $('[name=value]'),

    data_emissao: $('[name=data_emissao]'),
    data_vencimento: $('[name=data_vencimento]'),
    data_liquidacao: $('[name=data_liquidacao]'),

    description: $('[name=description]'),
    voice: $('[name=voice]')
}

function start(voice) {

    if (!('webkitSpeechRecognition' in window)) {
        alert('Este navegador não tem suporte, use o Chrome!');
        return
    }

    if (voice == 'ADDED_RELEASE') {
        speak('Lançamento salvo com sucesso')
        ask('Deseja fazer outro lançamento?', askStart)
    } else {

        $('#btn-start').addClass('btn-danger')

        ask('Recebimento ou Pagamento?', askNatureza)
        fields.natureza.focus()
        fields.voice.val('ADD_RELEASE')
    }
}

function askNatureza(error, answer) {

    if (error) {
        denovo(askNatureza)
    } else {

        delete attemps.askNatureza

        if (answer == 'recebimento' || answer == 'pagamento') {
            fields.natureza.val(answer == 'recebimento' ? 1 : 2)
            ask('Quem é a pessoa?', askPessoa)
            fields.people_id.focus()
        } else {
            ask('Responda "Recebimento" ou "Pagamento"', askNatureza)
        }
    }
}

function askPessoa(error, answer) {

    if (error) {
        denovo(askPessoa)
    } else {

        delete attemps.askPessoa

        if (answer == 'editar natureza') {
            ask('Recebimento ou Pagamento?', askNatureza)
            fields.natureza.focus()
            return
        }

        people = answer;

        if ($.inArray(people, Object.keys(array_flip(peoples))) == -1) {
            ask(people + ' não está cadastrado. Deseja cadastrar?', askAddPessoa)
        } else {
            var flip = array_flip(peoples)
            fields.people_id.val(flip[people])
            ask('Qual a categoria?', askCategory)
            fields.category_id.focus()
        }
    }
}

function askCategory(error, answer) {

    if (error) {
        denovo(askCategory)
    } else {

        delete attemps.askCategory

        category = answer;

        if ($.inArray(category, Object.keys(array_flip(categories))) == -1) {
            ask('Categoria "' + category + '" não está cadastrada. Deseja cadastrar?', askAddCategory)
        } else {
            var flip = array_flip(categories)
            fields.category_id.val(flip[category])
            fields.value.focus()
            ask('Qual o valor do documento?', askValorDocumento)
        }
    }
}

function askAddCategory(error, answer) {

    if (error) {
        denovo(askAddCategory)
    } else {

        delete attemps.askAddCategory

        if (answer == 'editar pessoa') {
            ask('Quem é a pessoa?', askPessoa)
            fields.people_id.focus()
            return
        }

        if (answer == 'sim') {
            $('#add-category-link').click()
            fields.category.val(category)
            ask('Qual o valor do documento?', askValorDocumento)
            fields.value.focus()
        } else if (answer == 'não') {
            ask('Diga o nome de outra categoria', askCategory)
        } else {
            ask('Responda "sim" ou "não"', askAddPessoa)
        }
    }
}

function askAddPessoa(error, answer) {
    if (error) {
        denovo(askAddPessoa)
    } else {

        delete attemps.askAddPessoa

        if (answer == 'sim') {
            $('#add-people-link').click()
            fields.people.val(people)
            ask('Qual a categoria?', askCategory)
            fields.category_id.focus()
        } else if (answer == 'não') {
            ask('Diga o nome de outra pessoa', askPessoa)
        } else {
            ask('Responda "sim" ou "não"', askAddPessoa)
        }
    }
}

function askValorDocumento(error, answer) {
    if (error) {
        denovo(askValorDocumento)
    } else {

        delete attemps.askValorDocumento

        if (answer == 'editar categoria') {
            ask('Qual a categoria?', askCategory)
            fields.category_id.focus()
            return
        }

        var value = parseFloat(answer.replace('r$ ', '').replace(',', '.'))
        console.log('valor', value)

        if (isNaN(value)) {
            ask('Apenas centavos eu não entendo, diga um valor maior que R$ 1', askValorDocumento)
        } else {

            fields.value.val(value)
            ask('Qual a data de vencimento?', askDataVencimento)
            fields.data_vencimento.focus()
        }
    }
}

var attemps = {}

function denovo(callback) {

    var fn = functionName(callback)
    var askstr = 'Tente de novo'

    if (attemps[fn] == 1) {
        askstr = 'Ta difícil! Bem devagar... repita novamente'
    }

    if (attemps[fn] == 2) {
        askstr = 'Vamos ficar aqui até o brasil ganhar a copa? Vai, De novo...'
    }

    if (attemps[fn] >= 3) {
        speak('Deu ruim, vou recarregar a página', function () {
            location.reload()
        })
        return
    }

    if (!attemps[fn]) {
        attemps[fn] = 0
    }

    attemps[fn]++

    console.log('attemps', attemps)

    ask(askstr, callback)
}

function functionName(fun) {
    var ret = fun.toString();
    ret = ret.substr('function '.length);
    ret = ret.substr(0, ret.indexOf('('));
    return ret;
}

function askDataVencimento(error, answer) {
    if (error) {
        denovo(askDataVencimento)
    } else {
        
        delete attemps.askDataVencimento

        if (answer == 'editar valor') {
            ask('Qual o valor do documento?', askValorDocumento)
            fields.value.focus()
            return
        }

        var date = answer

        if (inDataList(date)) {
            fields.data_vencimento.val(dates[date])
            submit()
        } else {
            date = date.replace(/ de /gi, ' ')
            date = Date.parse(date)

            if (date) {
                fields.data_vencimento.val(date.toString('yyyy-MM-dd'))
                submit()
            } else {
                ask('A data de vencimento pode ser: "' + Object.keys(dates).join('", "') + '". Ou, por exemplo "10 de janeiro de 2002"', askDataVencimento)
            }
        }
    }
}

function askSubmit(error, answer) {
    if (error) {
        denovo(askSubmit)
    } else {
        if (answer == 'sim' || answer == 'com certeza') {
            $('[type=submit]').click()
        } else if (answer == 'não') {
            ask('Deseja fazer outro lançamento?', askStart)
        } else {
            ask('Vai salvar "sim" ou "não".', askSubmit)
        }
    }
}

function askStart(error, answer) {
    if (error) {
        denovo(askStart)
    } else {
        if (answer == 'sim' || answer == 'vamos nessa') {
            location.href = location.href.split('?')[0] + '?voice=ADD_RELEASE';
        } else if (answer == 'não') {
            speak('Fico a disposição!')
            location.href = '/app'
        } else {
            ask('Deseja fazer outro lançamento?', askStart)
        }
    }
}

function clearForm() {
    fields.natureza.val(1)
    fields.people.val('')
    fields.value.val('')
    fields.data_emissao.val('')
    fields.data_vencimento.val('')
    fields.data_liquidacao.val('')
    fields.description.val('')
}

function submit() {
    ask('Confirmar ' + strNatureza(fields.natureza.val(), people) + ', no valor de R$ ' + fields.value.val().replace(',', ' reais ') + ' para ' + Date.parse(fields.data_vencimento.val()).toString('dd/MM/yyyy'), askSubmit)
}