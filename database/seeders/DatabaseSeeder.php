<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Production-safe default: do not create customers or team credentials.
     * Team users should be provisioned explicitly with unique passwords.
     */
    public function run(): void
    {
        // Intentionally empty.
    }
}
