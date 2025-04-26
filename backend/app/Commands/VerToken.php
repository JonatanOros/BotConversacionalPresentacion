<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class VerToken extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'ver:token';
    protected $description = 'Muestra el token del bot desde el archivo .env';

    public function run(array $params)
    {
        $token = env('TELEGRAM_BOT_TOKEN');
        if ($token) {
            CLI::write(" Token encontrado: " . $token, 'green');
        } else {
            CLI::error("No se encontró el token. ¿Está definido en el archivo .env?");
        }
    }
}
