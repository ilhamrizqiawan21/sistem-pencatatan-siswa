</main><!-- /.page-content -->

    <!-- FOOTER -->
    <footer class="app-footer">
        <span>&copy; <?= date('Y') ?> MTs Al-Ihsan &mdash; Sistem Pencatatan Siswa</span>
        <span class="footer-version">v2.0</span>
    </footer>

</div><!-- /.main-wrapper -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/script.js"></script>
<script>
(function() {
    var token = '<?php echo htmlspecialchars(generate_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>';
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('form[method="POST"]').forEach(function(form) {
            if (!form.querySelector('input[name="csrf_token"]')) {
                var hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'csrf_token';
                hidden.value = token;
                form.appendChild(hidden);
            }
        });
    });
})();
</script>

<?php if (($pageTitle ?? '') === 'Dashboard'): ?>
<script src="<?= BASE_URL ?>assets/js/chart.js"></script>
<?php endif; ?>

</body>
</html>