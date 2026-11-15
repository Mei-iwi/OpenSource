# Verified self attendance

Self check-in and check-out require a camera capture, a browser location reading and server-side face verification. HR/Admin register one reference face from the employee detail page using the browser camera, previewing the capture before saving. The reference and attendance proofs stay on the private attendance proof disk. The employee record stores only the private path; the app never exposes the reference image as a public URL.

Configure these values in `.env` and run `php artisan migrate` plus `php artisan optimize:clear`:

```dotenv
ATTENDANCE_TIMEZONE=Asia/Ho_Chi_Minh
ATTENDANCE_SITE_LATITUDE=10.80728
ATTENDANCE_SITE_LONGITUDE=106.62863
ATTENDANCE_SITE_RADIUS_METERS=100
ATTENDANCE_MAX_LOCATION_ACCURACY_METERS=100
ATTENDANCE_FACE_DRIVER=face-api-js
ATTENDANCE_FACE_API_URL=http://127.0.0.1:8001
ATTENDANCE_FACE_MAX_DISTANCE=0.40
```

The address is HUIT, 140 Lê Trọng Tấn, Tây Thạnh. The coordinates above are an approximate map pin; verify them at the exact workplace and adjust the radius before use. Docker Compose starts a private Node face-api.js service and the Laravel app together; no external API key is needed. The service loads the Tiny Face Detector, tiny landmarks and face recognition models from local files. It requires exactly one face in each image and returns the descriptor distance. Laravel accepts only distances at or below `ATTENDANCE_FACE_MAX_DISTANCE` (default 0.40); unavailable models or invalid images fail closed. The threshold is a conservative default and should be evaluated with representative employee photos before production use.

The camera needs HTTPS or localhost in the browser. For direct PHP development, start the face service with `cd face-service && npm ci && npm start`, then use the URL above. Docker uses `http://face:8001` internally and does not expose this port to the host. The original CompreFace integration remains available with `ATTENDANCE_FACE_DRIVER=compreface`, a service URL and `ATTENDANCE_FACE_API_KEY` in local environment settings. Camera enrollment does not provide liveness detection; a face match alone cannot prove a live person is in front of the camera.

Location checking is enabled by default in Docker and direct PHP development. The browser requests GPS and the server enforces the configured 100 m radius and accuracy limit. For a temporary off-site test only, set `ATTENDANCE_ENFORCE_LOCATION=false` locally and rebuild the app; restore `true` afterward.

Camera and geolocation require browser permission and a secure context. Use `localhost` for local development or HTTPS for a device on the network. A browser supplied GPS position and a camera snapshot can be spoofed; this flow does not provide liveness detection or tamper-proof location attestation. Use a trusted kiosk or a dedicated attestation/liveness provider if those guarantees are required.

The database stores server-recorded UTC check-in/check-out timestamps plus local clock times, and distance from the site instead of the device's exact coordinates. An unfinished camera check-in on a previous date must be checked out before starting a new shift. HR attendance edit and delete routes are disabled; existing records remain readable. Existing manually entered attendance without camera proof is excluded from the unfinished camera-shift rule.

Chat messages and admin broadcast subject/body are encrypted in the database with Laravel `APP_KEY`. The migrations encrypt existing plaintext rows; keep `APP_KEY` backed up securely or old messages cannot be decrypted. Message attachments are managed separately and are not encrypted by this change.
