// Sajad Mirpour
// April 20 2026
// rateLimit.js

// Very simple in-memory rate limiter for demo purposes.
// Requirements (from assignment spec):
// - Track requests per IP OR per user (token), your choice.
// - Limit to RATE_LIMIT_MAX requests per RATE_LIMIT_WINDOW_SECONDS.
// - When exceeded, produce an error (429 Too Many Requests) via next(err).
// - Include a Retry-After header in the final response (set that in errorHandler).

const windowMs = (parseInt(process.env.RATE_LIMIT_WINDOW_SECONDS, 10) || 60) * 1000;
const maxRequests = parseInt(process.env.RATE_LIMIT_MAX, 10) || 5;

const buckets = new Map();

module.exports = function rateLimit(req, res, next) {
  const key = req.ip;
  const now = Date.now();

  if (!buckets.has(key)) {
    buckets.set(key, { count: 1, windowStart: now });
    return next();
  }

  const data = buckets.get(key);

  if (now - data.windowStart > windowMs) {
    data.count = 1;
    data.windowStart = now;
    return next();
  }

  data.count++;

  if (data.count > maxRequests) {
    const error = new Error("Rate limit exceeded. Please try again later.");
    error.statusCode = 429;
    error.errorType = "TooManyRequests";
    error.retryAfterSeconds = Math.ceil((data.windowStart + windowMs - now) / 1000);
    return next(error);
  }

  next();
};