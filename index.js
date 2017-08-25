var http = require('http');

var url = require('url');

http.createServer(function (req, res) {


    var params = url.parse(req.url, true).query;

    console.log(params.password);
    console.log(params.v1);
    console.log(params.v2);


    res.writeHead(200, {'Content-Type': 'application/json'});

    var CryptoJS = require("crypto-js");

    var password = params.password;
    var passwordMd5 = CryptoJS.MD5(password);
    var passwordKey = CryptoJS.SHA256(CryptoJS.SHA256(passwordMd5 + params.v1) + params.v2);
    var encryptedPassword = CryptoJS.AES.encrypt(passwordMd5, passwordKey, {mode: CryptoJS.mode.ECB,padding: CryptoJS.pad.NoPadding});
    encryptedPassword = CryptoJS.enc.Base64.parse(encryptedPassword.toString()).toString(CryptoJS.enc.Hex);
    console.log(encryptedPassword);
    var data = {result:encryptedPassword};
    res.end(JSON.stringify(data));





    // res.end('helloworld\n');

}).listen(8888, "0.0.0.0");
console.log('Server running at http://0.0.0.0:8888/');


