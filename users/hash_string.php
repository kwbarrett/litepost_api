<?php

$stringToHash = "Gr33nOn!on";
$hashedString = password_hash($stringToHash, PASSWORD_DEFAULT);

echo $hashedString;