<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix-It Auto Services - Time Tracking Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #667eea;
        }
        .header h1 {
            color: #764ba2;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.2em;
        }
        .test-section {
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .test-section h2 {
            color: #667eea;
            margin-top: 0;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd8;
            transform: translateY(-2px);
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        .result.success {
            border-left-color: #28a745;
            background: #d4edda;
            color: #155724;
        }
        .result.error {
            border-left-color: #dc3545;
            background: #f8d7da;
            color: #721c24;
        }
        .result.info {
            border-left-color: #17a2b8;
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-indicator {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-clocked-in {
            background: #d4edda;
            color: #155724;
        }
        .status-clocked-out {
            background: #f8d7da;
            color: #721c24;
        }
        .log-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .log-table th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .log-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
        .log-table tr:hover {
            background: #f8f9fa;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.8em;
            font-weight: bold;
        }
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .summary-card h3 {
            margin-top: 0;
            color: white;
        }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 5px;
        }
        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 0.9em;
            opacity: 0.9;
        }
        .timestamp {
            text-align: center;
            margin-top: 30px;
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🕒 Fix-It Auto Services - Time Tracking Test</h1>
            <p>Technician Productivity Tools - Time Tracking System</p>
        </div>

        <div class="test-section">
            <h2>📊 System Status</h2>
            <div class="summary-card">
                <h3>Time Tracking System Status</h3>
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="total-technicians">0</div>
                        <div class="stat-label">Technicians</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="total-time-logs">0</div>
                        <div class="stat-label">Time Logs</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="pending-logs">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="approved-logs">0</div>
                        <div class="stat-label">Approved</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h2>👨‍🔧 Technician Selection</h2>
            <div class="form-group">
                <label for="technician-select">Select Technician:</label>
                <select id="technician-select" class="technician-select">
                    <option value="">Loading technicians...</option>
                </select>
            </div>
            <div id="technician-status" class="result info">
                Select a technician to view status
            </div>
            <div class="button-group">
                <button class="btn btn-primary" onclick="getTechnicianStatus()">🔄 Refresh Status</button>
                <button class="btn btn-success" onclick="clockIn()" id="clock-in-btn">⏰ Clock In</button>
                <button class="btn btn-danger" onclick="clockOut()" id="clock-out-btn" disabled>🚪 Clock Out</button>
                <button class="btn btn-warning" onclick="getHoursWorked()">📊 Get Hours</button>
            </div>
        </div>

        <div class="test-section">
            <h2>📝 Create Time Log</h2>
            <div class="form-group">
                <label for="log-type">Log Type:</label>
                <select id="log-type">
                    <option value="clock_in">Clock In</option>
                    <option value="clock_out">Clock Out</option>
                    <option value="break_start">Break Start</option>
                    <option value="break_end">Break End</option>
                    <option value="lunch_start">Lunch Start</option>
                    <option value="lunch_end">Lunch End</option>
                    <option value="job_start">Job Start</option>
                    <option value="job_end">Job End</option>
                </select>
            </div>
            <div class="form-group">
                <label for="log-notes">Notes (Optional):</label>
                <textarea id="log-notes" rows="3" placeholder="Enter any notes..."></textarea>
            </div>
            <div class="button-group">
                <button class="btn btn-primary" onclick="createTimeLog()">➕ Create Time Log</button>
            </div>
            <div id="create-log-result" class="result"></div>
        </div>

        <div class="test-section">
            <h2>📋 Recent Time Logs</h2>
            <div class="button-group">
                <button class="btn btn-primary" onclick="loadTimeLogs()">🔄 Refresh Logs</button>
                <button class="btn btn-success" onclick="approveAllPending()">✅ Approve All Pending</button>
            </div>
            <div id="time-logs-container">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Technician</th>
                            <th>Type</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="time-logs-body">
                        <tr>
                            <td colspan="6" style="text-align: center;">Loading time logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="test-section">
            <h2>✅ Test Results</h2>
            <div id="test-results">
                <div class="result info">
                    <strong>Test Status:</strong> Ready to test
                </div>
            </div>
            <div class="button-group">
                <button class="btn btn-success" onclick="runAllTests()">🚀 Run All Tests</button>
                <button class="btn btn-primary" onclick="clearTestResults()">🗑️ Clear Results</button>
            </div>
        </div>

        <div class="timestamp">
            Last Updated: <span id="current-time">Loading...</span><br>
            System Status: <span id="system-status">✅ OPERATIONAL</span>
        </div>
    </div>

    <script>
        // Global variables
        let currentTechnicianId = null;
        let currentTechnicianStatus = null;

        // Update timestamp
        function updateTimestamp() {
            const now = new Date();
            document.getElementById('current-time').textContent = 
                now.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    timeZone: 'UTC'
                }) + ' UTC';
        }
        updateTimestamp();
        setInterval(updateTimestamp, 1000);

        // Load technicians
        async function loadTechnicians() {
            try {
                const response = await fetch('/api/users?role=technician&is_active=true');
                const data = await response.json();
                
                const select = document.getElementById('technician-select');
                select.innerHTML = '<option value="">Select a technician...</option>';
                
                data.data.forEach(technician => {
                    const option = document.createElement('option');
                    option.value = technician.id;
                    option.textContent = `${technician.name} (${technician.employee_id || 'No ID'})`;
                    select.appendChild(option);
                });
                
                // Load system stats
                loadSystemStats();
                
            } catch (error) {
                console.error('Error loading technicians:', error);
                document.getElementById('technician-select').innerHTML = 
                    '<option value="">Error loading technicians</option>';
            }
        }

        // Load system statistics
        async function loadSystemStats() {
            try {
                // Get technician count
                const techResponse = await fetch('/api/users?role=technician&is_active=true');
                const techData = await techResponse.json();
                document.getElementById('total-technicians').textContent = techData.data.length;
                
                // Get time logs count (simplified - would need API endpoint)
                // For now, we'll use placeholder
                document.getElementById('total-time-logs').textContent = 'N/A';
                document.getElementById('pending-logs').textContent = 'N/A';
                document.getElementById('approved-logs').textContent = 'N/A';
                
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }

        // Get technician status
        async function getTechnicianStatus() {
            const technicianId = document.getElementById('technician-select').value;
            if (!technicianId) {
                showResult('technician-status', 'Please select a technician first.', 'error');
                return;
            }
            
            currentTechnicianId = technicianId;
            
            try {
                const response = await fetch(`/time-tracking/status/${technicianId}`);
                const data = await response.json();
                
                if (data.success) {
                    currentTechnicianStatus = data.data.status;
                    
                    let statusHtml = `<strong>Status:</strong> `;
                    if (data.data.status === 'clocked_in') {
                        statusHtml += `<span class="status-indicator status-clocked-in">CLOCKED IN</span>`;
                        document.getElementById('clock-in-btn').disabled = true;
                        document.getElementById('clock-out-btn').disabled = false;
                    } else {
                        statusHtml += `<span class="status-indicator status-clocked-out">CLOCKED OUT</span>`;
                        document.getElementById('clock-in-btn').disabled = false;
                        document.getElementById('clock-out-btn').disabled = true;
                    }
                    
                    if (data.data.last_clock_in) {
                        const lastClockIn = new Date(data.data.last_clock_in.log_time);
                        statusHtml += `<br><strong>Last Clock In:</strong> ${lastClockIn.toLocaleString()}`;
                    }
                    
                    statusHtml += `<br><strong>Current Time:</strong> ${data.data.current_time}`;
                    
                    showResult('technician-status', statusHtml, 'success');
                    
                    // Load time logs for this technician
                    loadTimeLogs();
                    
                } else {
                    showResult('technician-status', 'Error getting status: ' + data.message, 'error');
                }
            } catch (error) {
                showResult('technician-status', 'Error: ' + error.message, 'error');
            }
        }

        // Clock in
        async function clockIn() {
            if (!currentTechnicianId) {
                showResult('technician-status', 'Please select a technician first.', 'error');
                return;
            }
            
            try {
                const response = await fetch('/time-tracking/clock-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        technician_id: currentTechnicianId,
                        notes: 'Clocked in via test interface'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showResult('technician-status', `✅ Clocked in successfully at ${new Date().toLocaleTimeString()}`, 'success');
                    document.getElementById('clock-in-btn').disabled = true;
                    document.getElementById('clock-out-btn').disabled = false;
                    currentTechnicianStatus = 'clocked_in';
                    
                    // Refresh logs
                    loadTimeLogs();
                    
                    // Add to test results
                    addTestResult('Clock In Test', '✅ PASSED', 'Clock in functionality works correctly');
                } else {
                    showResult('technician-status', '