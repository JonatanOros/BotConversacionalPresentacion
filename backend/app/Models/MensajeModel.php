<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Firebase;

class MensajeModel extends Model
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = new Firebase();
    }

    public function crearMensaje($data)
    {
        try {

            $this->firebase->getDatabase()->collection('mensajes')->document($data['mensaje_id'])->set($data);
            
            return ['message' => 'Mensaje registrado exitosamente'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
