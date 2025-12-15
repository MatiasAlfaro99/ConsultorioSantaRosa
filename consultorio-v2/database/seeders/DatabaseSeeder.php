<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ADMIN (Soporte TI / Desarrollador)
        // Tiene acceso técnico total, pero no necesariamente firma documentos administrativos.
        User::create([
            'name' => 'Admin Sistema',
            'email' => 'admin@cesfam.cl',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'cargo' => 'Soporte TI',
        ]);

        // 2. DIRECTOR (Autoridad Máxima)
        // Segunda firma en solicitudes, crea comunicados, ve todo.
        User::create([
            'name' => 'Director General',
            'email' => 'director@cesfam.cl',
            'password' => Hash::make('password'),
            'role' => 'director',
            'cargo' => 'Director CESFAM',
        ]);

        // 3. SUBDIRECCIÓN (Administrativa/Clínica)
        // IMPORTANTE SEGÚN PDF: Únicos encargados de subir Licencias Médicas (RF5).
        User::create([
            'name' => 'Subdirector Administrativo',
            'email' => 'subdirector@cesfam.cl',
            'password' => Hash::make('password'),
            'role' => 'subdireccion',
            'cargo' => 'Subdirector Admin',
        ]);

        // 4. JEFATURA (Supervisores)
        // Primera firma en solicitudes.
        // NOTA: Usamos 'jefatura' para coincidir con tu código de validación (Policy/Request).
        $jefe = User::create([
            'name' => 'Jefe Administrativo',
            'email' => 'jefe@cesfam.cl',
            'password' => Hash::make('password'),
            'role' => 'jefatura',
            'cargo' => 'Jefe de Finanzas',
        ]);

        // 5. FUNCIONARIO (Usuario Base)
        // Solicita días, ve comunicados, descarga sus documentos.
        User::create([
            'name' => 'Juan Perez',
            'email' => 'juan@cesfam.cl',
            'password' => Hash::make('password'),
            'role' => 'funcionario',
            'cargo' => 'Administrativo',
            'jefe_id' => $jefe->id, // Asignamos al jefe creado arriba
        ]);
    }
}
