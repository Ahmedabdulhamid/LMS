<?php

namespace App\Services;

use App\Exceptions\MuxVideoException;
use Firebase\JWT\JWT;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class MuxVideoService
{
    public function importVideo(string $reference, string $sourceUrl): array
    {
        return $this->request('POST', '/assets', $this->assetSettings($reference) + ['inputs' => [['url' => $sourceUrl]]]);
    }

    public function getAsset(string $id): array
    {
        $this->assertId($id);

        return $this->request('GET', '/assets/'.$id);
    }

    public function deleteAsset(string $id): void
    {
        $this->assertId($id);
        try {
            $this->request('DELETE', '/assets/'.$id);
        } catch (MuxVideoException $exception) {
            if ($exception->httpStatus !== 404) {
                throw $exception;
            }
        }
    }

    public function tokenTtl(): int
    {
        return max(60, min(3600, (int) config('services.mux.playback_token_ttl', 900)));
    }

    public function generatePlaybackToken(string $playbackId, string $audience = 'v'): string
    {
        $this->assertId($playbackId);
        $keyId = config('services.mux.signing_key_id');
        $privateKey = config('services.mux.signing_private_key');
        if (! $keyId || ! $privateKey || ! in_array($audience, ['v', 't'], true)) {
            throw new MuxVideoException(reason: 'not_configured');
        }
        try {
            $pem = str_contains($privateKey, '-----BEGIN') ? str_replace('\\n', "\n", $privateKey) : base64_decode($privateKey, true);
            if (! $pem) {
                throw new \RuntimeException;
            }

            return JWT::encode(['sub' => $playbackId, 'aud' => $audience,
                'exp' => now()->timestamp + $this->tokenTtl(), 'kid' => $keyId], $pem, 'RS256', $keyId);
        } catch (\Throwable) {
            throw new MuxVideoException(reason: 'signing_error');
        }
    }

    public function getPlaybackUrl(string $playbackId): string
    {
        return 'https://stream.mux.com/'.$playbackId.'.m3u8?token='.$this->generatePlaybackToken($playbackId);
    }

    public function getThumbnailUrl(string $playbackId): string
    {
        return 'https://image.mux.com/'.$playbackId.'/thumbnail.jpg?token='.$this->generatePlaybackToken($playbackId, 't');
    }

    public function validId(?string $id): bool
    {
        return is_string($id) && (bool) preg_match('/^[A-Za-z0-9_-]{1,255}$/D', $id);
    }

    private function assertId(string $id): void
    {
        if (! $this->validId($id)) {
            throw new MuxVideoException(reason: 'invalid_response');
        }
    }

    private function assetSettings(string $reference): array
    {
        return ['playback_policies' => ['signed'], 'video_quality' => 'basic', 'passthrough' => $reference];
    }

    private function request(string $method, string $path, array $body = []): array
    {
        if (! config('services.mux.token_id') || ! config('services.mux.token_secret')) {
            throw new MuxVideoException(reason: 'not_configured');
        }
        try {
            $response = Http::withBasicAuth(config('services.mux.token_id'), config('services.mux.token_secret'))
                ->acceptJson()->connectTimeout(10)->timeout(30)->withoutRedirecting()
                ->send($method, 'https://api.mux.com/video/v1'.$path, $body ? ['json' => $body] : []);
        } catch (ConnectionException) {
            throw new MuxVideoException(reason: 'connection_failed');
        }
        // Never throw a RequestException or retain the response: it may contain secrets/upload URLs.
        if (! $response->successful()) {
            throw new MuxVideoException($response->status(), match ($response->status()) {
                401, 403 => 'access_denied', 402 => 'capacity_error', 429 => 'rate_limited',
                400, 413, 422 => 'upload_rejected', default => 'service_error',
            });
        }
        $data = $response->json('data');
        if ($method !== 'DELETE' && ! is_array($data)) {
            throw new MuxVideoException(reason: 'invalid_response');
        }

        return is_array($data) ? $data : [];
    }
}
