var express = require('express');
var router = express.Router();
const axios = require('axios');
const sha256 = require('js-sha256');

axios.defaults.timeout = 15000; // ms

const low = require('lowdb')
const FileSync = require('lowdb/adapters/FileSync')

const adapter = new FileSync('data/db.json')
const db = low(adapter)
const adapter_config = new FileSync('data/request.json')
const dbconfig = low(adapter_config)

const publicIp = require('public-ip');
(async () => {
  await publicIp.v4().then(function(e){
    dbconfig.getState()
    dbconfig.update("ip", n => e).write();
  })
})();

router.post("/", function(req, res,){
  
  db.read()
  dbconfig.read()
  var request_obj = dbconfig.getState();
  var url = request_obj.gtp_url;
  var merchant_id = request_obj.merchant_id;
  var api_version = request_obj.version;
  var merchant_key = request_obj.merchant_key;
  var ips = request_obj.ip;

  let form_data = false;
  if (Object.keys(req.body).length != 0){
    form_data = true;
  }
  if (form_data){
    requested = req.body.action ? req.body.action : '';
    
    var html_actions = [];
    request_obj.actions.map((action) => {
      const active = (requested == action.name)  ? 'active' : ''; 
      html_actions.push({
          action_name: action.name,
          active: active
      })
    })

    action = req.body.action

    final_params = {
        
    }

    final_params = Object.assign(final_params, req.body);

    delete final_params.digest;

    hash_string = '';
    Object.entries(final_params).map(([key, val], index) =>{
      length = Object.keys(final_params).length;
      if (typeof val === "object"){
        val = encodeURI(JSON.stringify(val));
      }
      hash_string += key + "=" + val + ((length != index+1) ? "&" : '');
    })
    hash = sha256(hash_string+"&key="+merchant_key);
    // console.log(hash_string,"hash_string");
    // .toUpperCase()
    final_params = Object.assign(final_params, {digest: hash});

    let startTime = Date.now();
    axios.get(url, {params: final_params})
    // axios.post(url, final_params)
      .then(function (response){
          console.log(response.body,'success request_body')
          console.log(response.data,'success request_data')
          if (response.data && response.data.status == '1'){
            // _INSERT DATA.
            insertData(response.data);

            return_data = response.data
          }else{
            return_data = response.data.error
          }

          if (response.data && response.data.status == '1'){
            res_status = 'success';
            return_data = response.data
          }else if (response.data && response.data.error){
            res_status = 'error';
            return_data = response.data
          }else{
            res_status = 'serv_error_msg';
            return_data = response.data
          }

          // get latest data
          var data = db.getState();
          var config = {}
          config.envs = dbconfig.get('envs').value()

          var return_index = {
              "title": req.body.action,
              "buttons": html_actions,
              "request": final_params,
              "response": return_data,
              "response_status": res_status,
              "time": axiosTimerFunc(startTime),
              "params": hash_string,
              "data": data,
              "config": config,
              "ip": ips,
          }
          res.render('index', return_index);
      })
      .catch(function (error){
          // get latest data
          console.log(error,'error request')
          var data = db.getState();
          var config = {}
          config.envs = dbconfig.get('envs').value()

          var return_index = {
              "title": req.body.action,
              "buttons": html_actions,
              "request": final_params,
              "response": error,
              "time": axiosTimerFunc(startTime),
              "params": hash_string,
              "data": data,
              "config": config,
              "ip": ips,
          }
          res.render('index', return_index);
      })
  }
})

/* GET home page. */
router.get('/', function(req, res) {
  db.read()
  dbconfig.read()
  var request_obj = dbconfig.getState();
  var url = request_obj.gtp_url;
  var merchant_id = request_obj.merchant_id;
  var api_version = request_obj.version;
  var merchant_key = request_obj.merchant_key;
  var ips = request_obj.ip;
  

  var data = [];
  
  var data = db.getState();
  var config = {}
  config.envs = dbconfig.get('envs').value()
  
  let startTime = Date.now();
  requested = req.query.action ? req.query.action : '';

  var html_actions = [];
  request_obj.actions.map((action) => {
      const active = (requested == action.name)    ? 'active' : ''; 
      html_actions.push({
          action_name: action.name,
          active: active
      })
  })

  if (requested == ''){
    var return_index = {
      "title": "Home",
      "buttons": html_actions,
      "config": config,
      "ip": ips,
    }
    res.render('index', return_index);
    return;
  }

  action = request_obj.actions.filter(obj =>{
    return obj.name === requested;
  })

  final_params = {
      version: api_version,
      merchant_id: merchant_id,
      action: requested,
  }

  final_params = Object.assign(final_params, action[0].params);

  date = new Date();
  // timestamp = { timestamp: date.toISOString().split('.')[0] };
  timestamp = { timestamp: getFormattedCurrentDateTime() };
  final_params = Object.assign(final_params, timestamp);

  hash_string = '';
  Object.entries(final_params).map(([key, val], index) =>{
      length = Object.keys(final_params).length;
      if (typeof val === "object"){
        val = encodeURI(JSON.stringify(val));
        val = JSON.stringify(val);
      }
      hash_string += key + "=" + val + ((length != index+1) ? "&" : '');
  })

  console.log(hash_string,);

  hash = sha256(merchant_key+hash_string);
  final_params = Object.assign(final_params, {digest: hash});

  var return_index = {
      "title": requested,
      "buttons": html_actions,
      "request": final_params,
      "time": axiosTimerFunc(startTime),
      "data": data,
      "config": config,
      "ip": ips,
  }
  res.render('index', return_index);
  return;
});

const axiosTimerFunc = (startTime) => {
  let now = Date.now();
  let seconds = Math.floor((now - startTime)/1000);
  let milliseconds = Math.floor((now - startTime)%1000);
  return `${seconds}.${milliseconds} seconds`;
}

const insertData = (data) => {
  var db_collection = false;
  switch(data.action_requested) {
    case 'price_acebuy':
    case 'price_acesell':
      db_collection = 'price_stream';
      break;
    case 'spot_acebuy':
    case 'spot_acesell':
      db_collection = 'orders';
      break;
    default:
      db_collection = false;
  }

  if (db_collection){
    db.get(db_collection)
      .unshift(data)
      .write()
  }

}

const getFormattedCurrentDateTime = () => {
  var currentDate = new Date();
  var formattedDateTime = currentDate.getFullYear() + "-" +
  ("0" + (currentDate.getMonth() + 1)).slice(-2) + "-" +
  ("0" + currentDate.getDate()).slice(-2) + " " +
  ("0" + currentDate.getHours()).slice(-2) + ":" +
  ("0" + currentDate.getMinutes()).slice(-2) + ":" +
  ("0" + currentDate.getSeconds()).slice(-2);
  return formattedDateTime;
}

module.exports = router;
