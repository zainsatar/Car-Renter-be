const multer = require('multer')
const express=require("express")
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs')
const router=new express.Router()
const upload = multer()
const auth=require("../middleware/auth")
const pool=require("../config/database")

const JWT_SECRET_KEY = process.env.JWT_SECRET_KEY
const Role = {
  Customer: 'Customer',
  Renter: 'Renter'
}

router.post('/register', upload.any(), (req, res) => {
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
  const idBackImage = files.find(file => file.fieldname === 'idBackImage')?.buffer
  const idFrontImage = files.find(file => file.fieldname === 'idFrontImage')?.buffer

  try {
    pool.query('SELECT email from users WHERE email = ?', [email], async (error, results) => {

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
          idBackImage,
          subscriptionPlan,
          role,
          password: hashedPassword,
        }

        pool.query('INSERT INTO users SET ?', user, (error, result) => {
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


router.post('/login',(req, res) => {
  const {email,password}=req.body
  if (!email) {
    res.status(404).json({ error: 'email should not be empty' });
  }
  if (!password) {
    res.status(404).json({ error: 'password should not be empty' });
  }
  try {
    pool.query('SELECT * from users WHERE email = ?', [email], async (error, results) => {
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
        res.status(200).json({ jwt: accessToken, user });
        return
      }
    })
  } catch (error) {
    res.status(500).json({ error: 'Unable to login', message: error.message });
  }
});

router.delete('/delete',auth,async(req,res)=>{
  const {password,id}=req.body
  if(!id){
    res.status(500).send({error:"id is required"})
    return
  }
  if(!password){
    res.status(500).send({error:"password is required"})
    return
  }
  try{
    pool.query('SELECT * from users WHERE id = ?', [id], async (error, results) => {
      if (error) {
        res.status(500).json({ error: 'Unable to get user', message: error.message });
        return
      }
      const user = results?.[0]
      if (!user) {
        res.status(404).json({ error: 'User not found' });
        return
      }
      const isEqual = await bcrypt.compare(password,user?.password)
      if (!isEqual) {
        res.status(400).json({ error: 'Password is incorrect' });
        return
      }
      pool.query('DELETE FROM users WHERE id= ?',[id],(error,result)=>{
        if (error) {
          res.status(500).json({ error: 'Unable to delete user', message: error.message });
          return
        }
        if(result?.affectedRows>0){
          res.status(200).json({message:"User deleted successfully"})
          return
        }
      })
    })
  }catch(error){
    res.status(500).json({ error: 'Unable to delete user', message: error.message });
  }
})

module.exports = router;
