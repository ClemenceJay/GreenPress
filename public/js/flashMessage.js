// fonction de fermeture des flash messages
function closeFlashCard(button) {
    const flashCard = button.closest('.flash-card');
    flashCard.style.opacity = '0';
    flashCard.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        flashCard.remove();
    }, 300);
}