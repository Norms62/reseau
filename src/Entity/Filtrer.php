<?php
namespace App\Entity;

class Filtrer {

    /**
     * @var string|null
     */
    private $prestataire;

    /**
     * @return string|null
     */
	public function getPrestataire() : ?string { 
        return $this->prestataire;
    }

    public function setPrestataire($prestataire){
        $this->prestataire = $prestataire;
        return $this;
    }




}
?>