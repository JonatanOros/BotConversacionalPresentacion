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


    public function obtenerPorNombreArchivo($nombreArchivo)
    {
        try {
            $snapshot = $this->firebase->getDatabase()->collection('presentaciones')->where('titulo', '=', $nombreArchivo)->documents();
    
            foreach ($snapshot as $document) {
                if ($document->exists()) {
                    return $document->data();
                }
            }
    
            return ['error' => 'Archivo no encontrado'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }  
    
    public function obtenerPresentacionPorId($id)
    {
        try {
            $documento = $this->firebase->getDatabase()->collection('presentaciones')->document($id)->snapshot();
            if ($documento->exists()) {
                return $documento->data();
            }
            return null;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    public function getPresentacionesPorUsuarioId($usuarioId)
    {
        try {
            $presentaciones = [];
            $documentos = $this->firebase->getDatabase()
                ->collection('presentaciones')
                ->where('usuario_id', '=', $usuarioId)
                ->documents();
    
            foreach ($documentos as $doc) {
                if ($doc->exists()) {

                    $data = $doc->data();
                    $data['id'] = $doc->id();
                    $presentaciones[] = $data;
                }
            }
            return $presentaciones;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    



}
