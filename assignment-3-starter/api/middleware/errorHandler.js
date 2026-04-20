// Centralized error handler.
// This should be the LAST app.use(...) in server.js.

module.exports = function errorHandler(err, req, res, next) {
  const statusCode = err.statusCode || 500;
  const errorType = err.errorType || "InternalServerError";
  const requestId = req.requestId || null;
  const safeMessage =
    statusCode === 500
      ? "An unexpected error occurred." : err.message || "An unexpected error occurred.";

  console.error(`Unhandled error for request ${requestId}:`, err.message);

  // Add Retry-After header for rate limiting
  if (statusCode === 429 && err.retryAfterSeconds) {
    res.set("Retry-After", String(err.retryAfterSeconds));
  }

  res.status(statusCode).json({
    error: errorType,
    message: safeMessage,
    statusCode: statusCode,
    requestId: requestId,
    timestamp: new Date().toISOString(),
    ...(statusCode === 429 && err.retryAfterSeconds
      ? { retryAfter: err.retryAfterSeconds }
      : {})
  });
};