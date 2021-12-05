// ask a question and get an answer
function ask(text, callback) {
    // ask question
    speak(text, function () {

        console.log(text, 'Aguardando resosta...')

        $('#output').html('&nbsp;&nbsp;&nbsp;' + text)

        // get answer
        var recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;

        recognition.onend = function (e) {
            console.log('ERROR', e)
            if (callback) {
                recognition.stop()
                clearOutput()
                callback('não entendo');
            }
        };

        recognition.onresult = function (e) {
            // cancel onend handler
            recognition.onend = null;
            if (callback) {
                var out = e.results[0][0].transcript.toLowerCase()
                console.log('OUT', out)
                recognition.stop()
                clearOutput()
                callback(null, out)
                // callback(null, {
                //     out: out,
                //     confidence: e.results[0][0].confidence
                // });
            }
        }

        $('#btn-start').addClass('btn-danger')

        // start listening
        recognition.start()
    });
}

// say a message
function speak(text, callback) {
    var speech = new SpeechSynthesisUtterance();

    speech.text = text;
    speech.lang = 'pt-BR';
    speech.rate = 3;
    // speech.pitch = .55;

    speech.onend = function () {
        console.log('speech end')
        if (callback) {
            console.log('speech end callback')
            callback();
        }
    }

    speech.onerror = function (e) {
        console.log('speech error')
        if (callback) {
            console.log('speech error callback')
            callback(e);
        }
    }

    console.log('speak')

    speechSynthesis.speak(speech);
}

function clearOutput() {
    $('#output').html('')
    $('#btn-start').removeClass('btn-danger')
}

function strNatureza(natureza, people) {
    if (natureza == 'receita') {
        return 'Recebimento de ' + people
    }

    return 'Pagamento para ' + people
}

function inDataList(date) {
    return $.inArray(date, Object.keys(dates)) != -1
}

function extractSelect(name, output) {

    $('[name=' + name + '] option').map(function (i, e) {
        if ($(e).attr('value')) {
            output[$(e).attr('value')] = $(e).text().toLowerCase()
        }
    }).filter(e => e)
}

function array_flip(trans) {
    var key, tmp_ar = {};

    for (key in trans) {
        if (trans.hasOwnProperty(key)) {
            tmp_ar[trans[key]] = key;
        }
    }

    return tmp_ar;
}