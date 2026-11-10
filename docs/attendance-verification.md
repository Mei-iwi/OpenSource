# Verified self attendance

Self check-in and check-out now require a camera capture, a browser location reading and server-side face verification. HR/Admin register one reference face from the employee detail page. The reference and attendance proofs stay on the private attendance proof disk. The employee record stores only the private path; the app never exposes the reference image as a public URL.

Configure these values in `.env` and run `php artisan migrate` plus `php artisan optimize:clear`:

```dotenv
ATTENDANCE_TIMEZONE=Asia/Ho_Chi_Minh
ATTENDANCE_SITE_LATITUDE=10.80728
ATTENDANCE_SITE_LONGITUDE=106.62863
ATTENDANCE_SITE_RADIUS_METERS=100
ATTENDANCE_MAX_LOCATION_ACCURACY_METERS=100
ATTENDANCE_FACE_API_URL=http://127.0.0.1:8001
ATTENDANCE_FACE_API_KEY=<private CompreFace Face Verification service key>
ATTENDANCE_FACE_MIN_SIMILARITY=0.80
```

The address is HUIT, 140 Lê Trọng Tấn, Tây Thạnh. The coordinates above are an approximate map pin; verify them at the exact workplace and adjust the radius before use. The face service must be a running [CompreFace Face Verification Service](https://github.com/exadel-inc/CompreFace/blob/master/docs/Rest-API-description.md#face-verification-service). Create a verification service and put its API key only in `.env` or a secret store. When the service or key is unavailable, attendance fails closed. The app sends the two images to that service over the configured server connection and requires one clear face with similarity at or above the configured threshold.

Camera and geolocation require browser permission and a secure context. Use `localhost` for local development or HTTPS for a device on the network. A browser supplied GPS position and a camera snapshot can be spoofed; this flow does not provide liveness detection or tamper-proof location attestation. Use a trusted kiosk or a dedicated attestation/liveness provider if those guarantees are required.

The database stores server-recorded UTC check-in/check-out timestamps plus local clock times, and distance from the site instead of the device's exact coordinates. An unfinished camera check-in on a previous date must be checked out before starting a new shift. HR attendance edit and delete routes are disabled; existing records remain readable. Existing manually entered attendance without camera proof is excluded from the unfinished camera-shift rule.

Chat messages and admin broadcast subject/body are encrypted in the database with Laravel `APP_KEY`. The migrations encrypt existing plaintext rows; keep `APP_KEY` backed up securely or old messages cannot be decrypted. Message attachments are managed separately and are not encrypted by this change.
