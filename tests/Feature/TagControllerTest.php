<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use LazilyRefreshDatabase;


    /**
     * A basic feature test example.
     *
     * @return void
     */

    /** @test */

    public function itListsTags()
    {
        $response = $this->get('/api/tags');

        // dd(
        //     $response->json()
        // );

        $response->assertStatus(200);

        $this->assertNotNull($response->json('data')[0]['id']) ;

    }
}
