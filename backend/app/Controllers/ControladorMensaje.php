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
}
