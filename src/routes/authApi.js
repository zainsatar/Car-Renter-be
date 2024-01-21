const multer = require('multer')
const api = require('express').Router();
const database = require('../config/database')

const upload = multer()
const { mySqlDb } = database

api.post('/register', upload.any(), (req, res) => {
  const files = req.files

  const { email, name, password } = req.body
  if (!name) {
    res.status(404).json({ message: 'name should not be empty' });
  }
  if (!email) {
    res.status(404).json({ message: 'email should not be empty' });
  }
  if (!password) {
    res.status(404).json({ message: 'password should not be empty' });
  }
  const profileImage = files.find(file => file.fieldname === 'profileImage')?.buffer
  const idbackImage = files.find(file => file.fieldname === 'idbackImage')?.buffer
  const idFrontImage = files.find(file => file.fieldname === 'idFrontImage')?.buffer

  mySqlDb.query('SELECT email from users WHERE email = ?', [email], async (error) => {

    if (error) {
      res.status(404).json({ message: 'Email already exists' });
    }
    else {
      // let hashedPassword = await bcrypt.hash(password, 8)
      mySqlDb.query('INSERT INTO users SET ?', {
        name,
        email,
        profileImage,
        idFrontImage,
        idbackImage,
        password,
      }, (error) => {
        if (error) {
          res.status(404).json({ message: error.message });
        } else {
          res.status(200).json({ message: "account created" });
        }
      })
    }
  })
});


api.get('/login', (req, res) => {
  const email = req.body.email;
  const pass = req.body.password;

  mySqlDb.query('SELECT * from users WHERE email = ?', [email], async (error, results) => {
    const user = results[0]
    if(!user){
      res.status(404).json({ message: 'user not found' });
    }
    if (pass !== user?.password) {
      res.status(500).json({ message: 'email or password is invalid' });
    } else {
      res.status(200).json({ message: 'Login Success' });

    }
  })
});

module.exports = api;
