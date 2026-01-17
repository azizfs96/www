@extends('layouts.waf')

@section('title', 'WAF Policy - ' . $site->name)

@section('styles')
<style>
    :root {
        --bg-dark: #1A1A1A;
        --bg-card: #1E1E1E;
        --bg-hover: #2A2A2A;
        --border: #333333;
        --border-light: #404040;
        --text-primary: #E5E5E5;
        --text-secondary: #B3B3B3;
        --text-muted: #808080;
        --primary: #9D4EDD;
        --primary-hover: #B06FE8;
        --success: #4ADE80;
        --error: #F87171;
        --warning: #FBBF24;
    }

    .page-header {
        margin-bottom: 32px;
    }

    .page-title {
        font-size: 32px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .site-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: rgba(157, 78, 221, 0.15);
        border: 1px solid rgba(157, 78, 221, 0.3);
        border-radius: 6px;
        font-size: 13px;
        color: var(--primary);
        margin-top: 8px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        margin-top: 12px;
        transition: color 0.2s;
    }

    .back-link:hover {
        color: var(--primary);
    }

    .card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .form-help {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 6px;
        line-height: 1.5;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 10px 14px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text-primary);
        font-size: 14px;
        transition: all 0.2s;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary);
        background: var(--bg-hover);
        box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 12px;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px;
        background: var(--bg-dark);
        border: 1px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .checkbox-item:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .checkbox-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary);
    }

    .checkbox-label {
        font-size: 14px;
        color: var(--text-primary);
        cursor: pointer;
        flex: 1;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-primary {
        background: var(--primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: var(--bg-dark);
        color: var(--text-primary);
        border: 1px solid var(--border);
    }

    .alert-success {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 24px;
        background: rgba(74, 222, 128, 0.1);
        border: 1px solid rgba(74, 222, 128, 0.3);
        color: var(--success);
        font-size: 14px;
    }

    .paranoia-levels {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 8px;
    }

    .paranoia-level {
        position: relative;
    }

    .paranoia-level input[type="radio"] {
        position: absolute;
        opacity: 0;
    }

    .paranoia-level label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px;
        background: var(--bg-dark);
        border: 2px solid var(--border);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }

    .paranoia-level input[type="radio"]:checked + label {
        border-color: var(--primary);
        background: rgba(157, 78, 221, 0.1);
    }

    .paranoia-level label:hover {
        border-color: var(--border-light);
        background: var(--bg-hover);
    }

    .level-number {
        font-size: 24px;
        font-weight: 600;
        color: var(--text-primary);
    }

    .level-name {
        font-size: 11px;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="direction: ltr; text-align: left;">
    <h1 class="page-title">⚙️ WAF Policy Settings</h1>
    <div class="site-badge">
        🌐 {{ $site->name }} ({{ $site->server_name }})
    </div>
    <a href="{{ route('sites.index') }}" class="back-link">← Back to Sites</a>
</div>

@if(session('status'))
    <div class="alert-success" style="direction: ltr; text-align: left;">
        ✓ {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('sites.policy.update', $site) }}">
    @csrf
    @method('PUT')

    {{-- General Settings --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">🛡️ General Settings</h2>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="waf_enabled" id="waf_enabled" 
                       {{ $policy->waf_enabled ? 'checked' : '' }}>
                <label for="waf_enabled" class="checkbox-label" style="direction: ltr; text-align: left;">
                    <strong>Enable WAF</strong> - Enable/disable WAF for this site
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="inherit_global_rules" id="inherit_global_rules" 
                       {{ $policy->inherit_global_rules ? 'checked' : '' }}>
                <label for="inherit_global_rules" class="checkbox-label" style="direction: ltr; text-align: left;">
                    <strong>Inherit Global Rules</strong> - Apply global rules + site-specific rules
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Paranoia Level</label>
            <div class="paranoia-levels">
                @for($i = 1; $i <= 4; $i++)
                    <div class="paranoia-level">
                        <input type="radio" name="paranoia_level" id="level{{ $i }}" value="{{ $i }}" 
                               {{ $policy->paranoia_level == $i ? 'checked' : '' }}>
                        <label for="level{{ $i }}">
                            <div class="level-number">{{ $i }}</div>
                            <div class="level-name">
                                @if($i == 1) Low
                                @elseif($i == 2) Medium
                                @elseif($i == 3) High
                                @else Critical
                                @endif
                            </div>
                        </label>
                    </div>
                @endfor
            </div>
            <div class="form-help" style="direction: ltr; text-align: left;">
                Level 1 = Basic protection (recommended to start)<br>
                Level 4 = Maximum protection (may block legitimate requests)
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Anomaly Threshold</label>
            <input type="text" name="anomaly_threshold" class="form-input" 
                   value="{{ $policy->anomaly_threshold }}" required>
            <div class="form-help" style="direction: ltr; text-align: left;">Default: 5. Lower value = stricter protection</div>
        </div>
    </div>

    {{-- Blocked Attack Types --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">🚫 Blocked Attack Types</h2>

        <div class="checkbox-group">
            <div class="checkbox-item">
                <input type="checkbox" name="block_sql_injection" id="block_sql_injection" 
                       {{ $policy->block_sql_injection ? 'checked' : '' }}>
                <label for="block_sql_injection" class="checkbox-label">
                    SQL Injection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_xss" id="block_xss" 
                       {{ $policy->block_xss ? 'checked' : '' }}>
                <label for="block_xss" class="checkbox-label">
                    XSS (Cross-Site Scripting)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_rce" id="block_rce" 
                       {{ $policy->block_rce ? 'checked' : '' }}>
                <label for="block_rce" class="checkbox-label">
                    RCE (Remote Code Execution)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_lfi" id="block_lfi" 
                       {{ $policy->block_lfi ? 'checked' : '' }}>
                <label for="block_lfi" class="checkbox-label">
                    LFI (Local File Inclusion)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_rfi" id="block_rfi" 
                       {{ $policy->block_rfi ? 'checked' : '' }}>
                <label for="block_rfi" class="checkbox-label">
                    RFI (Remote File Inclusion)
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_suspicious_user_agents" id="block_suspicious_user_agents" 
                       {{ $policy->block_suspicious_user_agents ? 'checked' : '' }}>
                <label for="block_suspicious_user_agents" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Suspicious User Agents
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_path_traversal" id="block_path_traversal" 
                       {{ $policy->block_path_traversal ?? true ? 'checked' : '' }}>
                <label for="block_path_traversal" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Path Traversal
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_php_injection" id="block_php_injection" 
                       {{ $policy->block_php_injection ?? true ? 'checked' : '' }}>
                <label for="block_php_injection" class="checkbox-label" style="direction: ltr; text-align: left;">
                    PHP Injection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_java_injection" id="block_java_injection" 
                       {{ $policy->block_java_injection ?? true ? 'checked' : '' }}>
                <label for="block_java_injection" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Java Injection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_nodejs_injection" id="block_nodejs_injection" 
                       {{ $policy->block_nodejs_injection ?? true ? 'checked' : '' }}>
                <label for="block_nodejs_injection" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Node.js Injection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_session_fixation" id="block_session_fixation" 
                       {{ $policy->block_session_fixation ?? true ? 'checked' : '' }}>
                <label for="block_session_fixation" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Session Fixation
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_file_upload_attacks" id="block_file_upload_attacks" 
                       {{ $policy->block_file_upload_attacks ?? true ? 'checked' : '' }}>
                <label for="block_file_upload_attacks" class="checkbox-label" style="direction: ltr; text-align: left;">
                    File Upload Attacks
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_scanner_detection" id="block_scanner_detection" 
                       {{ $policy->block_scanner_detection ?? true ? 'checked' : '' }}>
                <label for="block_scanner_detection" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Scanner Detection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_protocol_attacks" id="block_protocol_attacks" 
                       {{ $policy->block_protocol_attacks ?? true ? 'checked' : '' }}>
                <label for="block_protocol_attacks" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Protocol Attacks
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_dos_protection" id="block_dos_protection" 
                       {{ $policy->block_dos_protection ?? false ? 'checked' : '' }}>
                <label for="block_dos_protection" class="checkbox-label" style="direction: ltr; text-align: left;">
                    DoS Protection
                </label>
            </div>

            <div class="checkbox-item">
                <input type="checkbox" name="block_data_leakages" id="block_data_leakages" 
                       {{ $policy->block_data_leakages ?? true ? 'checked' : '' }}>
                <label for="block_data_leakages" class="checkbox-label" style="direction: ltr; text-align: left;">
                    Data Leakages
                </label>
            </div>
        </div>
    </div>

    {{-- Rate Limiting --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">⏱️ Rate Limiting</h2>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="rate_limiting_enabled" id="rate_limiting_enabled" 
                       {{ $policy->rate_limiting_enabled ? 'checked' : '' }}>
                <label for="rate_limiting_enabled" class="checkbox-label" style="direction: ltr; text-align: left;">
                    <strong>Enable Rate Limiting</strong>
                </label>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" style="direction: ltr; text-align: left;">Requests Per Minute</label>
                <input type="number" name="requests_per_minute" class="form-input" 
                       value="{{ $policy->requests_per_minute }}" min="1">
            </div>

            <div class="form-group">
                <label class="form-label" style="direction: ltr; text-align: left;">Burst Size</label>
                <input type="number" name="burst_size" class="form-input" 
                       value="{{ $policy->burst_size }}" min="1">
                <div class="form-help" style="direction: ltr; text-align: left;">Additional requests allowed at once</div>
            </div>
        </div>
    </div>

    {{-- Exceptions --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">🔓 Exceptions</h2>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Excluded URLs (one per line)</label>
            <textarea name="excluded_urls" class="form-textarea" 
                      placeholder="/api/webhook&#10;/admin/login">{{ $policy->excluded_urls }}</textarea>
            <div class="form-help" style="direction: ltr; text-align: left;">These paths will bypass WAF checks</div>
        </div>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Whitelisted IPs (one per line)</label>
            <textarea name="excluded_ips" class="form-textarea" 
                      placeholder="192.168.1.1&#10;10.0.0.5">{{ $policy->excluded_ips }}</textarea>
            <div class="form-help" style="direction: ltr; text-align: left;">These IPs will bypass all WAF checks</div>
        </div>
    </div>

    {{-- Logging --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">📝 Logging</h2>

        <div class="form-group">
            <div class="checkbox-item">
                <input type="checkbox" name="detailed_logging" id="detailed_logging" 
                       {{ $policy->detailed_logging ? 'checked' : '' }}>
                <label for="detailed_logging" class="checkbox-label" style="direction: ltr; text-align: left;">
                    <strong>Enable Detailed Logging</strong>
                </label>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Log Level</label>
            <select name="log_level" class="form-select">
                <option value="debug" {{ $policy->log_level == 'debug' ? 'selected' : '' }}>Debug (Everything)</option>
                <option value="info" {{ $policy->log_level == 'info' ? 'selected' : '' }}>Info</option>
                <option value="warn" {{ $policy->log_level == 'warn' ? 'selected' : '' }}>Warning (Recommended)</option>
                <option value="error" {{ $policy->log_level == 'error' ? 'selected' : '' }}>Error Only</option>
            </select>
        </div>
    </div>

    {{-- Custom 403 Page --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">🚫 Custom 403 Forbidden Page</h2>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Custom 403 Page Path (Optional)</label>
            <input type="text" name="custom_403_page_path" class="form-input" 
                   value="{{ $policy->custom_403_page_path }}" 
                   placeholder="/etc/nginx/custom-403.html">
            <div class="form-help" style="direction: ltr; text-align: left;">
                Full path to your custom 403 HTML page. If empty, a default page will be generated automatically.
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Custom 403 Message (Optional)</label>
            <textarea name="custom_403_message" class="form-textarea" style="min-height: 80px;"
                      placeholder="Access Denied - Your request has been blocked by WAF">{{ $policy->custom_403_message }}</textarea>
            <div class="form-help" style="direction: ltr; text-align: left;">
                Custom message to display on the 403 page. Used if no custom page path is provided.
            </div>
        </div>
    </div>

    {{-- Custom Rules --}}
    <div class="card">
        <h2 class="section-title" style="direction: ltr; text-align: left;">⚡ Custom ModSecurity Rules</h2>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Additional ModSecurity Rules (Optional)</label>
            <textarea name="custom_modsec_rules" class="form-textarea" style="min-height: 150px;"
                      placeholder="SecRule ...">{{ $policy->custom_modsec_rules }}</textarea>
            <div class="form-help" style="direction: ltr; text-align: left;">
                Advanced ModSecurity rules. Use only if you know what you're doing!<br>
                Example: SecRule REQUEST_URI "@beginsWith /test" "id:100001,phase:1,deny,status:403"
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" style="direction: ltr; text-align: left;">Notes</label>
            <textarea name="notes" class="form-textarea" style="min-height: 80px;">{{ $policy->notes }}</textarea>
        </div>
    </div>

    <div style="display: flex; gap: 12px; direction: ltr;">
        <button type="submit" class="btn btn-primary">
            💾 Save Settings
        </button>
        <a href="{{ route('sites.index') }}" class="btn btn-secondary">
            ✕ Cancel
        </a>
    </div>
</form>

@endsection
