<?php
$db_config = [
    'host2' => 'localhost',
    'dbname2' => 'yisumonimyisumat_gnostocracy',
    'user2' => 'yisumonimyisumat_gnostocracy',  // שם המשתמש הנכון לפי cPanel
    'pass2' => 'sdfsfSDF465jDS',  // עדכן כאן את הסיסמה הנכונה
    // Gemini API key (server-side only). Prefer env var GEMINI_API_KEY; fallback to this value.
    // IMPORTANT: do not commit real secrets; set it on the server after FTP.
    'gemini_api_key' => 'AIzaSyDATHfqsdC96HV6CtqSYaWbJ0Bcf-mH52I',

    // Optional: secret used to sign "remember login" cookie (so users don't get disconnected).
    // Prefer env var GEFEN_AUTH_SECRET; fallback to this value.
    'auth_secret' => '',
];
