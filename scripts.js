const scrollContainer = document.querySelector('.scroll-container');
const panels = document.querySelectorAll('.panel');

// Set initial index
let currentIndex = 0;
let startX = 0;
let isDragging = false;

// Function to go to the specified panel
function goToPanel(index) {
    if (index < 0) {
        currentIndex = 0;
    } else if (index >= panels.length) {
        currentIndex = panels.length - 1;
    } else {
        currentIndex = index;
    }
    scrollContainer.scrollTo({
        left: currentIndex * window.innerWidth,
        behavior: 'smooth'
    });
}

// Handle swipe gestures
scrollContainer.addEventListener('touchstart', (event) => {
    startX = event.touches[0].clientX;
    isDragging = true;
});

scrollContainer.addEventListener('touchmove', (event) => {
    if (!isDragging) return;
    const moveX = event.touches[0].clientX;
    const diffX = startX - moveX;

    // If the difference in touch movement exceeds a threshold, go to the next or previous panel
    if (diffX > 50) {
        goToPanel(currentIndex + 1); // Swipe left (go to next panel)
        isDragging = false;
    } else if (diffX < -50) {
        goToPanel(currentIndex - 1); // Swipe right (go to previous panel)
        isDragging = false;
    }
});

scrollContainer.addEventListener('touchend', () => {
    isDragging = false;
});

// Optional: Add tap event to navigate to the next panel on tap (this can remain or be removed)
scrollContainer.addEventListener('click', () => {
    goToPanel(currentIndex + 1); // Go to next panel on click
});

// Optional: Add keyboard support for left/right arrow keys
window.addEventListener('keydown', (event) => {
    if (event.key === 'ArrowRight') {
        goToPanel(currentIndex + 1);
    } else if (event.key === 'ArrowLeft') {
        goToPanel(currentIndex - 1);
    }
});
