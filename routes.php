<?php
    
    return [
        '' => function(){
            require 'index.php';
        },
        'activites' => function(){
            require 'gestion_activites/voir_activites.php';
        },
        'participants' => function(){
            require 'gestion_participants/voir_participants.php';
        },
        'mon_compte' => function(){
            require 'parametres/gestion_compte/voir_profile.php';
        },
        'connexion' => function(){
            require 'auth/connexion.php';
        }
    ]
?>