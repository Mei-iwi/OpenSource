These model weights come from the official [face-api.js weights directory](https://github.com/justadudewhohacks/face-api.js/tree/a86f011d72124e5fb93e59d5c4ab98f699dd5c9c/weights). Only the Tiny Face Detector, tiny 68-point landmark, and face recognition models are included. See `LICENSE` in this directory.

The models run inside the private `face` Docker service. No face image, descriptor, or model result is exposed through its Docker port to the host.
