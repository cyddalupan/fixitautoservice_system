<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parts Procurement System Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background-color: #f8f9fa; }
        .test-section { margin-bottom: 30px; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .status-badge { font-size: 0.8em; padding: 5px 10px; border-radius: 20px; }
        .status-success { background-color: #d4edda; color: #155724; }
        .status-warning { background-color: #fff3cd; color: #856404; }
        .status-error { background-color: #f8d7da; color: #721c24; }
        .test-result { margin-top: 10px; padding: 10px; border-radius: 5px; }
        .success { background-color: #d4edda; border: 1px solid #c3e6cb; }
        .error { background-color: #f8d7da; border: 1px solid #f5c6cb; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">🔧 Parts Procurement System Test</h1>
        <p class="lead mb-4">Testing Feature 8: Parts Procurement System</p>

        <div class="test-section">
            <h3>📊 Database Tables</h3>
            <div id="database-results">
                <p>Checking database tables...</p>
            </div>
        </div>

        <div class="test-section">
            <h3>🧠 Eloquent Models</h3>
            <div id="model-results">
                <p>Checking Eloquent models...</p>
            </div>
        </div>

        <div class="test-section">
            <h3>🎮 Controllers</h3>
            <div id="controller-results">
                <p>Checking controllers...</p>
            </div>
        </div>

        <div class="test-section">
            <h3>🛣️ Routes</h3>
            <div id="route-results">
                <p>Checking routes...</p>
            </div>
        </div>

        <div class="test-section">
            <h3>👁️ Views</h3>
            <div id="view-results">
                <p>Checking views...</p>
            </div>
        </div>

        <div class="test-section">
            <h3>🔗 Integration</h3>
            <div id="integration-results">
                <p>Checking system integration...</p>
            </div>
        </div>

        <div class="test-section">
            <h3>📋 Feature 8 Requirements</h3>
            <div id="requirements-results">
                <p>Checking Feature 8 requirements...</p>
            </div>
        </div>

        <div class="test-section" id="summary-section" style="display: none;">
            <h3>📈 Summary</h3>
            <div id="summary-results"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Test database tables
            fetch('/api/test/parts-procurement/database')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.tables.forEach(table => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${table.name}</span>
                            <span class="status-badge ${table.exists ? 'status-success' : 'status-error'}">
                                ${table.exists ? '✓ EXISTS' : '✗ MISSING'}
                            </span>
                        </div>`;
                    });
                    document.getElementById('database-results').innerHTML = html;
                });

            // Test models
            fetch('/api/test/parts-procurement/models')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.models.forEach(model => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${model.name}</span>
                            <span class="status-badge ${model.exists ? 'status-success' : 'status-error'}">
                                ${model.exists ? '✓ LOADED' : '✗ MISSING'}
                            </span>
                        </div>`;
                    });
                    document.getElementById('model-results').innerHTML = html;
                });

            // Test controllers
            fetch('/api/test/parts-procurement/controllers')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.controllers.forEach(controller => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${controller.name}</span>
                            <span class="status-badge ${controller.exists ? 'status-success' : 'status-error'}">
                                ${controller.exists ? '✓ EXISTS' : '✗ MISSING'}
                            </span>
                        </div>`;
                    });
                    document.getElementById('controller-results').innerHTML = html;
                });

            // Test routes
            fetch('/api/test/parts-procurement/routes')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.routes.forEach(route => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${route.name}</span>
                            <span class="status-badge ${route.exists ? 'status-success' : 'status-warning'}">
                                ${route.exists ? '✓ CONFIGURED' : '⚠️ CHECK'}
                            </span>
                        </div>`;
                    });
                    document.getElementById('route-results').innerHTML = html;
                });

            // Test views
            fetch('/api/test/parts-procurement/views')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.views.forEach(view => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${view.name}</span>
                            <span class="status-badge ${view.exists ? 'status-success' : 'status-warning'}">
                                ${view.exists ? '✓ EXISTS' : '⚠️ MISSING'}
                            </span>
                        </div>`;
                    });
                    document.getElementById('view-results').innerHTML = html;
                });

            // Test integration
            fetch('/api/test/parts-procurement/integration')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.integrations.forEach(integration => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${integration.name}</span>
                            <span class="status-badge ${integration.connected ? 'status-success' : 'status-warning'}">
                                ${integration.connected ? '✓ CONNECTED' : '⚠️ CHECK'}
                            </span>
                        </div>`;
                    });
                    document.getElementById('integration-results').innerHTML = html;
                });

            // Test requirements
            fetch('/api/test/parts-procurement/requirements')
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    data.requirements.forEach(req => {
                        html += `<div class="d-flex justify-content-between align-items-center mb-2">
                            <span>${req.name}</span>
                            <span class="status-badge ${req.implemented ? 'status-success' : 'status-warning'}">
                                ${req.implemented ? '✓ IMPLEMENTED' : '⚠️ PARTIAL'}
                            </span>
                        </div>`;
                        if (req.notes) {
                            html += `<div class="test-result ${req.implemented ? 'success' : 'warning'}">
                                <small>${req.notes}</small>
                            </div>`;
                        }
                    });
                    document.getElementById('requirements-results').innerHTML = html;
                });

            // Get summary
            fetch('/api/test/parts-procurement/summary')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('summary-section').style.display = 'block';
                    let html = `<div class="alert alert-${data.overall_status === 'complete' ? 'success' : data.overall_status === 'partial' ? 'warning' : 'danger'}">
                        <h5>Overall Status: ${data.overall_status.toUpperCase()}</h5>
                        <p>${data.message}</p>
                        <hr>
                        <p><strong>Progress:</strong> ${data.progress_percentage}% complete</p>
                        <p><strong>Components Ready:</strong> ${data.components_ready}/${data.total_components}</p>
                        <p><strong>Next Steps:</strong> ${data.next_steps}</p>
                    </div>`;
                    document.getElementById('summary-results').innerHTML = html;
                });
        });
    </script>
</body>
</html>