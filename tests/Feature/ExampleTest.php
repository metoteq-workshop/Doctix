<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_and_tools_directory_render_successfully(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/')->assertStatus(200)->assertSee('Doctix');
        $this->get('/tools')->assertStatus(200)->assertSee('Merge PDF');
        $this->get('/pricing')->assertStatus(200)->assertSee('Monthly Premium');
        $this->get('/sitemap.xml')->assertStatus(200);
        $this->get('/robots.txt')->assertStatus(200);
    }
}
