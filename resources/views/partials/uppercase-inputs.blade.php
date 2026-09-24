{{--
    Auto-uppercase fill-up / fill-in fields, app-wide.
    As you type (or paste) into a text input / textarea, the value is stored in
    ALL CAPS so it looks the same on screen, in the DB and on printed documents.

    Opt a field out with class "no-uppercase" or attribute data-uppercase="off".
    Skipped automatically: email, password, url, search, tel, number, date/time,
    file, hidden, color, range, and anything whose name looks like email/password/
    url/search/csrf.
--}}
<script>
(function () {
    var SKIP_TYPES = ['email','password','url','search','tel','number','date','time',
                      'datetime-local','month','week','file','hidden','color','range',
                      // checkbox/radio carry fixed machine values (keys, codes) — never uppercase them
                      'checkbox','radio'];
    var SKIP_NAME  = /email|password|url|search|_token|csrf/i;

    function shouldSkip(el) {
        if (!el || el.disabled || el.readOnly) { return true; }
        if (el.classList && el.classList.contains('no-uppercase')) { return true; }
        if (el.dataset && el.dataset.uppercase === 'off') { return true; }
        if (el.tagName === 'INPUT') {
            var t = (el.getAttribute('type') || 'text').toLowerCase();
            if (SKIP_TYPES.indexOf(t) !== -1) { return true; }
        }
        if (SKIP_NAME.test(el.getAttribute('name') || '')) { return true; }
        return false;
    }

    function upper(el) {
        var up = el.value.toUpperCase();
        if (up === el.value) { return; }
        var s = el.selectionStart, e = el.selectionEnd;
        el.value = up;
        if (s !== null && s !== undefined) {
            try { el.setSelectionRange(s, e); } catch (err) {}
        }
    }

    function handle(ev) {
        var el = ev.target;
        if (!el || (el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA')) { return; }
        if (shouldSkip(el)) { return; }
        upper(el);
    }

    // capture=true so we uppercase before any page-level handlers read the value
    document.addEventListener('input', handle, true);
    document.addEventListener('change', handle, true);
})();
</script>
