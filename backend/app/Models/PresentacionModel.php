<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Firebase;

class PresentacionModel extends Model
{
    protected $firebase;

    public function __construct()
    {
        $this->firebase = new Firebase();
    }

    public function crearPresentacion($data)
    {
        try {

            $this->firebase->getDatabase()->collection('presentaciones')->document($data['presentacion_id'])->set($data);
           
            return ['message' => 'Presentación creada exitosamente'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
