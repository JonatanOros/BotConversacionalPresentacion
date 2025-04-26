<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UsuarioModel;

class ControladorUsuario extends ResourceController
{
    protected $usuarioModel;
    protected $botToken;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
    }

    public function crearUsuario()
    {
        $userData = [
            'usuario_id' => $this->request->getPost('usuario_id'),
            'nombreDeUsuario' => $this->request->getPost('nombreDeUsuario'),
            'nombre' => $this->request->getPost('nombre'),
            'apellido' => $this->request->getPost('apellido'),
            'rol' => $this->request->getPost('rol') ?? 'invitado',
            'creado_en' => date('Y-m-d H:i:s')
        ];

        return $this->respond($this->usuarioModel->crearUsuario($userData), 200);
    }


    public function verificarOCrearUsuario($usuario_id, $nombreDeUsuario = '', $nombre = '', $apellido = '')
    {
        if (!$this->usuarioModel->existeUsuario($usuario_id)) {
            $userData = [
                'usuario_id' => $usuario_id,
                'nombreDeUsuario' => $nombreDeUsuario,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'rol' => 'invitado',
                'creado_en' => date('Y-m-d H:i:s')
            ];
    
            return $this->usuarioModel->crearUsuario($userData);
        }
    
        return ['message' => 'Usuario ya existe', 'usuario_id' => $usuario_id];
    }



    public function verificarLoginTelegram()
    {
        // CORS local
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Allow-Methods: POST");
    
        // Obtener JSON del frontend
        $json = file_get_contents('php://input');
        $datos = json_decode($json, true);
    
        if (!isset($datos['hash'])) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Falta el hash']);
        }
    
        // Extraer campos y preparar validación
        $check_fields = $datos;
        $hash_recibido = $check_fields['hash'];
        unset($check_fields['hash']);
    
        ksort($check_fields);
    
        $check_string = '';
        foreach ($check_fields as $key => $value) {
            $check_string .= "$key=$value\n";
        }
        $check_string = rtrim($check_string);
    
        $secret_key = hash('sha256', $this->botToken, true);
        $hash_calculado = hash_hmac('sha256', $check_string, $secret_key);
    
        if ($hash_calculado === $hash_recibido) {
            // Validación exitosa, ahora crear/verificar usuario
            $usuario_id = $datos['id'];
            $nombre = $datos['first_name'] ?? '';
            $apellido = $datos['last_name'] ?? '';
            $username = $datos['username'] ?? '';
    
            $this->verificarOCrearUsuario($usuario_id, $username, $nombre, $apellido);
    
            return $this->response->setStatusCode(200)->setJSON([
                'status' => 'ok',
                'mensaje' => 'Usuario autenticado con éxito',
                'usuario' => [
                    'usuario_id' => $usuario_id,
                    'nombre' => $nombre,
                    'apellido' => $apellido,
                    'nombreDeUsuario' => $username
                ]
            ]);
        } else {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'mensaje' => 'Hash no válido, acceso denegado'
            ]);
        }
    }
    
    


    
}
