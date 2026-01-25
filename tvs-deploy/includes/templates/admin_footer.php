<?php
/**
 * TVS Admin Footer Template
 */
?>
    </main>

    <footer style="text-align: center; padding: 20px; color: #666; font-size: 12px;">
        &copy; <?= date('Y') ?> Tri-Valley Stargazers. Admin Panel v1.0
    </footer>

    <script>
        // Auto-hide alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    </script>
</body>
</html>
