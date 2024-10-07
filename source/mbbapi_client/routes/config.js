var express = require('express');
var router = express.Router();
const axios = require('axios');
const sha256 = require('js-sha256');

const low = require('lowdb')
const FileSync = require('lowdb/adapters/FileSync')

const adapter = new FileSync('data/db.json')
const db = low(adapter)
const adapter_config = new FileSync('data/request.json')
const dbconfig = low(adapter_config)

router.post('/', function(req, res, next) {
    req.body.env.forEach(function(env, index){
        if (req.body.env_check == index){
            dbconfig.update("gtp_url", n => env.gtp_url)
                .write()
            dbconfig.update("merchant_key", n => env.merchant_key)
                .write()
            dbconfig.update("merchant_id", n => env.merchant_id)
                .write()
            req.body.env[index].env_use = 'on'
        }
    })

    dbconfig.update("envs", n => req.body.env)
        .write()
    res.redirect(req.header('Referer'))
});


module.exports = router;
