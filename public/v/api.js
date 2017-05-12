// ask a question and get an answer
function ask(text, callback) {
    // ask question
    speak(text, function () {

        console.log(text, 'Aguardando resosta...')

        // get answer
        var recognition = new webkitSpeechRecognition();
        recognition.continuous = false;
        recognition.interimResults = false;

        recognition.onend = function (e) {
            console.log('ERROR', e)
            if (callback) {
                recognition.stop()
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
                callback(null, {
                    out: out,
                    confidence: e.results[0][0].confidence
                });
            }
        }

        // start listening
        recognition.start();
    });
}

// say a message
function speak(text, callback) {
    var speech = new SpeechSynthesisUtterance();

    speech.text = text;
    speech.lang = 'pt-BR';

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