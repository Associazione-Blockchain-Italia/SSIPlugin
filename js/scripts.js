let offerUrl;
const urlAjax = 'ajax.php';
let verificationRequestUrl;
let verificationId;
let isValid;

function createConnection() {
    let isSubscriber;
    let identifier;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const role = document.getElementById('role').value;
    const description = document.getElementById('description').value;

    let jsonObj = {
        username: username,
        email: email,
        role: role,
        description: description,
    };

    role === 'subscriber' ? isSubscriber = true : isSubscriber = false;
    isSubscriber ? identifier = guid() + 'ssi' : identifier = guid() + 'ssi2';
    console.log(username + ' ' + email + ' ' + role + ' ' + description);

    $.ajax({
        url: urlAjax,
        type: 'POST',
        data: {functionname: 'createConnection', arguments: [],},
        success: function (result) {
            try {
                const jsonResult = JSON.parse(result);
                const connectionId = jsonResult['connectionId'];
                const invitationUrl = jsonResult['invitationUrl'];
                console.log('Invitation URL :' + invitationUrl);
                $(".qrcode").attr("src", "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=" + invitationUrl);
                $(".qrcode").show();
                jsonObj = Object.assign({connectionId: connectionId}, jsonObj);
                pollingReg(identifier, connectionId, isSubscriber, jsonObj);
            } catch (e) {
                console.log(e);
                $(".testo").text('Api key inesistente');
                $(".testo").css('color', 'red');
            }
        },
    });
}

function pollingReg(identifier, connectionId, isSubscriber, jsonObj) {
    let state;
    let refreshId = setInterval(function () {
        $.ajax({
            url: urlAjax,
            type: 'POST',
            data: {functionname: 'getConnection', arguments: [connectionId],},
            success: function (result) {
                const jsonResult = JSON.parse(result);
                state = jsonResult['state'];

            },
            complete: function () {
                console.log(state);
                if (state === "Connected") {
                    if (isSubscriber)
                        offerCredential(identifier, jsonObj);
                     createUser(identifier, jsonObj,);
                    clearInterval(refreshId);
                }
            }
        });

    }, 5000);
}

function offerCredential(identifier, jsonObj) {
    let credentialId;
    $.ajax({
        url: urlAjax,
        type: 'POST',
        data: {functionname: 'offerCredential', arguments: [identifier, jsonObj],},
        success: function (result) {
            const jsonResult = JSON.parse(result);
            credentialId = jsonResult['credentialId'];
        },
        complete: function () {
            jsonObj = Object.assign({credentialId: credentialId}, jsonObj);
        },
    });
}

function createUser(identifier, jsonObj, id=0) {
    $.ajax({
        url: urlAjax,
        type: 'POST',
        data: {functionname: 'createUser', arguments: [identifier, jsonObj, id],},
        complete: function (result) {
            console.log(result);
        },
    });
}

function authenticateUser() {
    $(".form").hide();
    $.ajax({
        url: urlAjax,
        type: 'POST',
        data: {functionname: 'verifyCredential', arguments: [],},
        success: function (result) {
            try {
                let jsonResult = JSON.parse(result);
                verificationRequestUrl = jsonResult['verificationRequestUrl'];
                verificationId = jsonResult['verificationId'];
                console.log(verificationRequestUrl);
                console.log(verificationId);
                $(".qrcode").attr("src", "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=" + verificationRequestUrl);
                $(".qrcode").show();
                pollingAuth(verificationId);
            } catch (e) {
                $(".testo").text('Api key inesistente');
                $(".testo").css('color', 'red');
            }
        }
        ,
    });
}

function pollingAuth(verificationId) {
    let refreshId = setInterval(function () {
        $.ajax({
            url: urlAjax,
            type: 'POST',
            data: {functionname: 'getVerification', arguments: [verificationId],},
            success: function (result) {
                jsonResult = JSON.parse(result);
                state = jsonResult['state'];
                isValid = jsonResult['isValid'];
                console.log(state);
            },
        });
        if (state === "Accepted" && isValid === true) {
            console.log(state);
            identifier = jsonResult['proof']['Credenziale']['attributes']['Identifier'];
            $.ajax({
                url: urlAjax,
                type: 'POST',
                data: {functionname: 'authenticateUser', arguments: [identifier],},
                success: function (result) {
                    console.log(result);
                    $(".qrcode").hide();
                    $(".testo").text('Utente autenticato');
                },
            });
            clearInterval(refreshId);
        } else if (state === "Accepted" && isValid === false) {
            $(".testo").text('Credenziale non valida');
            $(".testo").css('color', 'red');
            $(".qrcode").hide();
            console.log("Credenziale non valida");
            clearInterval(refreshId);
        }
    }, 3000);
}


function guid() {
    let s4 = () => {
        return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
    }
    return s4() + s4() + s4();
}

function showForm() {
    $(".form").show();
}