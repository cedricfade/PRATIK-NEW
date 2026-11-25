<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccueilTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_d_accueil_s_affiche_meme_base_vide(): void
    {
        $this->get('/')->assertOk()->assertSee('Pharmacies de garde');
    }
}
