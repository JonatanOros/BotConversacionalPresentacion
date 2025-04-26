<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Firebase;

class UsuarioModel extends Model
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = new Firebase();
    }

    public function crearUsuario($data)
    {
        try {

            $this->firebase->getDatabase()->collection('usuarios')->document($data['usuario_id'])->set($data);

            return ['message' => 'Usuario creado exitosamente'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }


    public function existeUsuario($usuarioId)
    {
        try {
            $document = $this->firebase->getDatabase()->collection('usuarios')->document($usuarioId)->snapshot();
            return $document->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
    
}
