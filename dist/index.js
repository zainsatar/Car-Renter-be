const dotenv = require('dotenv');
dotenv.config();
const express = require('express');
const userApi = require('./routes/user');
const homeApi = require('./routes/home');
const carApi = require('./routes/car');
const database = require('./config/database');
const app = express();
const port = 3300;
const {
  mySqlDb
} = database;
app.use(express.urlencoded({
  extended: false
}));
app.use(express.urlencoded({
  extended: true
}));
app.use(express.json());
app.use('/api/users', userApi);
app.use('/api/home', homeApi);
app.use("/api/car", carApi);
mySqlDb.connect(error => {
  if (error) console.log(error);else app.listen(port, () => console.log('Server is Running and db is connected'));
});