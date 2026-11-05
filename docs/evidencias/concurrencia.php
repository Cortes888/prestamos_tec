<?php
// Prueba de confiabilidad (ADR-003): manda varios préstamos del mismo equipo al mismo tiempo.
declare(strict_types=1);

$idUsuario = (int) ($argv[1] ?? 0);
$idObjeto = (int) ($argv[2] ?? 0);
$cantidad = (int) ($argv[3] ?? 10);
$url = $argv[4] ?? 'http://localhost/prestamos/api/prestamos';

if ($idUsuario <= 0 || $idObjeto <= 0) {
    exit("Uso: php docs/evidencias/concurrencia.php ID_USUARIO ID_EQUIPO [CANTIDAD]\n");
}

$multi = curl_multi_init();
$peticiones = [];
for ($i = 1; $i <= $cantidad; $i++) {
    $c = curl_init($url);
    curl_setopt_array($c, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode(['id_usuario' => $idUsuario, 'id_objeto' => $idObjeto, 'dias' => 2, 'obs' => "Prueba simultánea {$i}"]),
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_multi_add_handle($multi, $c);
    $peticiones[$i] = $c;
}

// Las manda todas a la vez
do {
    curl_multi_exec($multi, $activas);
    curl_multi_select($multi);
} while ($activas > 0);

$conteo = [];
$masLenta = 0.0;
echo "Solicitudes simultáneas para el equipo #{$idObjeto}:\n\n";
foreach ($peticiones as $i => $c) {
    $codigo = (int) curl_getinfo($c, CURLINFO_RESPONSE_CODE);
    $tiempo = (float) curl_getinfo($c, CURLINFO_TOTAL_TIME);
    $masLenta = max($masLenta, $tiempo);
    $conteo[$codigo] = ($conteo[$codigo] ?? 0) + 1;
    printf("  #%-2d HTTP %d  %.3f s  %s\n", $i, $codigo, $tiempo, curl_multi_getcontent($c));
    curl_multi_remove_handle($multi, $c);
    curl_close($c);
}
curl_multi_close($multi);

ksort($conteo);
echo "\nResumen:\n";
foreach ($conteo as $codigo => $n) {
    echo "  HTTP {$codigo}: {$n}\n";
}
printf("  Respuesta más lenta: %.3f s\n", $masLenta);
echo ($conteo[201] ?? 0) === 1
    ? "\nResultado: solo se aprobó un préstamo. No hubo doble préstamo.\n"
    : "\nResultado: revisar, se esperaba exactamente un préstamo aprobado.\n";
