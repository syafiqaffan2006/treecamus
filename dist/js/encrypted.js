
var originalCode = `
var imageLoader = {
    getImageUrl: function(filename) {
        var key = "Tr33CAM-LOAD3R1MG"; // Replace with your own secret key
        var encodedFilename = encodeURIComponent(filename);
        var obfuscatedUrl = "display_images.php?key=" + key + "&filename=" + encodedFilename;
        return obfuscatedUrl;
    }
};
`;

// Encrypt the code using a key
var key = "Tr33CAMsdsdsdcvvvfd"; // Replace with your encryption k3y
var encryptedCode = CryptoJS.AES.encrypt(originalCode, key).toString();

//console.log(encryptedCode);
