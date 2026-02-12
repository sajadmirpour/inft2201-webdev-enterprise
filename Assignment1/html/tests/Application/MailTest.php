<?php
// Sajad Mirpour 
// February 11 2026
// MailTest.php
// This file contains unit tests for the Mail class using PHPUnit. 
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
    // Create tests
    public function testCreateMail() {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Alice", "Hello world");
        $this->assertIsInt($id);
        $this->assertEquals(1, $id);
    }
    // Get tests
    public function testGetMail() {
        $mail = new Mail($this->pdo);
        $mail->createMail("Alice", "Hello world");
        $result = $mail->getMail(1);
        $this->assertIsArray($result);
        $this->assertEquals("Alice", $result['subject']);
        $this->assertEquals("Hello world", $result['body']);
    }
    // Get not found test
    public function testGetMailNotFound() {
        $mail = new Mail($this->pdo);
        $result = $mail->getMail(999);
        $this->assertFalse($result);
    }
    // Get all tests
    public function testGetAllMail() {
        $mail = new Mail($this->pdo);
        $mail->createMail("Alice", "Hello");
        $mail->createMail("Sajad", "World"); // added a second name test to ensure multiple records are returned for testing purposes 
        $results = $mail->getAllMail();
        $this->assertIsArray($results);
        $this->assertCount(2, $results);
    }
    // Update tests
    public function testUpdateMail() {
        $mail = new Mail($this->pdo);
        $mail->createMail("Alice", "Hello");
        $updated = $mail->updateMail(1, "Updated subject", "Updated body");
        $this->assertTrue($updated);

        $result = $mail->getMail(1);
        $this->assertEquals("Updated subject", $result['subject']);
        $this->assertEquals("Updated body", $result['body']);
    }
    // Update not found test
    public function testUpdateMailNotFound() {
        $mail = new Mail($this->pdo);
        $updated = $mail->updateMail(999, "No", "No");
        $this->assertFalse($updated);
    }
    // Delete tests
    public function testDeleteMail() {
        $mail = new Mail($this->pdo);
        $mail->createMail("Alice", "Hello");
        $deleted = $mail->deleteMail(1);
        $this->assertTrue($deleted);

        $result = $mail->getMail(1);
        $this->assertFalse($result);
    }
    // Delete not found test
    public function testDeleteMailNotFound() {
        $mail = new Mail($this->pdo);
        $deleted = $mail->deleteMail(999);
        $this->assertFalse($deleted);
    }

}