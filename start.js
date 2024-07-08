window.addEventListener('load', function() {
    const loadingScreen = document.getElementById('loading');

    // Fade out the loading screen
    loadingScreen.style.transition = 'opacity 0.6s';
    loadingScreen.style.opacity = '0';

    // Once the transition is complete, redirect to register.html
    loadingScreen.addEventListener('transitionend', function() {
        window.location.href = 'register.html';
    });
});
