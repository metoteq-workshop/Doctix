<?php

namespace Tests\Unit;

use App\Services\FileSecurityService;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function test_filename_sanitization_prevents_path_traversal_and_php_execution(): void
    {
        $security = new FileSecurityService();
        $clean = $security->sanitizeFilename('../../etc/passwd/../report.pdf');
        $this->assertSame('etcpasswdreport.pdf', $clean);

        $this->expectException(\InvalidArgumentException::class);
        $security->sanitizeFilename('shell.php');
    }
}
