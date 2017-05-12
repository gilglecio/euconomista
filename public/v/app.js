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

    if (voice == 'ADDED_RELEASE') {
        speak('Lançamento salvo com sucesso')
        ask('Deseja fazer outro lançamento?', askStart)
    } else {

        ask('Receita ou Despesa?', askNatureza)
        fields.voice.val('ADD_RELEASE')
        fields.natureza.focus()
    }
}

function askNatureza(error, r) {

    if (error) {
        ask('Tente de novo', askPessoa)
    } else {

        if (r.out == 'receita' || r.out == 'despesa') {
            fields.natureza.val(r.out == 'receita' ? 1 : 2)
            ask('Quem é a pessoa?', askPessoa)
            fields.people_id.focus()
        } else {
            ask('Responda "Receita" ou "Despesa"', askNatureza)
        }
    }
}

function askPessoa(error, r) {

    if (error) {
        ask('Tente de novo', askPessoa)
    } else {

        people = r.out;

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

function askCategory(error, r) {

    if (error) {
        ask('Tente de novo', askCategory)
    } else {

        category = r.out;

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

function askAddCategory(error, r) {
    if (error) {
        ask('Tente de novo', askAddPessoa)
    } else {
        if (r.out == 'sim') {
            $('#add-category-link').click()
            fields.category.val(category)
            ask('Qual o valor do documento?', askValorDocumento)
            fields.value.focus()
        } else if (r.out == 'não') {
            ask('Diga o nome de outra categoria', askCategory)
        } else {
            ask('Responda "sim" ou "não"', askAddPessoa)
        }
    }
}

function askAddPessoa(error, r) {
    if (error) {
        ask('Tente de novo', askAddPessoa)
    } else {
        if (r.out == 'sim') {
            $('#add-people-link').click()
            fields.people.val(people)
            ask('Qual a categoria?', askCategory)
            fields.category_id.focus()
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
        var value = parseFloat(r.out.replace('r$ ', '').replace(',', '.'))
        console.log('valor', value)
        fields.value.val(value)
        ask('Qual a data de vencimento?', askDataVencimento)
        fields.data_vencimento.focus()
    }
}

function askDataVencimento(error, r) {
    if (error) {
        ask('Tente de novo', askDataVencimento)
    } else {

        var date = r.out

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

function askSubmit(error, r) {
    if (error) {
        ask('Tente de novo', askSubmit)
    } else {
        if (r.out == 'sim') {
            $('[type=submit]').click()
        } else if (r.out == 'não') {
            ask('Deseja fazer outro lançamento?', askStart)
        } else {
            ask('Vai salvar "sim" ou "não".', askSubmit)
        }
    }
}

function askStart(error, r) {
    if (error) {
        ask('Tente de novo', askStart)
    } else {
        if (r.out == 'sim') {
            location.href = location.href.split('?')[0] + '?voice=ADD_RELEASE';
        } else if (r.out == 'não') {
            speak('Fico a disposição, obrigada!')
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