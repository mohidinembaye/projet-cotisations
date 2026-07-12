<?php
require_once __DIR__ . '/../core/Auth.php';

function apprenantDashboard()
{
    verifierRole('apprenant');
    $flash = flash_get();
    require __DIR__ . '/../views/apprenant/dashboard.php';
}