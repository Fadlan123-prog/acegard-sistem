<script>
(function () {
    const checkAll        = document.getElementById('checkAll');
    const checkboxes      = document.querySelectorAll('.check-item');
    const bulkForm        = document.getElementById('bulkDeleteForm');
    const selectedIdsInput = document.getElementById('selected_ids');

    if (!checkAll || !bulkForm || !selectedIdsInput) return;

    function syncBulkButton() {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        bulkForm.classList.toggle('d-none', selected.length === 0);
        selectedIdsInput.value = selected.join(',');
    }

    checkAll.addEventListener('change', function () {
        checkboxes.forEach(cb => (cb.checked = this.checked));
        syncBulkButton();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', syncBulkButton));
})();
</script>
