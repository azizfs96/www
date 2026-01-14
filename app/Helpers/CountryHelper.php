<?php

if (!function_exists('getCountryName')) {
    /**
     * Get country name from country code
     * 
     * @param string|null $code Country code (e.g., 'US', 'SA', 'GB')
     * @return string Country name or code if not found
     */
    function getCountryName(?string $code): string
    {
        if (empty($code)) {
            return 'Unknown';
        }

        $countries = [
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

        return $countries[strtoupper($code)] ?? $code;
    }
}

