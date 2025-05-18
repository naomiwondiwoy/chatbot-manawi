<?php
// aiml_parser.php

function parseAIML($input, $folder = 'aiml')
{
    $input = trim(strtoupper($input)); // agar case insensitive dan matching

    // Cari semua file AIML di folder
    $files = glob("$folder/*.aiml");

    foreach ($files as $file) {
        // Load XML
        $xml = simplexml_load_file($file);

        if ($xml === false) continue; // skip file kalau gagal load

        foreach ($xml->category as $category) {
            $pattern = trim((string)$category->pattern);

            if ($pattern === $input) {
                // Ketemu pattern cocok
                $template = (string)$category->template;
                return $template;
            }
        }
    }

    return null; // kalau tidak ada yang cocok
}
