<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTrustedOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldValidate($request) && ! $this->hasTrustedOrigin($request)) {
            abort(403, 'Permintaan ditolak karena origin tidak valid.');
        }

        return $next($request);
    }

    private function shouldValidate(Request $request): bool
    {
        if ($request->isMethodSafe()) {
            return false;
        }

        return true;
    }

    private function hasTrustedOrigin(Request $request): bool
    {
        $source = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if (! $source) {
            return false;
        }

        $originHost = parse_url($source, PHP_URL_HOST);
        $originScheme = parse_url($source, PHP_URL_SCHEME);
        $originPort = parse_url($source, PHP_URL_PORT);

        if (! $originHost || ! $originScheme) {
            return false;
        }

        $trustedHosts = array_filter([
            $request->getHost(),
            parse_url(config('app.url'), PHP_URL_HOST),
        ]);

        $trustedSchemes = array_filter([
            $request->getScheme(),
            parse_url(config('app.url'), PHP_URL_SCHEME),
        ]);

        $trustedPorts = array_filter([
            $request->getPort(),
            parse_url(config('app.url'), PHP_URL_PORT),
        ], fn ($port) => $port !== null);

        $originPort ??= $originScheme === 'https' ? 443 : 80;

        return in_array($originHost, $trustedHosts, true)
            && in_array($originScheme, $trustedSchemes, true)
            && ($trustedPorts === [] || in_array($originPort, $trustedPorts, true));
    }
}
