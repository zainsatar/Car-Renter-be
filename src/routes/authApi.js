const multer = require('multer')
const jwt = require('jsonwebtoken');
const api = require('express').Router();
const database = require('../config/database')
const upload = multer()

const JWT_SECRET_KEY=process.env.JWT_SECRET_KEY
const { mySqlDb } = database
const Role={
  Customer:'Customer',
  Renter:'Renter'
}

api.post('/register', upload.any(), (req, res) => {
  const files = req.files
  const { email, name, password } = req.body
  const subscriptionPlan= req.body?.subscriptionPlan
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
  
  mySqlDb.query('SELECT email from users WHERE email = ?', [email], async (error,results) => {

    if (error) {
      res.status(500).json({ error: "Unable to register" });
    }else if(results.length>0){
      res.status(400).json({ error: "Email already in use" });
    }
    else {
      const user={
        name,
        email,
        profileImage,
        idFrontImage,
        idbackImage,
        password,
        subscriptionPlan,
        role
      }
      // let hashedPassword = await bcrypt.hash(password, 8)
      mySqlDb.query('INSERT INTO users SET ?', user, (error,result) => {
        if (error) {
          res.status(500).json({ error: "Unable to register" });
        } else {
          const accessToken = jwt.sign(user.email, JWT_SECRET_KEY);
          res.status(200).json({ jwt : accessToken , user });
        }
      })
    }
  })
});


api.get('/login', (req, res) => {
  const email = req.body.email;
  const password = req.body.password;
  mySqlDb.query('SELECT * from users WHERE email = ?', [email], async (error, results) => {
    if(error){
      res.status(500).json({ error: 'Unable to login' });
    }
    const user = results[0]
    if(!user){
      res.status(404).json({ error: 'User not found' });
      return
    }
    if (password !== user?.password) {
      res.status(400).json({ error: 'Incorrect password' });
      return
    } else {
      const accessToken = jwt.sign(user.email, JWT_SECRET_KEY);
      console.log(accessToken);
      res.status(200).json({ jwt : accessToken , user });
      return
    }
  })
});
module.exports = api;
