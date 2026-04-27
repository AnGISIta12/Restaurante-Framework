<?php

namespace Database\Seeders;

use App\Models\Horario;
use App\Models\Mesa;
use App\Models\Plato;
use App\Models\Reservacion;
use App\Models\Rol;
use App\Models\Tipo;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         |--------------------------------------------------------------
         | 1) Roles básicos
         |--------------------------------------------------------------
         */
        $rolAdministrador = Rol::firstOrCreate(['nombre' => Rol::ADMINISTRADOR]);
        Rol::firstOrCreate(['nombre' => Rol::MAITRE]);
        Rol::firstOrCreate(['nombre' => Rol::MESERO]);
        Rol::firstOrCreate(['nombre' => Rol::COCINERO]);
        $rolCliente = Rol::firstOrCreate(['nombre' => Rol::CLIENTE]);

        /*
         |--------------------------------------------------------------
         | 2) Usuarios de prueba (administrador y cliente)
         |--------------------------------------------------------------
         */
        $admin = User::updateOrCreate(
            ['name' => 'admin_prueba'],
            [
                'email' => 'admin@restaurante.com',
                'password' => bcrypt('admin123'),
                'rol_id' => $rolAdministrador->id,
            ]
        );

        $cliente = User::updateOrCreate(
            ['name' => 'cliente_prueba'],
            [
                'email' => 'cliente@restaurante.com',
                'password' => bcrypt('cliente123'),
                'rol_id' => $rolCliente->id,
            ]
        );
     /*
         |--------------------------------------------------------------
         | 3) Mesas (6) con capacidades y estados
         |--------------------------------------------------------------
         */
        $mesasSemilla = [
            ['capacidad' => 2, 'estado' => 'disponible'],
            ['capacidad' => 2, 'estado' => 'reservada'],
            ['capacidad' => 4, 'estado' => 'disponible'],
            ['capacidad' => 4, 'estado' => 'ocupada'],
            ['capacidad' => 6, 'estado' => 'reservada'],
            ['capacidad' => 6, 'estado' => 'disponible'],
        ];

        $mesasCreadas = [];
        foreach ($mesasSemilla as $mesaData) {
            $mesasCreadas[] = Mesa::firstOrCreate([
                'capacidad' => $mesaData['capacidad'],
                'estado' => $mesaData['estado']
            ]);
        }
        /*
         |--------------------------------------------------------------
         | 4) Platos del menú (5)
         |--------------------------------------------------------------
         */
        $tipoMenu = Tipo::firstOrCreate(['nombre' => 'General']);

        $platosSemilla = [
            ['nombre' => 'Ceviche Mixto', 'descripcion' => 'Pescado y mariscos con limón y cilantro.', 'precio' => 12.50],
            ['nombre' => 'Hamburguesa Clásica', 'descripcion' => 'Carne de res, queso, lechuga y tomate.', 'precio' => 9.90],
            ['nombre' => 'Pasta Alfredo', 'descripcion' => 'Pasta en salsa cremosa con queso parmesano.', 'precio' => 11.75],
            ['nombre' => 'Pizza Margarita', 'descripcion' => 'Salsa de tomate, mozzarella y albahaca.', 'precio' => 10.25],
            ['nombre' => 'Tiramisu', 'descripcion' => 'Postre italiano con café y cacao.', 'precio' => 6.30],
        ];

        foreach ($platosSemilla as $platoData) {
            $payload = [
                'descripcion' => $platoData['descripcion'],
                'precio' => $platoData['precio'],
            ];

            if (Schema::hasColumn('platos', 'tipo_id')) {
                $payload['tipo_id'] = $tipoMenu->id;
            }

            if (Schema::hasColumn('platos', 'tiempo')) {
                $payload['tiempo'] = '00:20:00';
            }

            Plato::updateOrCreate(
                ['nombre' => $platoData['nombre']],
                $payload
            );
        }

       /*
         |--------------------------------------------------------------
         | 5) Reservaciones (2) vinculadas a usuario y mesa
         |--------------------------------------------------------------
         */
        $fecha1 = Carbon::now()->addDay()->setHour(19)->setMinute(0)->setSecond(0);
        $fecha2 = Carbon::now()->addDays(2)->setHour(20)->setMinute(30)->setSecond(0);

        $mesaParaReserva1 = $mesasCreadas[0] ?? Mesa::first();
        $mesaParaReserva2 = $mesasCreadas[2] ?? Mesa::skip(1)->first() ?? Mesa::first();

        $reservacion1 = Reservacion::updateOrCreate(
            [
                'user_id' => $cliente->id,
                'cantidad' => 2,
                'mesa_id' => $mesaParaReserva1->id,
            ],
            [
                'estado' => 'confirmada',
                'fecha' => $fecha1->toDateString(),
                'hora' => $fecha1->toTimeString(),
            ]
        );

        $reservacion2 = Reservacion::updateOrCreate(
            [
                'user_id' => $cliente->id,
                'cantidad' => 4,
                'mesa_id' => $mesaParaReserva2->id,
            ],
            [
                'estado' => 'confirmada',
                'fecha' => $fecha2->toDateString(),
                'hora' => $fecha2->toTimeString(),
            ]
        );
    }
}
