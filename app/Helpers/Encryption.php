<?php

function encryptUserId(int $userId): string
{
    $key = 12345;

    do {
        $encrypted = $userId ^ $key;
        $base64Encrypted = base64_encode(str_pad($encrypted, 8, "\0"));
        $encryptedCode = substr($base64Encrypted, 0, 8);
    } while (\App\Models\User::where('identifier', $encryptedCode)->exists());

    return $encryptedCode;
}

function decryptUserHash(string $userHash): int
{
    $key = 12345;
    $decoded = base64_decode($userHash);
    $decrypted = (int)substr($decoded, 0, 8) ^ $key;
    return $decrypted;
}









