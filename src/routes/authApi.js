const multer = require('multer')
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs')
const api = require('express').Router();
const database = require('../config/database')
const upload = multer()

const JWT_SECRET_KEY = process.env.JWT_SECRET_KEY
const { mySqlDb } = database
const Role = {
  Customer: 'Customer',
  Renter: 'Renter'
}

api.post('/register', upload.any(), (req, res) => {
  const files = req.files
  const { email, name, password } = req.body
  const subscriptionPlan = req.body?.subscriptionPlan
  let role = subscriptionPlan ? Role.Renter : Role.Customer
  if (!name) {
    res.status(404).json({ error: 'name should not be empty' });
  }
  if (!email) {
    res.status(404).json({ error: 'email should not be empty' });
  }
  if (!password) {
    res.status(404).json({ error: 'password should not be empty' });
  }
  const profileImage = files.find(file => file.fieldname === 'profileImage')?.buffer
  const idbackImage = files.find(file => file.fieldname === 'idbackImage')?.buffer
  const idFrontImage = files.find(file => file.fieldname === 'idFrontImage')?.buffer

  try {
    mySqlDb.query('SELECT email from users WHERE email = ?', [email], async (error, results) => {

      if (error) {
        res.status(500).json({ error: "Unable to register", message: error.message });
        return
      } else if (results.length > 0) {
        res.status(400).json({ error: "Email already in use" });
        return
      }
      else {
        const salt = await bcrypt.genSalt()
        const hashedPassword = await bcrypt.hash(password, salt)

        const user = {
          name,
          email,
          profileImage,
          idFrontImage,
          idbackImage,
          subscriptionPlan,
          role,
          password: hashedPassword,
        }

        mySqlDb.query('INSERT INTO users SET ?', user, (error, result) => {
          if (error) {
            res.status(500).json({ error: "Unable to register", message: error.message });
          } else {
            const accessToken = jwt.sign(user.email, JWT_SECRET_KEY);
            res.status(200).json({ jwt: accessToken, user });
          }
        })
      }
    })
  } catch (error) {
    res.status(500).json({ error: "Unable to register", message: error.message });
  }
});


api.get('/login', (req, res) => {
  const email = req.body.email;
  const password = req.body.password;
  try {
    mySqlDb.query('SELECT * from users WHERE email = ?', [email], async (error, results) => {
      if (error) {
        res.status(500).json({ error: 'Unable to login', message: error.message });
        return
      }
      const user = results?.[0]
      if (!user) {
        res.status(404).json({ error: 'User not found' });
        return
      }

      const isEqual = await bcrypt.compare(password,user?.password)

      if (!isEqual) {
        res.status(400).json({ error: 'Incorrect email or password' });
        return
      } else {
        const accessToken = jwt.sign(user.email, JWT_SECRET_KEY);
        console.log(accessToken);
        res.status(200).json({ jwt: accessToken, user });
        return
      }
    })
  } catch (error) {
    res.status(500).json({ error: 'Unable to login', message: error.message });
  }
});

module.exports = api;
