@forelse ($events as $event)
    @php
        $status = (int) $event->status;
        $rule = $event->rule_id;
        $desc = $rule ? ($ruleDescriptions[$rule] ?? null) : null;
        // Use accessor to get event_time in Saudi Arabia timezone
        $eventTime = $event->event_time_saudi ?? $event->event_time ?? null;
        $timeAgo = $eventTime ? $eventTime->diffForHumans() : '';
    @endphp
    
    <div class="event-item" data-event-id="{{ $event->id ?? '' }}">
        {{-- Main Row --}}
        <div class="event-main-row" onclick="toggleEventDetails(this)">
            <div class="event-status {{ $status === 403 ? 'blocked' : ($status === 200 ? 'allowed' : 'other') }}" style="min-width: fit-content;">
                @if ($status === 403)
                    Blocked
                @elseif ($status === 200)
                    Allowed
                @else
                    {{ $status }}
                @endif
            </div>
            
            <div class="event-icon status-{{ $status }}">
                @if ($status === 403)
                    <span class="no-symbol"></span>
                @elseif ($status === 200)
                    <span style="display: inline-block; width: 14px; height: 14px; background: #4ADE80; border-radius: 50%;"></span>
                @elseif ($status === 404)
                    <span style="color: #B3B3B3; font-size: 14px; font-weight: bold;">ℹ</span>
                @else
                    <span style="color: #9D4EDD; font-size: 14px; font-weight: bold;">●</span>
                @endif
            </div>
            
            <div class="event-info">
                <span class="event-method-badge">{{ $event->method ?? 'GET' }}</span>
                @if ($event->uri)
                    <span class="event-uri">{{ $event->uri }}</span>
                @else
                    <span></span>
                @endif
                @if ($event->host)
                    <span class="event-host">{{ Str::limit($event->host, 30) }}</span>
                @else
                    <span></span>
                @endif
                <div style="display: flex; align-items: center; gap: 8px;">
                    <strong class="event-ip">{{ $event->client_ip }}</strong>
                    @if($event->country)
                        @php
                            $countryCode = strtoupper($event->country);
                            $flagEmoji = '';
                            // Convert ISO country code (e.g. SA) to emoji flag 🇸🇦
                            if (strlen($countryCode) === 2) {
                                $flagEmoji =
                                    mb_chr(ord($countryCode[0]) + 127397, 'UTF-8') .
                                    mb_chr(ord($countryCode[1]) + 127397, 'UTF-8');
                            } elseif ($countryCode === 'LOCAL') {
                                $flagEmoji = '🏠';
                            } elseif ($countryCode === 'PRIVATE') {
                                $flagEmoji = '🔒';
                            } elseif ($countryCode === 'UNKNOWN') {
                                $flagEmoji = '❓';
                            }
                        @endphp
                        <span class="event-country" 
                              data-country-code="{{ $countryCode }}"
                              onclick="showCountryTooltip(this, event)">
                            {{ $flagEmoji ?: $countryCode }}
                            <span class="country-tooltip" id="tooltip-{{ $event->id }}"></span>
                        </span>
                    @endif
                </div>
                @if ($rule || $status === 403)
                    <div class="event-value">
                        {{ $rule ?: 'WAF' }}
                        <span class="event-arrow">→</span>
                    </div>
                @else
                    <div class="event-value" style="visibility: hidden;">
                        &nbsp;
                    </div>
                @endif
            </div>
            
            <div class="event-time">
                <div class="event-duration">{{ $timeAgo }}</div>
                <div class="event-timestamp">{{ $eventTime ? $eventTime->format('Y-m-d H:i:s') : '' }}</div>
            </div>
        </div>
        
        {{-- Details Row (Sub-row) --}}
        <div class="event-details-row" id="details-{{ $event->id ?? '' }}">
            <div class="event-details-label">Attack Details</div>
            <div class="event-details-content">
                @if ($desc)
                    <div class="event-detail-item">
                        <span class="event-detail-label">Type:</span>
                        <span class="event-detail-value">{{ $desc }}</span>
                    </div>
                @endif
                @if ($rule)
                    <div class="event-detail-item">
                        <span class="event-detail-label">Rule ID:</span>
                        <span class="event-detail-value highlight">{{ $rule }}</span>
                    </div>
                @endif
                @if ($event->method)
                    <div class="event-detail-item">
                        <span class="event-detail-label">Method:</span>
                        <span class="event-detail-value">{{ $event->method }}</span>
                    </div>
                @endif
                @if ($event->severity)
                    <div class="event-detail-item">
                        <span class="event-detail-label">Severity:</span>
                        <span class="event-detail-value severity severity-{{ $event->severity }}">{{ $event->severity }}</span>
                    </div>
                @endif
                @if ($event->country)
                    @php
                        $countryNames = [
                            'US' => 'United States',
                            'SA' => 'Saudi Arabia',
                            'GB' => 'United Kingdom',
                            'DE' => 'Germany',
                            'FR' => 'France',
                            'CN' => 'China',
                            'JP' => 'Japan',
                            'IN' => 'India',
                            'BR' => 'Brazil',
                            'RU' => 'Russia',
                            'CA' => 'Canada',
                            'AU' => 'Australia',
                            'IT' => 'Italy',
                            'ES' => 'Spain',
                            'NL' => 'Netherlands',
                            'SE' => 'Sweden',
                            'NO' => 'Norway',
                            'DK' => 'Denmark',
                            'FI' => 'Finland',
                            'PL' => 'Poland',
                            'KR' => 'South Korea',
                            'MX' => 'Mexico',
                            'AR' => 'Argentina',
                            'ZA' => 'South Africa',
                            'EG' => 'Egypt',
                            'AE' => 'United Arab Emirates',
                            'TR' => 'Turkey',
                            'ID' => 'Indonesia',
                            'TH' => 'Thailand',
                            'VN' => 'Vietnam',
                            'PH' => 'Philippines',
                            'MY' => 'Malaysia',
                            'SG' => 'Singapore',
                            'NZ' => 'New Zealand',
                            'IE' => 'Ireland',
                            'CH' => 'Switzerland',
                            'AT' => 'Austria',
                            'BE' => 'Belgium',
                            'PT' => 'Portugal',
                            'GR' => 'Greece',
                            'CZ' => 'Czech Republic',
                            'HU' => 'Hungary',
                            'RO' => 'Romania',
                            'BG' => 'Bulgaria',
                            'HR' => 'Croatia',
                            'SK' => 'Slovakia',
                            'SI' => 'Slovenia',
                            'LT' => 'Lithuania',
                            'LV' => 'Latvia',
                            'EE' => 'Estonia',
                            'IS' => 'Iceland',
                            'LU' => 'Luxembourg',
                            'MT' => 'Malta',
                            'CY' => 'Cyprus',
                            'KW' => 'Kuwait',
                            'QA' => 'Qatar',
                            'BH' => 'Bahrain',
                            'OM' => 'Oman',
                            'JO' => 'Jordan',
                            'LB' => 'Lebanon',
                            'IQ' => 'Iraq',
                            'SY' => 'Syria',
                            'YE' => 'Yemen',
                            'PK' => 'Pakistan',
                            'BD' => 'Bangladesh',
                            'LK' => 'Sri Lanka',
                            'NP' => 'Nepal',
                            'AF' => 'Afghanistan',
                            'IR' => 'Iran',
                            'IL' => 'Israel',
                            'PS' => 'Palestine',
                            'LOCAL' => 'Local Network',
                            'PRIVATE' => 'Private Network',
                            'UNKNOWN' => 'Unknown Country',
                        ];
                        $countryName = $countryNames[strtoupper($event->country)] ?? $event->country;
                    @endphp
                    <div class="event-detail-item">
                        <span class="event-detail-label">Country:</span>
                        <span class="event-detail-value">{{ $countryName }}</span>
                    </div>
                @endif
                @if ($event->message)
                    <div class="event-message">
                        <strong style="color: var(--text-muted);">Message:</strong> {{ Str::limit($event->message, 150) }}
                    </div>
                @endif
            </div>
            
            {{-- AI Analysis Button --}}
            <div class="event-detail-actions" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <button onclick="analyzeEvent({{ $event->id }})" class="btn-ai-analyze" title="تحليل بواسطة AI">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    AI Analysis
                </button>
            </div>
        </div>
    </div>
@empty
    <div class="empty-state">
        No events found matching the current filters.
    </div>
@endforelse


