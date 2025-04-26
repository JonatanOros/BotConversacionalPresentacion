<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\MensajeModel;

class ControladorMensaje extends ResourceController
{
    protected $mensajeModel;

    public function __construct()
    {
        $this->mensajeModel = new MensajeModel();
    }

    public function crearMensaje()
    {
        $messageData = [
            'mensaje_id' => uniqid(),
            'chat_id' => $this->request->getPost('chat_id'),
            'usuario_id' => $this->request->getPost('usuario_id'),
            'texto' => $this->request->getPost('texto'),
            'tipo' => $this->request->getPost('tipo'),
            'direccion' => $this->request->getPost('direccion'),
            'hora_de_creacion' => date('Y-m-d H:i:s')
        ];

        return $this->respond($this->mensajeModel->crearMensaje($messageData), 200);
    }


    // Este método se usa internamente desde otros controladores
    public function guardar($mensajeData)
    {
        if (!$this->validarDatos($mensajeData)) {
            return ['error' => 'Faltan datos requeridos'];
        }

        $mensajeData['hora_de_creacion'] = date('Y-m-d H:i:s');
        $mensajeData['mensaje_id'] = uniqid();

        return $this->mensajeModel->guardarMensaje($mensajeData);
    }

    private function validarDatos($data)
    {
        return isset($data['chat_id'], $data['usuario_id'], $data['texto'], $data['tipo'], $data['direccion']);
    }


    //obtiene los mensajes del usuario por medio de su id
    public function obtenerMensajesPorUsuario($usuarioId)
    {

        // Agregar CORS headers manuales
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        $mensajes = $this->mensajeModel->obtenerMensajesPorUsuario($usuarioId);
        return $this->respond($mensajes);
    }
}
