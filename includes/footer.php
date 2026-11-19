</main>
<footer class="py-4 text-center text-secondary small">© <?= date('Y') ?> E-Ceklist BNNK Batang</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm))e.preventDefault()}));
// Hilangkan fokus visual tombol hamburger setelah diklik agar outline tidak tertinggal.
document.querySelectorAll('.navbar-toggler').forEach(el=>el.addEventListener('click',()=>{window.setTimeout(()=>el.blur(),0)}));
</script>
</body></html>
