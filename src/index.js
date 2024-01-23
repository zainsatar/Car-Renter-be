const dotenv = require('dotenv');
dotenv.config();

const express = require('express');
const authApi = require('./routes/authApi');
const homeApi = require('./routes/home')
const carModelsApi=require('./routes/carModel')
const database = require('./config/database');
var bodyParser = require('body-parser')

const app = express();
const port = 3300;
const { mySqlDb } = database

app.use(bodyParser.urlencoded({
  extended: true
}));
app.use(bodyParser.json());
// app.use(express.json());

app.use('/api/users', authApi);
app.use('/api/home', homeApi);
app.use("/api/car",carModelsApi)

mySqlDb.connect((error) => {
  if (error) console.log(error)
  else
    app.listen(port, () => console.log('Server is Running and db is connected'));
})