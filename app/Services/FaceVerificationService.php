<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

class FaceVerificationService
{
    public function assertSingleFace(string $image): void
    {
        if (config('attendance.face_driver') === 'face-api-js') {
            $response = $this->localRequest('single', ['image' => $image]);
            if ($response->json('face_count') !== 1) {
                throw ValidationException::withMessages(['photo' => 'Ảnh cần có đúng một khuôn mặt rõ ràng.']);
            }

            return;
        }

        $this->assertSameFace($image, $image);
    }

    public function assertSameFace(string $source, string $reference): void
    {
        if (config('attendance.face_driver') === 'face-api-js') {
            $response = $this->localRequest('compare', ['source_image' => $source, 'target_image' => $reference]);
            $distance = $response->json('distance');
            if ($response->json('source_face_count') !== 1 || $response->json('target_face_count') !== 1
                || ! is_numeric($distance) || ! is_finite((float) $distance)
                || (float) $distance > (float) config('attendance.face_max_distance')) {
                throw ValidationException::withMessages(['photo' => 'Khuôn mặt không khớp hoặc ảnh không có đúng một khuôn mặt rõ ràng.']);
            }

            return;
        }

        $url = rtrim((string) config('attendance.face_api_url'), '/');
        $key = (string) config('attendance.face_api_key');
        if ($url === '' || $key === '') {
            throw ValidationException::withMessages(['photo' => 'Dịch vụ xác thực khuôn mặt chưa được cấu hình.']);
        }

        try {
            $response = Http::withHeaders(['x-api-key' => $key])
                ->timeout(15)
                ->attach('source_image', $source, 'source.jpg')
                ->attach('target_image', $reference, 'reference.jpg')
                ->post($url.'/api/v1/verification/verify');
        } catch (Throwable) {
            throw ValidationException::withMessages(['photo' => 'Không thể xác thực khuôn mặt. Vui lòng thử lại.']);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages(['photo' => 'Không thể xác thực khuôn mặt. Vui lòng thử lại.']);
        }

        $results = $response->json('result');
        $minimum = (float) config('attendance.face_min_similarity');
        if (! is_array($results) || count($results) !== 1
            || ! is_array($results[0] ?? null)
            || ! is_array($results[0]['face_matches'] ?? null)
            || count($results[0]['face_matches']) !== 1
            || (float) ($results[0]['source_image_face']['box']['probability'] ?? 0) < 0.9
            || (float) ($results[0]['face_matches'][0]['box']['probability'] ?? 0) < 0.9
            || (float) ($results[0]['face_matches'][0]['similarity'] ?? 0) < $minimum) {
            throw ValidationException::withMessages(['photo' => 'Khuôn mặt không khớp hoặc ảnh không có đúng một khuôn mặt rõ ràng.']);
        }
    }

    private function localRequest(string $path, array $images): \Illuminate\Http\Client\Response
    {
        $url = rtrim((string) config('attendance.face_api_url'), '/');
        if ($url === '') {
            throw ValidationException::withMessages(['photo' => 'Dịch vụ xác thực khuôn mặt chưa được cấu hình.']);
        }

        try {
            $request = Http::timeout(30);
            foreach ($images as $name => $image) {
                $request = $request->attach($name, $image, $name.'.jpg');
            }
            $response = $request->post($url.'/'.$path);
        } catch (Throwable) {
            throw ValidationException::withMessages(['photo' => 'Không thể xác thực khuôn mặt. Vui lòng thử lại.']);
        }

        if (! $response->successful()) {
            throw ValidationException::withMessages(['photo' => 'Không thể xác thực khuôn mặt. Vui lòng thử lại.']);
        }

        return $response;
    }
}
