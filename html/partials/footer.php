</main>
</div>


<footer class="main-footer">
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> Sistem Informasi Mahasiswa. All rights reserved.</p>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flash = document.getElementById('flash-message');
        if (flash) {
            setTimeout(() => {
                // Bootstrap 5 pakai class fade dan remove dari DOM
                flash.classList.remove('show');
                flash.classList.add('hide');

                flash.addEventListener('transitionend', () => {
                    if (flash.parentNode) {
                        flash.parentNode.removeChild(flash);
                    }
                });
            }, 3000);
        }
    });
</script>

<script src="/js/script.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const flashCard = document.getElementById('flash-card');
        if (flashCard) {
            const closeBtn = document.getElementById('flash-close-btn');

            // Fungsi hilangkan popup
            function hideFlash() {
                flashCard.style.transition = 'opacity 0.4s ease';
                flashCard.style.opacity = '0';
                setTimeout(() => {
                    if (flashCard.parentNode) {
                        flashCard.parentNode.removeChild(flashCard);
                    }
                }, 400);
            }

            // Auto hide setelah 4 detik
            setTimeout(hideFlash, 4000);

            // Close button click
            closeBtn.addEventListener('click', hideFlash);
        }
    });
</script>

</body>

</html>