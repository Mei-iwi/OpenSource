<?php

namespace Tests\Unit;

use App\Services\FaceVerificationService;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class FaceApiJsVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('attendance.face_driver', 'face-api-js');
        config()->set('attendance.face_api_url', 'http://face.test');
        config()->set('attendance.face_max_distance', 0.40);
    }

    public function test_enrollment_requires_exactly_one_face(): void
    {
        Http::fake(['face.test/single' => Http::response(['face_count' => 1])]);
        app(FaceVerificationService::class)->assertSingleFace('image');

        Http::swap(new Factory);
        Http::fake(['face.test/single' => Http::response(['face_count' => 2])]);
        $this->expectException(ValidationException::class);
        app(FaceVerificationService::class)->assertSingleFace('multiple faces');
    }

    public function test_match_accepts_close_faces_and_rejects_mismatch(): void
    {
        Http::swap(new Factory);
        Http::fake(['face.test/compare' => Http::response([
            'source_face_count' => 1, 'target_face_count' => 1, 'distance' => 0.30,
        ])]);
        app(FaceVerificationService::class)->assertSameFace('capture', 'reference');

        Http::swap(new Factory);
        Http::fake(['face.test/compare' => Http::response([
            'source_face_count' => 1, 'target_face_count' => 1, 'distance' => 0.49,
        ])]);
        $this->expectException(ValidationException::class);
        app(FaceVerificationService::class)->assertSameFace('other person', 'reference');
    }

    public function test_unavailable_model_fails_closed(): void
    {
        Http::fake(['face.test/*' => Http::response(['error' => 'unavailable'], 503)]);
        $this->expectException(ValidationException::class);
        app(FaceVerificationService::class)->assertSameFace('capture', 'reference');
    }
}
