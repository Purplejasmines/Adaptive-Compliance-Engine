document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (menuToggle && sidebar && mainContent) {
        menuToggle.addEventListener('click', () => {
            if(window.innerWidth > 768){
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            }else{
        sidebar.classList.toggle('active');
            }
        });
    }
    
    // Placeholder for other interactive features like button clicks.
    document.querySelectorAll('.btn').forEach(button => {
        button.addEventListener('click', (e) => {
            console.log(`${e.target.textContent} button clicked!`);
        });
    });
});
