<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UsuarioModel;

class ControladorUsuario extends ResourceController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
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
}
