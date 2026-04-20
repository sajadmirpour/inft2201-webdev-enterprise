const jwt = require("jsonwebtoken");
const SECRET = process.env.JWT_SECRET || "sajad_secret_key_assignment3_developer";

module.exports = function authenticateJWT(req, res, next) {
  const authHeader = req.headers.authorization;

  if (authHeader && authHeader.startsWith("Bearer ")) {
    const token = authHeader.split(" ")[1];

    jwt.verify(token, SECRET, (err, user) => {
      if (err) {
        const error = new Error("Invalid or expired token");
        error.statusCode = 401;
        error.errorType = "Unauthorized";
        return next(error);
      }
      req.user = user;
      next();
    });
  } else {
    const error = new Error("Authentication token missing");
    error.statusCode = 401;
    error.errorType = "Unauthorized";
    next(error);
  }
};