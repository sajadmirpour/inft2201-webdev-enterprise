import http from "http";
import fs from "fs";
import jwt from "jsonwebtoken";

const JWT_SECRET = "zB5!nW8*tS3kH6jY9v";

http
  .createServer((req, res) => {
    if (req.method === "GET") {
      res.writeHead(200, { "Content-Type": "text/plain" });
      res.end("Hello Apache!\n");

      return;
    }

    if (req.method === "POST") {
      if (req.url === "/login") {
        let body = "";
        req.on("data", (chunk) => {
          body += chunk;
        });
        req.on("end", () => {
          try {
            body = JSON.parse(body);
            
            // read users from users.txt and parse them into userid, username, password , and role
            const users = fs
              .readFileSync("./users.txt", "utf-8")
              .trim()
              .split("\n")
              .map((line) => {
                const [userId, username, password, role] = line.split(",");
                return { userId: parseInt(userId), username, password, role };
              });

            //find user by username
            const user = users.find((u) => u.username === body.username);

            // return error if username not found
            if (!user) {
              res.writeHead(404, { "Content-Type": "text/plain" }); // 404 error for user not found
              res.end(`${body.username} not found\n`);
              return;
            }

            // returns 401 error if password doesnt match
            if (user.password !== body.password) {
              res.writeHead(401, { "Content-Type": "text/plain" });
              res.end("Incorrect password\n");
              return;
            }

            // if successful then it will sign and return a jwt containing userId and role
            const token = jwt.sign(
              { userId: user.userId, role: user.role },
              JWT_SECRET,
              { expiresIn: "1h" }
            );

            res.writeHead(200, { "Content-Type": "application/json" }); // return token as json
            res.end(JSON.stringify({ token }));

          } catch (err) {
            console.log(err);
            res.writeHead(500, { "Content-Type": "text/plain" }); // 500 error for server error
            res.end("Server error\n");
          }
        });
      }

      return;
    }

    res.writeHead(404, { "Content-Type": "text/plain" });
    res.end("Not found\n");
  })
  .listen(8000);

console.log("listening on port 8000");