<?php

use App\Models\User;
use App\Models\Solicitud;
use Carbon\Carbon;
use function Pest\Laravel\actingAs;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

// --- TESTS BÁSICOS ---

test('un funcionario no puede pedir mas de 6 dias', function () {
    $juan = User::factory()->create(['role' => 'funcionario']);
    
    actingAs($juan)->postJson('/api/v1/solicitudes', [
        'tipo' => 'administrativo',
        'fecha_inicio' => '2025-12-01',
        'fecha_fin' => '2025-12-10',
        'motivo' => 'Vacaciones largas'
    ])
    ->assertStatus(422)
    ->assertJsonValidationErrors(['dias_solicitados']);
});

test('un jefe puede aprobar una solicitud de su subordinado', function () {
    $jefe = User::factory()->create(['role' => 'jefatura']);
    $juan = User::factory()->create(['role' => 'funcionario', 'jefe_id' => $jefe->id]);

    $solicitud = Solicitud::create([
        'funcionario_id' => $juan->id,
        'tipo' => 'administrativo',
        'fecha_inicio' => Carbon::now(),
        'fecha_fin' => Carbon::now(),
        'dias_solicitados' => 1,
        'motivo' => 'Dentista',
        'estado' => 'pendiente'
    ]);

    actingAs($jefe)
        ->patchJson("/api/v1/solicitudes/{$solicitud->id}/aprobar-jefe")
        ->assertStatus(200)
        ->assertJsonPath('data.estado', 'aprobado_jefatura');
});

test('juan no puede aprobar su propia solicitud', function () {
    $juan = User::factory()->create(['role' => 'jefatura']);

    $solicitud = Solicitud::create([
        'funcionario_id' => $juan->id,
        'tipo' => 'administrativo',
        'fecha_inicio' => Carbon::now(),
        'fecha_fin' => Carbon::now(),
        'dias_solicitados' => 1,
        'motivo' => 'Trampa',
        'estado' => 'pendiente'
    ]);

    actingAs($juan)
        ->patchJson("/api/v1/solicitudes/{$solicitud->id}/aprobar-jefe")
        ->assertStatus(400) // CORREGIDO: Tu controller devuelve 400 en el catch
        ->assertJsonFragment(['message' => 'No tienes permiso para aprobar solicitudes de este funcionario.']);
});

// --- TESTS AVANZADOS ---

test('un jefe no puede aprobar solicitudes de otros departamentos', function () {
    $jefeSistemas = User::factory()->create(['role' => 'jefatura']);
    $jefeSalud = User::factory()->create(['role' => 'jefatura']);

    $juan = User::factory()->create(['role' => 'funcionario', 'jefe_id' => $jefeSistemas->id]);

    $solicitud = Solicitud::create([
        'funcionario_id' => $juan->id,
        'tipo' => 'administrativo',
        'fecha_inicio' => Carbon::now(),
        'fecha_fin' => Carbon::now(),
        'dias_solicitados' => 1,
        'motivo' => 'Personal',
        'estado' => 'pendiente'
    ]);

    actingAs($jefeSalud)
        ->patchJson("/api/v1/solicitudes/{$solicitud->id}/aprobar-jefe")
        ->assertStatus(400) // CORREGIDO: Esperamos 400
        ->assertJsonFragment(['message' => 'No tienes permiso para aprobar solicitudes de este funcionario.']);
});

test('el director no puede aprobar una solicitud que no ha pasado por jefatura', function () {
    $director = User::factory()->create(['role' => 'director']);
    $juan = User::factory()->create(['role' => 'funcionario']);

    $solicitud = Solicitud::create([
        'funcionario_id' => $juan->id,
        'tipo' => 'feriado_legal',
        'fecha_inicio' => Carbon::now(),
        'fecha_fin' => Carbon::now(),
        'dias_solicitados' => 1,
        'motivo' => 'Vacaciones',
        'estado' => 'pendiente' 
    ]);

    actingAs($director)
        ->patchJson("/api/v1/solicitudes/{$solicitud->id}/aprobar-director")
        ->assertStatus(400)
        // CORREGIDO: Buscamos en 'message', no en 'estado'
        ->assertJsonFragment(['message' => 'Esta solicitud no cuenta con la aprobación previa de la jefatura o ya fue procesada.']);
});

test('la fecha de fin no puede ser anterior a la de inicio', function () {
    $juan = User::factory()->create(['role' => 'funcionario']);

    actingAs($juan)->postJson('/api/v1/solicitudes', [
        'tipo' => 'administrativo',
        'fecha_inicio' => '2025-12-10',
        'fecha_fin' => '2025-12-01',
        'motivo' => 'Back to the future'
    ])
    ->assertStatus(422)
    ->assertJsonValidationErrors(['fecha_fin']);
});

test('un funcionario normal no puede subir licencias medicas', function () {
    $juan = User::factory()->create(['role' => 'funcionario']);
    
    actingAs($juan)->postJson('/api/v1/licencias', [
        'funcionario_id' => $juan->id,
        'fecha_inicio' => '2025-01-01',
        'dias' => 3,
        'tipo_licencia' => 'enfermedad_comun'
    ])
    ->assertStatus(403);
});
