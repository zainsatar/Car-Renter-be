const api = require('express').Router();
const auth=require("../middleware/auth")

api.get('/',auth, (req, res) => {
    res.status(200).json({
        status: 'success',
        message: 'Logged in!',
    });
});

  module.exports = api;