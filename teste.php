<?php

$moodleUrl = 'https://ibraima.sieno.pt/sgei/webservice/rest/server.php';
$token = 'e8401f0e06e5e7886ed1222c67589c09';

$dados = [
    'wstoken' => $token,
    'wsfunction' => 'core_user_create_users',
    'moodlewsrestformat' => 'json',

    'users[0][username]' => 'aluno001',
    'users[0][password]' => 'Aluno@12345',
    'users[0][firstname]' => 'Joao',
    'users[0][lastname]' => 'Silva',
    'users[0][email]' => 'aluno001@example.com',
    'users[0][auth]' => 'manual',
    'users[0][idnumber]' => 'aluno001'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $moodleUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dados));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$resposta = curl_exec($ch);
curl_close($ch);

echo $resposta;