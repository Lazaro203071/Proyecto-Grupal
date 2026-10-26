<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware dual de autenticación para integraciones inter-equipos (Módulo 7.10).
 *
 * Acepta dos métodos de autenticación:
 * 1. Firma HMAC-SHA256 (Service-to-Service): Cabeceras X-Team-Timestamp + X-Team-Signature
 *    (o X-Campus-Timestamp + X-Campus-Signature) verificadas contra config('services.team4.shared_secret').
 * 2. Token Bearer Sanctum: Usuario con rol 'system', 'admin', 'platform_admin' o permiso 'integrations.ingest'.
 */
class EnsureIntegrationAuthorized
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Team-Signature') ?? $request->header('X-Campus-Signature');
        $timestamp = $request->header('X-Team-Timestamp') ?? $request->header('X-Campus-Timestamp');

        // ── 1. Verificación por firma HMAC si se envían cabeceras de firma ──
        if ($signature && $timestamp) {
            $secret = (string) config('services.team4.shared_secret', 'secreto_equipo4_eq3_2026');
            $allowedClockSkew = 300; // 5 minutos de tolerancia

            if (! ctype_digit((string) $timestamp)) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Firma HMAC inválida: timestamp no numérico.',
                ], 401);
            }

            $skew = abs(time() - (int) $timestamp);
            if ($skew > $allowedClockSkew) {
                return response()->json([
                    'success' => false,
                    'error'   => 'Firma HMAC expirada (desfase de reloj superior a 300s).',
                ], 401);
            }

            $path     = '/' . ltrim($request->path(), '/');
            $bodyHash = hash('sha256', $request->getContent());
            $payload  = $timestamp . '|' . strtoupper($request->method()) . '|' . $path . '|' . $bodyHash;
            $expected = hash_hmac('sha256', $payload, $secret);

            if (! hash_equals($expected, $signature)) {
                Log::warning('Firma HMAC de integración rechazada', [
                    'ip'         => $request->ip(),
                    'path'       => $path,
                    'received'   => $signature,
                    'skew_sec'   => $skew,
                ]);

                return response()->json([
                    'success' => false,
                    'error'   => 'Firma HMAC de integración inválida.',
                ], 401);
            }

            // Autenticación por HMAC exitosa
            $request->attributes->set('auth_method', 'hmac');
            return $next($request);
        }

        // ── 2. Verificación por Bearer Token (Sanctum) ──
        $user = auth('sanctum')->user();
        if ($user) {
            if ($user->hasRole('system')
                || $user->hasRole('platform_admin')
                || $user->hasRole('admin')
                || $user->hasPermission('integrations.ingest')
            ) {
                auth()->setUser($user);
                $request->attributes->set('auth_method', 'sanctum');
                return $next($request);
            }

            return response()->json([
                'success' => false,
                'error'   => 'Permisos insuficientes para ingerir eventos de integración.',
            ], 403);
        }

        return response()->json([
            'success' => false,
            'error'   => 'No autorizado: se requiere cabecera de firma HMAC (X-Team-Signature / X-Team-Timestamp) o Token Bearer válido.',
        ], 401);
    }
}
