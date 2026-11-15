const http = require('node:http');
const path = require('node:path');
const Busboy = require('busboy');
const sharp = require('sharp');
const faceapi = require('face-api.js');

const port = Number(process.env.PORT || 8001);
const modelPath = path.join(__dirname, 'models');
let ready = false;

function send(response, status, data) {
    response.writeHead(status, {'Content-Type': 'application/json; charset=utf-8', 'Cache-Control': 'no-store'});
    response.end(JSON.stringify(data));
}

function readImages(request) {
    return new Promise((resolve, reject) => {
        if (!request.headers['content-type']?.startsWith('multipart/form-data')) {
            reject(new Error('multipart required'));
            return;
        }
        const images = {};
        const parser = Busboy({headers: request.headers, limits: {files: 2, fileSize: 3 * 1024 * 1024, fields: 0}});
        let invalid = false;
        parser.on('file', (name, file) => {
            const chunks = [];
            file.on('data', chunk => chunks.push(chunk));
            file.on('limit', () => { invalid = true; });
            file.on('end', () => { images[name] = Buffer.concat(chunks); });
        });
        parser.on('filesLimit', () => { invalid = true; });
        parser.on('error', reject);
        parser.on('close', () => invalid ? reject(new Error('image too large')) : resolve(images));
        request.pipe(parser);
    });
}

async function facesIn(buffer) {
    if (!buffer?.length) throw new Error('image required');
    const metadata = await sharp(buffer).metadata();
    if (!metadata.width || !metadata.height || metadata.width < 160 || metadata.height < 160
        || metadata.width > 5000 || metadata.height > 5000) {
        throw new Error('invalid image dimensions');
    }
    const {data, info} = await sharp(buffer).rotate()
        .resize({width: 640, height: 640, fit: 'inside', withoutEnlargement: true})
        .removeAlpha().toColourspace('srgb').raw().toBuffer({resolveWithObject: true});
    if (info.channels !== 3) throw new Error('invalid image channels');
    const image = faceapi.tf.tensor3d(new Uint8Array(data), [info.height, info.width, 3], 'int32');
    try {
        return await faceapi.detectAllFaces(image, new faceapi.TinyFaceDetectorOptions({inputSize: 416, scoreThreshold: 0.55}))
            .withFaceLandmarks(true).withFaceDescriptors();
    } finally {
        image.dispose();
    }
}

const server = http.createServer(async (request, response) => {
    if (request.method === 'GET' && request.url === '/health') {
        send(response, ready ? 200 : 503, {ready});
        return;
    }
    if (!ready) { send(response, 503, {error: 'model unavailable'}); return; }
    if (request.method !== 'POST' || !['/single', '/compare'].includes(request.url)) {
        send(response, 404, {error: 'not found'});
        return;
    }
    try {
        const images = await readImages(request);
        if (request.url === '/single') {
            const faces = await facesIn(images.image);
            send(response, 200, {face_count: faces.length});
            return;
        }
        const source = await facesIn(images.source_image);
        const target = await facesIn(images.target_image);
        const distance = source.length === 1 && target.length === 1
            ? faceapi.euclideanDistance(source[0].descriptor, target[0].descriptor)
            : null;
        send(response, 200, {source_face_count: source.length, target_face_count: target.length, distance});
    } catch (error) {
        if (error.message === 'image required' || error.message.startsWith('invalid image')
            || error.message === 'multipart required' || error.message === 'image too large') {
            send(response, 422, {error: 'invalid image'});
            return;
        }
        console.error('face verification failed:', error.message);
        send(response, 503, {error: 'face verification unavailable'});
    }
});

(async () => {
    await faceapi.tf.setBackend('cpu');
    await faceapi.nets.tinyFaceDetector.loadFromDisk(modelPath);
    await faceapi.nets.faceLandmark68TinyNet.loadFromDisk(modelPath);
    await faceapi.nets.faceRecognitionNet.loadFromDisk(modelPath);
    ready = true;
    server.listen(port, '0.0.0.0', () => console.log(`face-api.js ready on ${port}`));
})().catch(error => {
    console.error('face models failed to load:', error.message);
    process.exit(1);
});
