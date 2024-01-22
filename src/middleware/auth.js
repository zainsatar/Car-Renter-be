const jwt = require('jsonwebtoken');

const JWT_SECRET_KEY=process.env.JWT_SECRET_KEY
const JWT_TOKEN=process.env.JWT_TOKEN

const userAuthentication = async(req, res, next) => {
    const token = req.header('Authorization');
    if (!token) return res.status(401).json({ error: 'Access denied' });
    try {
        const decodedToken = jwt.verify(token, JWT_SECRET_KEY);
        req.userEmail = decodedToken;
        next();
    } catch (error) {
        res.status(401).json({ error: 'Invalid token' });
    }
};

module.exports =userAuthentication