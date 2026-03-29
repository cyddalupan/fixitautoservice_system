@section('scripts')
<script>
    $(document).ready(function() {
        console.log('=== CUSTOMER PROFILE PAGE - SIMPLE TEST ===');
        
        // Basic checks
        console.log('1. Checking jQuery:', typeof jQuery !== 'undefined' ? '✓ Loaded v' + $.fn.jquery : '✗ NOT LOADED');
        console.log('2. Checking Bootstrap:', typeof bootstrap !== 'undefined' ? '✓ Loaded' : '✗ NOT LOADED');
        
        // Find the upload button
        const uploadBtn = $('#uploadProfilePictureBtn');
        console.log('3. Upload button:', uploadBtn.length > 0 ? '✓ Found (' + uploadBtn.length + ')' : '✗ NOT FOUND');
        
        if (uploadBtn.length > 0) {
            console.log('   Button HTML:', uploadBtn[0].outerHTML);
            console.log('   Button text:', uploadBtn.text());
            console.log('   Button classes:', uploadBtn.attr('class'));
        }
        
        // Remove any existing handlers
        uploadBtn.off('click');
        
        // Add simple test handler
        uploadBtn.on('click', function(e) {
            console.log('=== BUTTON CLICKED! ===');
            console.log('Event:', e.type);
            console.log('Button:', this);
            
            // Show alert to confirm it's working
            alert('SUCCESS! Button click is working.\n\nIf you see this, the button IS clickable.\n\nThe issue might be:\n1. File not selected\n2. JavaScript errors in other code\n3. Modal not properly initialized');
            
            // Prevent any form submission
            e.preventDefault();
            e.stopPropagation();
            return false;
        });
        
        console.log('4. Test handler attached: ✓ READY');
        console.log('=== INSTRUCTIONS ===');
        console.log('1. Open upload modal (click camera icon)');
        console.log('2. Select a file');
        console.log('3. Click "Upload Picture" button');
        console.log('4. Check if alert appears');
    });
</script>
@endsection