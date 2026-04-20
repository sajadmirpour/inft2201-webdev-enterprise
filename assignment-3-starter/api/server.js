const express = require("express");
const requestLogger = require("./middleware/requestLogger");
const rateLimit = require("./middleware/rateLimit");
const errorHandler = require("./middleware/errorHandler");

const authRoutes = require("./routes/auth");
const mailRoutes = require("./routes/mail");
const statusRoutes = require("./routes/status");

const app = express();

app.use(express.json());
app.use(requestLogger);

// Apply rate limiting 
app.use(rateLimit);

// Mount routes
app.use("/status", statusRoutes);
app.use("/auth", authRoutes);
app.use("/mail", mailRoutes);

// Centralized error handler
app.use(errorHandler);

const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Assignment 3 API listening on port ${PORT}`);
});