const multer = require( 'multer' )
const upload = multer()
const express = require( "express" )
const router = new express.Router()
const auth = require( "../middleware/auth" )
const pool = require( '../config/database' )
const buildUpdateQuery = require( '../helper/helper' )

router.get( '/models', auth, ( req, res ) => {
    try
    {
        pool.query( 'SELECT * from car_models', async ( error, carModels ) => {
            if ( error )
            {
                res.status( 500 ).json( { error: 'Unable to Find Car Model', message: error.message } );
                return;
            }
            if ( carModels.length < 1 )
            {
                res.status( 404 ).json( { error: 'No Car Model Yet' } );
                return;
            }
            res.status( 200 ).json( carModels )
            return;
        } )
    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Unable to Find Car Model', message: error.message } );
    }
} );

router.post( '/add', upload.any(), auth, ( req, res ) => {
    try
    {
        // const { renter_id, company_id, car_name, color, fuel_type, mileage, engine, kms_driven, address, longitude, latitude, province, city, famous_place_nearby} = req.body
        const { renter_id, company_id, car_name, province, city, address } = req.body
        if ( !renter_id )
        {
            res.status( 500 ).json( { error: "renter_id should not be empty" } )
            return
        }
        if ( !company_id )
        {
            res.status( 500 ).json( { error: "company_id should not be empty" } )
            return
        }
        if ( !car_name )
        {
            res.status( 500 ).json( { error: "car_name should not be empty" } )
            return
        }
        if ( !address )
        {
            res.status( 500 ).json( { error: "address should not be empty" } )
            return
        }
        if ( !city )
        {
            res.status( 500 ).json( { error: "city should not be empty" } )
            return
        }
        if ( !province )
        {
            res.status( 500 ).json( { error: "province should not be empty" } )
            return
        }
        const files = req.files
        const Image1 = files?.find( file => file.fieldname === 'Image1' )?.buffer
        const Image2 = files?.find( file => file.fieldname === 'Image2' )?.buffer
        const Image3 = files?.find( file => file.fieldname === 'Image3' )?.buffer
        const Image4 = files?.find( file => file.fieldname === 'Image4' )?.buffer

        const car = { ...req.body, Image1, Image2, Image3, Image4, ratings: -1 }
        pool.query( 'INSERT INTO cars SET ?', car, ( error, result ) => {
            if ( error )
            {
                res.status( 500 ).json( { error: 'Car is not added.', message: error.message } );
            } else
            {
                res.status( 200 ).json( { message: "Car added successfully." } );
            }
        } )

    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Car is not added...', message: error.message } );
    }
} )

router.get( '/getCarsByRenter', auth, ( req, res ) => {
    try
    {
        const { renter_id } = req.query
        pool.query( 'SELECT * from cars WHERE renter_id = ?', [ renter_id ], async ( error, cars ) => {
            if ( error )
            {
                res.status( 500 ).json( { error: "Unable to find cars", message: error.message } );
                return
            } else if ( cars.length < 1 )
            {
                res.status( 404 ).json( { error: "No car found for this renter" } );
                return;
            }
            res.status( 200 ).json( cars )
        } )

    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Unable to find cars', message: error.message } );
    }
} )

router.get( '/getCarsByRenterAndModel', auth, ( req, res ) => {
    try
    {
        const { renter_id, model_id } = req.query
        pool.query( 'SELECT * from cars WHERE renter_id = ? AND company_id = ?', [ renter_id, model_id ], async ( error, cars ) => {
            if ( error )
            {
                res.status( 500 ).json( { error: "Unable to find cars", message: error.message } );
                return
            } else if ( cars.length < 1 )
            {
                res.status( 404 ).json( { error: "No car found against given renter and model" } );
                return;
            }
            res.status( 200 ).json( cars )
        } )

    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Unable to find cars', message: error.message } );
    }
} )

router.get( '/getCarById', auth, ( req, res ) => {
    try
    {
        const { car_id } = req.query
        pool.query( 'SELECT * from cars WHERE car_id = ? ', [ car_id ], async ( error, cars ) => {
            if ( error )
            {
                res.status( 500 ).json( { error: "Unable to find car", message: error.message } );
                return
            } else if ( cars.length < 1 )
            {
                res.status( 404 ).json( { error: "No car found against given car id" } );
                return;
            }
            res.status( 200 ).json( cars[ 0 ] )
        } )

    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Unable to find car', message: error.message } );
    }
} )

router.get( '/', auth, ( req, res ) => {
    try
    {
        pool.query( 'SELECT * from cars', [], async ( error, cars ) => {
            if ( error )
            {
                res.status( 500 ).json( { error: "Error while getting cars", message: error.message } );
                return
            } else if ( cars.length < 1 )
            {
                res.status( 404 ).json( { error: "No cars found" } );
                return;
            }
            res.status( 200 ).json( cars )
        } )

    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Error while getting cars', message: error.message } );
    }
} )

router.post( '/:carId', auth, ( req, res ) => {
    try
    {
        const id = req.params.carId
        const body = req.body
        const tableName = 'cars'
        if ( !id )
        {
            res.status( 500 ).json( { error: "car_id not found" } )
            return
        }
        if ( body === {} )
        {
            res.status( 500 ).json( { error: "Nothing to Update" } )
            return
        }

        const { query, values } = buildUpdateQuery( body, tableName, id )
        pool.query( query, values, ( err, cars ) => {
            if ( err )
            {
                console.error( 'Error updating record', err );
                return;
            }
            res.status( 200 ).json( { message: 'record updated' } )
        } )
    } catch ( error )
    {
        res.status( 500 ).json( { error: 'Error updating record', message: error.message } );
    }
} );

module.exports = router;
