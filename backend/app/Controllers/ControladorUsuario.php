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
        // Reemplaza con el dominio real de tu frontend (usando ngrok por ejemplo)
        $frontendPermitido = 'https://tu-front.ngrok.app';

        header("Access-Control-Allow-Origin: $frontendPermitido");
        header("Access-Control-Allow-Credentials: true");
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
            // Validación exitosa
            $usuario_id = $datos['id'];
            $nombre = $datos['first_name'] ?? '';
            $apellido = $datos['last_name'] ?? '';
            $username = $datos['username'] ?? '';
        
            $this->verificarOCrearUsuario($usuario_id, $username, $nombre, $apellido);
        
            // Crear sesión
            $session = session();
            $session->set([
                'usuario_id' => $usuario_id,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'nombreDeUsuario' => $username,
                'logueado' => true,
            ]);
        
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
        }else {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'mensaje' => 'Hash no válido, acceso denegado'
            ]);
        }
    }




    public function usuarioLogueado()
    {
        // CORS local
        // Reemplaza con el dominio real de tu frontend (usando ngrok por ejemplo)
        $frontendPermitido = 'https://tu-front.ngrok.app';

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Allow-Methods: GET");


        $session = session();
    
        if ($session->get('logueado')) {
            return $this->response->setStatusCode(200)->setJSON([
                'logueado' => true,
                'usuario' => [
                    'usuario_id' => $session->get('usuario_id'),
                    'nombreDeUsuario' => $session->get('nombreDeUsuario'),
                    
                ]
            ]);
        } else {
            return $this->response->setStatusCode(401)->setJSON([
                'logueado' => false,
                'mensaje' => 'No hay sesión activa'
            ]);
        }
    }




    public function cerrarSesion()
    {
        // CORS local
        // Reemplaza con el dominio real de tu frontend (usando ngrok por ejemplo)
        $frontendPermitido = 'https://tu-front.ngrok.app';

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Allow-Methods: POST");


        $session = session();
        $session->destroy();
    
        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'ok',
            'mensaje' => 'Sesión cerrada exitosamente'
        ]);
    }


    public function obtenerUsuario()
    {


        // CORS local
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: Content-Type");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET");

        
        if (session()->has('usuario_id')) {
            return $this->response->setJSON([
                'id_telegram' => session('usuario_id')
            ]);
        } else {
            return $this->response->setStatusCode(401)->setJSON([
                'error' => 'No autenticado'
            ]);
        }
    }



    public function loginWeb()
    {

        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: Content-Type");
        header('Access-Control-Allow-Methods: POST, OPTIONS');

        // Manejo de preflight OPTIONS
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            return $this->response->setStatusCode(200); // Responde sin hacer nada más
        }

        $datos = $this->request->getJSON(true); // el true convierte a array asociativo
        $usuario = $datos['usuario'] ?? '';
        $clave = $datos['clave'] ?? '';


        $usuarioModel = new UsuarioModel();
        $resultado = $usuarioModel->verificarCredenciales($usuario, $clave);
    
        if ($resultado['valido']) {
            $session = session();
            $session->set([
                'usuario_id' => $resultado['usuario_id'],
                'nombreDeUsuario' => $usuario,
                'logueado' => true,
            ]);

            $sessionName = session_name();
            $sessionId = session_id();
            $expire = gmdate('D, d-M-Y H:i:s T', time() + 7200);

            header("Set-Cookie: $sessionName=$sessionId; Expires=$expire; Path=/; Secure; HttpOnly; SameSite=None; Partitioned");


            return $this->response->setJSON(['status' => 'ok', 'logueado' => true]);

        }
    
        return $this->response->setJSON([
            'status' => 'error',
            'logueado' => false,
            'mensaje' => $resultado['mensaje'] ?? 'Error desconocido'
        ]);
        
    }
    


    
}
