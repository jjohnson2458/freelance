    </div><!-- /.main-content -->
</div><!-- /#app-wrapper -->

<!-- Processing Overlay -->
<div id="processing-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center;">
    <div class="bg-white rounded-3 shadow-lg p-4 text-center" style="max-width:360px; width:90%;">
        <div class="spinner-border text-primary mb-3" style="width:3rem; height:3rem;" role="status">
            <span class="visually-hidden">Processing...</span>
        </div>
        <h5 class="mb-2" id="processing-title">Generating Proposal...</h5>
        <p class="text-muted mb-0 small" id="processing-message">This may take 15-30 seconds. Please don't close or refresh the page.</p>
    </div>
</div>

<script src="/js/bootstrap.bundle.min.js"></script>
<script src="/js/app.js"></script>
<script>
document.querySelectorAll('form[data-processing]').forEach(function(form) {
    form.addEventListener('submit', function() {
        var overlay = document.getElementById('processing-overlay');
        var title = form.getAttribute('data-processing-title');
        var msg = form.getAttribute('data-processing-message');
        if (title) document.getElementById('processing-title').textContent = title;
        if (msg) document.getElementById('processing-message').textContent = msg;
        overlay.style.display = 'flex';
    });
});
</script>
</body>
</html>
