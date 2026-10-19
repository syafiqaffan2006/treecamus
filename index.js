// New index.js - Updated by faris 29/2/2024
window.addEventListener('load', () => {
    registerSW();
});

async function registerSW() {
    if ('serviceWorker' in navigator) {
        try {
            const registration = await navigator.serviceWorker.register('./sw.js');
            // console.log('Service Worker registered successfully:', registration);
        } catch (error) {
            console.error('Service Worker registration failed:', error);
        }
    } else {
        // console.log('Service Worker is not supported in this browser.');
    }
}

let deferredPrompt;

window.addEventListener('beforeinstallprompt', (event) => {
    // - prevent Chrome 67 and earlier from automatically showing the prompt
    event.preventDefault();   
    deferredPrompt = event;
    // - pdate UI notify the user they can add to home screen
    document.getElementById('installButtonContainer').style.display = 'block'; // Show the install button
});

// - handle Install App process when button clicked
document.getElementById('installButton').addEventListener('click', () => {
    deferredPrompt.prompt();
    deferredPrompt.userChoice
        .then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('User accepted the A2HS prompt');
            } else {
                console.log('User dismissed the A2HS prompt');
            }
            deferredPrompt = null;
        });
});

// document.getElementById("notNowButton").addEventListener("click", function() {
//     alert("You can install the app later.");
// });
