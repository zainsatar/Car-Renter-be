const api = require('express').Router();
const auth=require("../middleware/auth")
const database = require('../config/database')
const { mySqlDb } = database

api.get('/models', (req, res) => {
    try{
        mySqlDb.query('SELECT * from car_models',async (error, carModels) => {
            if (error) {
                res.status(500).json({  error: 'Unable to Find Car Model', message: error.message });
                return;
            }
            if(carModels.length<1){
                res.status(404).json({ error: 'No Car Model Yet' });
                return;
            }
            res.status(200).json({carModels})
            return;
        })
    }catch(error){
        res.status(500).json({ error: 'Unable to Find Car Model', message: error.message });
    }
});

module.exports = api;