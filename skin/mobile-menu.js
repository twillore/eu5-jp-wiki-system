document.addEventListener('DOMContentLoaded', function() {
    const menuButton = document.getElementById('menu-button');
    const menuBar = document.getElementById('menubar');
    const body = document.body;

    if (menuButton && menuBar) {
        menuButton.addEventListener('click', function() {
            body.classList.toggle('menu-open');
        });
    }
});