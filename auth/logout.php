<?php
require_once '../config/config.php';

// Destroy session and redirect to landing page
session_destroy();
redirect('index.php');
