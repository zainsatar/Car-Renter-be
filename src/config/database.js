const mysql = require('mysql2');

const DB_HOST=process.env.DB_HOST
const DB_USER=process.env.DB_USER
const DB_USER_PASSWORD=process.env.DB_USER_PASSWORD
const DATABASE=process.env.DATABASE

const connectionItem = { host: DB_HOST, user: DB_USER, password: DB_USER_PASSWORD, database: DATABASE,waitForConnections: true, connectionLimit: 100, queueLimit: 0 }

const pool = mysql.createPool(connectionItem)
// pool.getConnection()
//   .then(conn => {
//     const res = conn.query('SELECT 1');
//     conn.release();
//     return res;
//   }).then(results => {
//     console.log('Connected to MySQL DB');
//   }).catch(err => {
//     console.log(err); 
//   });

  
module.exports = pool
