const mysql = require('mysql2');

const DB_HOST=process.env.DB_HOST
const DB_USER=process.env.DB_USER
const DB_USER_PASSWORD=process.env.DB_USER_PASSWORD
const DATABASE=process.env.DATABASE

const connectionItem = {
    host: DB_HOST,
    user: DB_USER,
    password: DB_USER_PASSWORD, 
    database: DATABASE,
    waitForConnections: true,
    connectionLimit: 20,
    maxIdle: 20, // max idle connections, the default value is the same as `connectionLimit`
    idleTimeout: 60000, // idle connections timeout, in milliseconds, the default value 60000
    queueLimit: 0,
    enableKeepAlive: true,
    keepAliveInitialDelay: 0,
}

const pool = mysql.createPool(connectionItem)
  
module.exports = pool
