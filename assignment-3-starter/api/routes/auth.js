const express = require("express");
const jwt = require("jsonwebtoken");
const users = require("../data/users");

const router = express.Router();
const SECRET = process.env.JWT_SECRET || "sajad_secret_key_assignment3_developer";

router.post("/login", (req, res, next) => {
  const { username, password } = req.body;
  const user = users.find(u => u.username === username && u.password === password);

  if (!user) {
    const error = new Error("Invalid username or password");
    error.statusCode = 401;
    error.errorType = "Unauthorized";
    return next(error);
  }

  const token = jwt.sign(
    { userId: user.id, role: user.role },
    SECRET,
    { expiresIn: "1h" }
  );

  res.json({ token });
});

module.exports = router;