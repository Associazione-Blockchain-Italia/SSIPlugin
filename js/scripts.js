let jsonResult;
let offerUrl;
let credentialId;
let state;
let urlAjax = 'ajax.php';
let verificationRequestUrl;
let verificationId;
let identifier;
let isValid;

function pollingReg(identifier, credentialId, role) {
    let refreshId = setInterval(function () {
        $.ajax({
            url: urlAjax,
            type: 'POST',
            data: {functionname: 'getCredential', arguments: [credentialId],},
            success: function (result) {
                jsonResult = JSON.parse(result);
                state = jsonResult['state'];
                console.log(state);
            },
        });
        if (state === "Issued") {
            console.log(state);
            $.ajax({
                url: urlAjax,
                type: 'POST',
                data: {functionname: 'createUser', arguments: [identifier, credentialId, role],},
                success: function (result) {
                    console.log(result);
                    $(".qrcode").hide();
                    $(".testo").text('Utente creato');
                },
            });
            clearInterval(refreshId);
        }
    }, 3000);
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

function createAndOfferCredential(identifier, role) {
    $.ajax({
        url: urlAjax,
        type: 'POST',
        data: {functionname: 'createAndOfferCredential', arguments: [identifier, role],},
        success: function (result) {
            try {
                jsonResult = JSON.parse(result);
                offerUrl = jsonResult['offerUrl'];
                credentialId = jsonResult['credentialId'];
                console.log(offerUrl);
                console.log(credentialId);
                $(".qrcode").attr("src", "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=" + offerUrl);
                $(".qrcode").show();
                pollingReg(identifier, credentialId, role);
            } catch (e) {
                $(".testo").text('Api key inesistente');
                $(".testo").css('color', 'red');
            }
        },

    });
}

function verifyCredential() {
    $.ajax({
        url: urlAjax,
        type: 'POST',
        data: {functionname: 'verifyCredential', arguments: [],},
        success: function (result) {
            try {
                jsonResult = JSON.parse(result);
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

