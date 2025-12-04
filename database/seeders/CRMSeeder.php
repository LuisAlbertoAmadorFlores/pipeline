<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stage;
use App\Models\Deal;

class CRMSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Prospect', 'position' => 1],
            ['name' => 'Calificación', 'position' => 2],
            ['name' => 'Propuesta', 'position' => 3],
            ['name' => 'Negociación', 'position' => 4],
            ['name' => 'Ganado', 'position' => 5],
            ['name' => 'Perdido', 'position' => 6],
        ];

        foreach ($stages as $i => $s) {
            $s['position'] = $i + 1;
            Stage::updateOrCreate(['name' => $s['name']], $s);
        }

        $prospect = Stage::where('name', 'Prospect')->first();
        $proposal = Stage::where('name', 'Propuesta')->first();

        Deal::updateOrCreate([
            'title' => 'Acuerdo con ACME',
        ], [
            'company' => 'ACME S.A.',
            'value' => 12000,
            'stage_id' => $prospect?->id,
            'contact_name' => 'Juan Perez',
            'contact_email' => 'juan@acme.test',
            'notes' => 'Interesado en paquete anual',
            'position' => 1,
        ]);

        Deal::updateOrCreate([
            'title' => 'Proyecto Beta',
        ], [
            'company' => 'Beta Ltd.',
            'value' => 45000,
            'stage_id' => $proposal?->id,
            'contact_name' => 'Ana Gomez',
            'contact_email' => 'ana@beta.test',
            'notes' => 'Esperando respuesta a la propuesta',
            'position' => 1,
        ]);
    }
}
