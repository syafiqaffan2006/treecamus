 // Get the list of cameras and populate the dropdown menu
 function populateCameraList() {
    navigator.mediaDevices.enumerateDevices()
      .then(function(devices) {
        var cameras = [];
        devices.forEach(function(device) {
          if (device.kind === "videoinput") {
            cameras.push(device);
          }
        });
        var cameraList = document.getElementById('camera-list');
        while (cameraList.firstChild) {
          cameraList.removeChild(cameraList.firstChild);
        }
        cameras.forEach(function(camera) {
          var option = document.createElement('option');
          option.value = camera.deviceId;
          option.text = camera.label || 'Camera ' + (cameraList.length + 1);
          cameraList.appendChild(option);
        });
      })
      .catch(function(err) {
        console.error("Error getting camera list: " + err);
      });
  }

  // Start the camera with the selected device ID
  function startCamera() {
    var cameraList = document.getElementById('camera-list');
    var deviceId = cameraList.value;
    var constraints = { video: { deviceId: { exact: deviceId } } };
    navigator.mediaDevices.getUserMedia(constraints)
      .then(function(stream) {
        var video = document.getElementById('camera-preview');
        video.width = 480;

        video.srcObject = stream;
        video.play();
      })
      .catch(function(err) {
        console.error("Error starting camera: " + err);
      });
  }

  // Stop the camera
  function stopCamera() {
    var video = document.getElementById('camera-preview');
    video.pause();
    video.srcObject = null;
  }

  // Capture an image from the camera and add it to the existing evidence file input,
  // so it's uploaded and saved exactly like any other chosen photo.
function captureImage() {
var video = document.getElementById('camera-preview');
var canvas = document.getElementById('image-canvas');

canvas.width = video.videoWidth;
canvas.height = video.videoHeight;
canvas.style.width = '480px';
canvas.getContext('2d').drawImage(video, 0, 0);

canvas.toBlob(function (blob) {
    if (!blob) {
        console.error('Could not capture image from camera.');
        return;
    }

    var fileName = 'camera-capture-' + Date.now() + '.jpg';
    var capturedFile = new File([blob], fileName, { type: 'image/jpeg' });

    var evidenceInput = document.getElementById('evidence');
    var dataTransfer = new DataTransfer();

    // Keep any files already chosen via the file picker
    for (var i = 0; i < evidenceInput.files.length; i++) {
        dataTransfer.items.add(evidenceInput.files[i]);
    }
    dataTransfer.items.add(capturedFile);
    evidenceInput.files = dataTransfer.files;

    // Let the page's existing thumbnail-picker logic know the file list changed
    evidenceInput.dispatchEvent(new Event('change'));
}, 'image/jpeg', 0.92);
}

  // Initialize the camera and dropdown menu
  populateCameraList();

  // Attach event listeners to buttons
  document.getElementById('start-button').addEventListener('click', startCamera);
  document.getElementById('stop-button').addEventListener('click', stopCamera);
  document.getElementById('capture-button').addEventListener('click', captureImage);
