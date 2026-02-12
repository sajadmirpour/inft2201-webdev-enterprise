<?php
use PHPUnit\Framework\TestCase;
use Application\Mail;

class MailTest extends TestCase {
    protected PDO $pdo;

    protected function setUp(): void
    {
        $dsn = "pgsql:host=" . getenv('DB_TEST_HOST') . ";dbname=" . getenv('DB_TEST_NAME');
        $this->pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Clean and reinitialize the table
        $this->pdo->exec("DROP TABLE IF EXISTS mail;");
        $this->pdo->exec("
            CREATE TABLE mail (
                id SERIAL PRIMARY KEY,
                subject TEXT NOT NULL,
                body TEXT NOT NULL
            );
        ");
    }

    public function testCreateMail() {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Alice", "Hello world");
        $this->assertIsInt($id);
        $this->assertEquals(1, $id);
    }

    public function testGetMail() {
        $mail = new Mail($this->pdo);
        $mail->createMail("Alice", "Hello world");
        $result = $mail->getMail(1);
        $this->assertIsArray($result);
        $this->assertEquals("Alice", $result['subject']);
        $this->assertEquals("Hello world", $result['body']);
    }

    public function testGetMailNotFound() {
        $mail = new Mail($this->pdo);
        $result = $mail->getMail(999);
        $this->assertFalse($result);
    }

}