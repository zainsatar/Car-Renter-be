const mysql = require('mysql2');

const DB_HOST=process.env.DB_HOST
const DB_USER=process.env.DB_USER
const DB_USER_PASSWORD=process.env.DB_USER_PASSWORD
const DATABASE=process.env.DATABASE

const connectionItem = { host: DB_HOST, user: DB_USER, password: DB_USER_PASSWORD, database: DATABASE }
const mySqlDb = mysql.createConnection(connectionItem)

exports.mySqlDb = mySqlDb;
