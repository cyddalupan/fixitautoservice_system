@extends('layouts.app')

@section('title', 'Dashboard Widget Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Dashboard Widget Management</h1>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" onclick="saveWidgetLayout()">
                        <i class="fas fa-save"></i> Save Layout
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="addNewWidget()">
                        <i class="fas fa-plus"></i> Add Widget
                    </button>
                </div>
            </div>
            <p class="text-muted">Customize your dashboard by adding, removing, and arranging widgets</p>
        </div>
    </div>

    <!-- Available Widgets -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Available Widgets</h6>
                </div>
                <div class="card-body">
                    <div class="row" id="availableWidgets">
                        @foreach($availableWidgets as $widget)
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card widget-card" data-widget-type="{{ $widget['type'] }}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $widget['name'] }}</h6>
                                            <p class="card-text small text-muted">{{ $widget['description'] }}</p>
                                        </div>
                                        <span class="badge badge-{{ $widget['badge_color'] }}">{{ $widget['category'] }}</span>
                                    </div>
                                    <div class="mt-3">
                                        <div class="small mb-2">
                                            <i class="fas fa-chart-{{ $widget['icon'] }} mr-1"></i>
                                            {{ $widget['data_type'] }}
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewWidget('{{ $widget['type'] }}')">
                                                <i class="fas fa-eye"></i> Preview
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" onclick="addWidgetToDashboard('{{ $widget['type'] }}')">
                                                <i class="fas fa-plus"></i> Add
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Layout Editor -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Dashboard Layout Editor</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="layoutMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="layoutMenu">
                            <a class="dropdown-item" href="#" onclick="loadDefaultLayout()">Load Default Layout</a>
                            <a class="dropdown-item" href="#" onclick="clearDashboard()">Clear Dashboard</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" onclick="exportLayout()">Export Layout</a>
                            <a class="dropdown-item" href="#" onclick="importLayout()">Import Layout</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Drag and drop widgets to rearrange. Click on widget settings to customize.
                    </div>
                    
                    <!-- Dashboard Grid -->
                    <div class="dashboard-grid" id="dashboardGrid">
                        @foreach($currentWidgets as $widget)
                        <div class="dashboard-widget" 
                             data-widget-id="{{ $widget['id'] }}"
                             data-col="{{ $widget['column_position'] }}"
                             data-row="{{ $widget['row_position'] }}"
                             data-width="{{ $widget['width'] }}"
                             data-height="{{ $widget['height'] }}">
                            <div class="widget-header">
                                <div class="widget-title">{{ $widget['title'] }}</div>
                                <div class="widget-actions">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="configureWidget('{{ $widget['id'] }}')" title="Configure">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeWidget('{{ $widget['id'] }}')" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="widget-body">
                                @if($widget['type'] === 'metric')
                                <div class="widget-metric">
                                    <div class="metric-value">${{ number_format($widget['value'], 2) }}</div>
                                    <div class="metric-label">{{ $widget['label'] }}</div>
                                </div>
                                @elseif($widget['type'] === 'chart')
                                <div class="widget-chart">
                                    <canvas id="widgetChart_{{ $widget['id'] }}" height="150"></canvas>
                                </div>
                                @elseif($widget['type'] === 'table')
                                <div class="widget-table">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($widget['data'] as $item)
                                            <tr>
                                                <td>{{ $item['label'] }}</td>
                                                <td class="font-weight-bold">{{ $item['value'] }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                            <div class="widget-footer">
                                <small class="text-muted">Last updated: {{ $widget['updated_at'] }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Widget Modal -->
<div class="modal fade" id="addWidgetModal" tabindex="-1" role="dialog" aria-labelledby="addWidgetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addWidgetModalLabel">Add New Widget</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addWidgetForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="widgetType">Widget Type</label>
                                <select class="form-control" id="widgetType" name="widget_type" required>
                                    <option value="">Select type...</option>
                                    @foreach($widgetTypes as $type)
                                    <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="widgetTitle">Widget Title</label>
                                <input type="text" class="form-control" id="widgetTitle" name="widget_title" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dataSource">Data Source</label>
                                <select class="form-control" id="dataSource" name="data_source" required>
                                    <option value="">Select data source...</option>
                                    @foreach($dataSources as $source)
                                    <option value="{{ $source['value'] }}">{{ $source['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="refreshInterval">Refresh Interval</label>
                                <select class="form-control" id="refreshInterval" name="refresh_interval">
                                    <option value="0">Manual</option>
                                    <option value="300">5 minutes</option>
                                    <option value="900" selected>15 minutes</option>
                                    <option value="1800">30 minutes</option>
                                    <option value="3600">1 hour</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="widgetWidth">Width (1-4 columns)</label>
                                <input type="number" class="form-control" id="widgetWidth" name="width" min="1" max="4" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="widgetHeight">Height (1-4 rows)</label>
                                <input type="number" class="form-control" id="widgetHeight" name="height" min="1" max="4" value="1" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="widgetColor">Color Theme</label>
                                <select class="form-control" id="widgetColor" name="color_theme">
                                    <option value="primary">Primary (Blue)</option>
                                    <option value="success">Success (Green)</option>
                                    <option value="warning">Warning (Yellow)</option>
                                    <option value="danger">Danger (Red)</option>
                                    <option value="info">Info (Cyan)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="widgetDescription">Description (Optional)</label>
                        <textarea class="form-control" id="widgetDescription" name="description" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewWidgetBtn">Add Widget</button>
            </div>
        </div>
    </div>
</div>

<!-- Configure Widget Modal -->
<div class="modal fade" id="configureWidgetModal" tabindex="-1" role="dialog" aria-labelledby="configureWidgetModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="configureWidgetModalLabel">Configure Widget</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="configureWidgetContent">
                <!-- Configuration will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveWidgetConfigBtn">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Widget Preview Modal -->
<div class="modal fade" id="widgetPreviewModal" tabindex="-1" role="dialog" aria-labelledby="widgetPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="widgetPreviewModalLabel">Widget Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="widgetPreviewContent">
                <!-- Preview will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="addPreviewedWidget()">Add to Dashboard</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        grid-gap: 20px;
        min-height: 600px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 2px dashed #dee2e6;
    }
    
    .dashboard-widget {
        grid-column: span 3;
        grid-row: span 1;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        cursor: move;
        transition: all 0.3s ease;
    }
    
    .dashboard-widget:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    .dashboard-widget.dragging {
        opacity: 0.5;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
    }
    
    .widget-header {
        padding: 12px 15px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .widget-title {
        font-weight: 600;
        font-size: 14px;
        color: #495057;
    }
    
    .widget-actions {
        display: flex;
        gap: 5px;
    }
    
    .widget-body {
        padding: 15px;
        flex: 1;
    }
    
    .widget-footer {
        padding: 10px 15px;
        background: #f8f9fa;
        border-top: 1px solid #dee2e6;
        font-size: 12px;
    }
    
    .widget-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .widget-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .widget-metric {
        text-align: center;
        padding: 20px 0;
    }
    
    .metric-value {
        font-size: 32px;
        font-weight: 700;
        color: #3498db;
        margin-bottom: 5px;
    }
    
    .metric-label {
        font-size: 14px;
        color: #6c757d;
    }
    
    .widget-chart {
        height: 150px;
    }
    
    .widget-table {
        max-height: 200px;
        overflow-y: auto;
    }
    
    /* Grid size classes */
    .widget-width-1 { grid-column: span 3; }
    .widget-width-2 { grid-column: span 6; }
    .widget-width-3 { grid-column: span 9; }
    .widget-width-4 { grid-column: span 12; }
    
    .widget-height-1 { grid-row: span 1; }
    .widget-height-2 { grid-row: span 2; }
    .widget-height-3 { grid-row: span 3; }
    .widget-height-4 { grid-row: span 4; }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/interactjs@1.10.11/dist/interact.min.js"></script>
<script>
    let currentWidgetId = null;
    let previewWidgetType = null;
    
    // Initialize drag and drop
    document.addEventListener('DOMContentLoaded', function() {
        initDragAndDrop();
        
        // Add Widget Button
        document.getElementById('saveNewWidgetBtn').addEventListener('click', function() {
            saveNewWidget();
        });
        
        // Save Widget Config Button
        document.getElementById('saveWidgetConfigBtn').addEventListener('click', function() {
            saveWidgetConfiguration();
        });
    });
    
    function initDragAndDrop() {
        const grid = document.getElementById('dashboardGrid');
        
        interact('.dashboard-widget').draggable({
            inertia: true,
            modifiers: [
                interact.modifiers.restrictRect({
                    restriction: 'parent',
                    endOnly: true
                })
            ],
            autoScroll: true,
            listeners: {
                start: function(event) {
                    event.target.classList.add('dragging');
                },
                move: function(event) {
                    const target = event.target;
                    const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                    const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                    
                    target.style.transform = `translate(${x}px, ${y}px)`;
                    target.setAttribute('data-x', x);
                    target.setAttribute('data-y', y);
                },
                end: function(event) {
                    event.target.classList.remove('dragging');
                    
                    // Calculate new grid position
                    const widget = event.target;
                    const gridRect = grid.getBoundingClientRect();
                    const widgetRect = widget.getBoundingClientRect();
                    
                    const colWidth = gridRect.width / 12;
                    const rowHeight = 100; // Approximate row height
                    
                    const col = Math.round((widgetRect.left - gridRect.left) / colWidth);
                    const row = Math.round((widgetRect.top - gridRect.top) / rowHeight);
                    
                    // Update widget position
                    widget.setAttribute('data-col', Math.max(0, col));
                    widget.setAttribute('data-row', Math.max(0, row));
                    
                    // Reset transform
                    widget.style.transform = 'none';
                    widget.setAttribute('data-x', 0);
                    widget.setAttribute('data-y', 0);
                    
                    // Update visual position
                    updateWidgetPosition(widget);
                }
            }
        });
        
        // Make grid a dropzone
        interact(grid).dropzone({
            accept: '.dashboard-widget',
            overlap: 0.75,
            listeners: {
                drop: function(event) {
                    // Widget dropped, position already updated in drag end
                }
            }
        });
    }
    
    function updateWidgetPosition(widget) {
        const col = parseInt(widget.getAttribute('data-col'));
        const row = parseInt(widget.getAttribute('data-row'));
        const width = parseInt(widget.getAttribute('data-width'));
        const height = parseInt(widget.getAttribute('data-height'));
        
        // Update grid position
        widget.style.gridColumn = `${col + 1} / span ${width}`;
        widget.style.gridRow = `${row + 1} / span ${height}`;
    }
    
    function addNewWidget() {
        $('#addWidgetModal').modal('show');
    }
    
    function saveNewWidget() {
        const form = document.getElementById('addWidgetForm');
        const formData = new FormData(form);
        
        fetch('{{ route("dashboard.widgets.create") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#addWidgetModal').modal('hide');
                form.reset();
                addWidgetToGrid(data.widget);
                showToast('Widget added successfully!', 'success');
            } else {
                showToast('Failed to add widget: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while adding the widget', 'error');
        });
    }
    
    function addWidgetToGrid(widgetData) {
        const grid = document.getElementById('dashboardGrid');
        const widgetHtml = createWidgetHtml(widgetData);
        
        const widgetElement = document.createElement('div');
        widgetElement.className = 'dashboard-widget';
        widgetElement.innerHTML = widgetHtml;
        widgetElement.setAttribute('data-widget-id', widgetData.id);
        widgetElement.setAttribute('data-col', widgetData.column_position);
        widgetElement.setAttribute('data-row', widgetData.row_position);
        widgetElement.setAttribute('data-width', widgetData.width);
        widgetElement.setAttribute('data-height', widgetData.height);
        
        grid.appendChild(widgetElement);
        updateWidgetPosition(widgetElement);
        initDragAndDrop(); // Reinitialize drag and drop for new widget
    }
    
    function createWidgetHtml(widgetData) {
        let bodyContent = '';
        
        switch(widgetData.widget_type) {
            case 'metric':
                bodyContent = `
                    <div class="widget-metric">
                        <div class="metric-value">Loading...</div>
                        <div class="metric-label">${widgetData.widget_title}</div>
                    </div>
                `;
                break;
            case 'chart':
                bodyContent = `
                    <div class="widget-chart">
                        <canvas id="widgetChart_${widgetData.id}" height="150"></canvas>
                    </div>
                `;
                break;
            case 'table':
                bodyContent = `
                    <div class="widget-table">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Loading...</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Loading data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                break;
            default:
                bodyContent = `
                    <div class="alert alert-warning">
                        Unknown widget type
                    </div>
                `;
        }
        
        return `
            <div class="widget-header">
                <div class="widget-title">${widgetData.widget_title}</div>
                <div class="widget-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="configureWidget('${widgetData.id}')" title="Configure">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeWidget('${widgetData.id}')" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="widget-body">
                ${bodyContent}
            </div>
            <div class="widget-footer">
                <small class="text-muted">Just added</small>
            </div>
        `;
    }
    
    function configureWidget(widgetId) {
        currentWidgetId = widgetId;
        
        fetch(`/dashboard/widgets/${widgetId}/configuration`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalContent = document.getElementById('configureWidgetContent');
                modalContent.innerHTML = createConfigurationForm(data.widget);
                $('#configureWidgetModal').modal('show');
            } else {
                showToast('Failed to load widget configuration', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load widget configuration', 'error');
        });
    }
    
    function createConfigurationForm(widget) {
        return `
            <form id="widgetConfigForm">
                <div class="form-group">
                    <label for="configTitle">Widget Title</label>
                    <input type="text" class="form-control" id="configTitle" name="widget_title" value="${widget.widget_title}" required>
                </div>
                
                <div class="form-group">
                    <label for="configWidth">Width (1-4 columns)</label>
                    <input type="number" class="form-control" id="configWidth" name="width" min="1" max="4" value="${widget.width}" required>
                </div>
                
                <div class="form-group">
                    <label for="configHeight">Height (1-4 rows)</label>
                    <input type="number" class="form-control" id="configHeight" name="height" min="1" max="4" value="${widget.height}" required>
                </div>
                
                <div class="form-group">
                    <label for="configRefresh">Refresh Interval</label>
                    <select class="form-control" id="configRefresh" name="refresh_interval">
                        <option value="0" ${widget.refresh_interval == 0 ? 'selected' : ''}>Manual</option>
                        <option value="300" ${widget.refresh_interval == 300 ? 'selected' : ''}>5 minutes</option>
                        <option value="900" ${widget.refresh_interval == 900 ? 'selected' : ''}>15 minutes</option>
                        <option value="1800" ${widget.refresh_interval == 1800 ? 'selected' : ''}>30 minutes</option>
                        <option value="3600" ${widget.refresh_interval == 3600 ? 'selected' : ''}>1 hour</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="configColor">Color Theme</label>
                    <select class="form-control" id="configColor" name="color_theme">
                        <option value="primary" ${widget.color_theme == 'primary' ? 'selected' : ''}>Primary (Blue)</option>
                        <option value="success" ${widget.color_theme == 'success' ? 'selected' : ''}>Success (Green)</option>
                        <option value="warning" ${widget.color_theme == 'warning' ? 'selected' : ''}>Warning (Yellow)</option>
                        <option value="danger" ${widget.color_theme == 'danger' ? 'selected' : ''}>Danger (Red)</option>
                        <option value="info" ${widget.color_theme == 'info' ? 'selected' : ''}>Info (Cyan)</option>
                    </select>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="configVisible" name="is_visible" ${widget.is_visible ? 'checked' : ''}>
                    <label class="form-check-label" for="configVisible">Visible on dashboard</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="configCollapsed" name="is_collapsed" ${widget.is_collapsed ? 'checked' : ''}>
                    <label class="form-check-label" for="configCollapsed">Initially collapsed</label>
                </div>
            </form>
        `;
    }
    
    function saveWidgetConfiguration() {
        const form = document.getElementById('widgetConfigForm');
        const formData = new FormData(form);
        
        fetch(`/dashboard/widgets/${currentWidgetId}/update`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#configureWidgetModal').modal('hide');
                updateWidgetInGrid(data.widget);
                showToast('Widget configuration saved!', 'success');
            } else {
                showToast('Failed to save configuration: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to save configuration', 'error');
        });
    }
    
    function updateWidgetInGrid(widgetData) {
        const widgetElement = document.querySelector(`[data-widget-id="${widgetData.id}"]`);
        if (widgetElement) {
            // Update title
            const titleElement = widgetElement.querySelector('.widget-title');
            if (titleElement) {
                titleElement.textContent = widgetData.widget_title;
            }
            
            // Update size
            widgetElement.setAttribute('data-width', widgetData.width);
            widgetElement.setAttribute('data-height', widgetData.height);
            updateWidgetPosition(widgetElement);
        }
    }
    
    function removeWidget(widgetId) {
        if (confirm('Are you sure you want to remove this widget?')) {
            fetch(`/dashboard/widgets/${widgetId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const widgetElement = document.querySelector(`[data-widget-id="${widgetId}"]`);
                    if (widgetElement) {
                        widgetElement.remove();
                    }
                    showToast('Widget removed successfully', 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to remove widget', 'error');
            });
        }
    }
    
    function previewWidget(widgetType) {
        previewWidgetType = widgetType;
        
        fetch(`/dashboard/widgets/preview/${widgetType}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const previewContent = document.getElementById('widgetPreviewContent');
                previewContent.innerHTML = createPreviewHtml(data.preview);
                $('#widgetPreviewModal').modal('show');
            } else {
                showToast('Failed to load widget preview', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to load widget preview', 'error');
        });
    }
    
    function createPreviewHtml(previewData) {
        return `
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">${previewData.name}</h6>
                </div>
                <div class="card-body">
                    <p class="card-text">${previewData.description}</p>
                    
                    <div class="mb-3">
                        <strong>Data Source:</strong> ${previewData.data_source}<br>
                        <strong>Update Frequency:</strong> ${previewData.update_frequency}<br>
                        <strong>Size:</strong> ${previewData.width} column(s) × ${previewData.height} row(s)
                    </div>
                    
                    <div class="preview-container" style="height: 200px; background: #f8f9fa; border-radius: 4px; padding: 20px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center">
                            <i class="fas fa-${previewData.icon} fa-3x text-primary mb-3"></i>
                            <p class="mb-0">Preview of ${previewData.name} widget</p>
                            <small class="text-muted">Actual data will be loaded when added to dashboard</small>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    function addPreviewedWidget() {
        if (previewWidgetType) {
            addWidgetToDashboard(previewWidgetType);
            $('#widgetPreviewModal').modal('hide');
        }
    }
    
    function addWidgetToDashboard(widgetType) {
        const widgetData = {
            widget_type: widgetType,
            widget_title: widgetType.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            width: 1,
            height: 1,
            column_position: 0,
            row_position: 0,
            color_theme: 'primary',
            refresh_interval: 900
        };
        
        fetch('{{ route("dashboard.widgets.create") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(widgetData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addWidgetToGrid(data.widget);
                showToast('Widget added to dashboard!', 'success');
            } else {
                showToast('Failed to add widget: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while adding the widget', 'error');
        });
    }
    
    function saveWidgetLayout() {
        const widgets = [];
        const widgetElements = document.querySelectorAll('.dashboard-widget');
        
        widgetElements.forEach((widget, index) => {
            widgets.push({
                id: widget.getAttribute('data-widget-id'),
                column_position: parseInt(widget.getAttribute('data-col')),
                row_position: parseInt(widget.getAttribute('data-row')),
                width: parseInt(widget.getAttribute('data-width')),
                height: parseInt(widget.getAttribute('data-height'))
            });
        });
        
        fetch('{{ route("dashboard.layout.save") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ widgets: widgets })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Dashboard layout saved successfully!', 'success');
            } else {
                showToast('Failed to save layout: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to save layout', 'error');
        });
    }
    
    function loadDefaultLayout() {
        if (confirm('Load default layout? This will replace your current layout.')) {
            fetch('{{ route("dashboard.layout.default") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast('Failed to load default layout', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to load default layout', 'error');
            });
        }
    }
    
    function clearDashboard() {
        if (confirm('Clear the entire dashboard? This will remove all widgets.')) {
            fetch('{{ route("dashboard.clear") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showToast('Failed to clear dashboard', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Failed to clear dashboard', 'error');
            });
        }
    }
    
    function exportLayout() {
        fetch('{{ route("dashboard.layout.export") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'dashboard_layout.json';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to export layout', 'error');
        });
    }
    
    function importLayout() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '.json';
        
        input.onchange = function(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const layoutData = JSON.parse(e.target.result);
                    
                    fetch('{{ route("dashboard.layout.import") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(layoutData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            showToast('Failed to import layout: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Failed to import layout', 'error');
                    });
                } catch (error) {
                    showToast('Invalid layout file', 'error');
                }
            };
            reader.readAsText(file);
        };
        
        input.click();
    }
    
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        const toastContainer = document.getElementById('toastContainer') || (() => {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            document.body.appendChild(container);
            return container;
        })();
        
        toastContainer.appendChild(toast);
        
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', function () {
            toast.remove();
        });
    }
</script>
@endsection
