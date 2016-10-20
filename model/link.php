<?php

/* 
 * AUTEUR: Fabien Meunier
 * PROJECT: Third_Type_Tapes_2_server
 * PATH: Third_Type_Tapes_2_server/model/
 * NAME: link.php
 */

class Link extends Model
{
    var $models = array('artiste');

    /**
     *  affiche le nombre d'éléments définit par le paramètre
     *  @param string $limit restreint les résultats retournés
     */
    public function index($limit = "0, 10")
    {
        $model = $this->models[0];
        $d['totalArtistes'] = $this->$model->findAll(array('fields' => 'COUNT(*) as total'));
        $d['artistes'] = $this->$model->findAll(array('limit' => $limit));
        return $d;
    }    
}

