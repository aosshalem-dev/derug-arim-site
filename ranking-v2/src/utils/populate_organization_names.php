<?php
/**
 * Script to populate organization_name column
 * Extracts organization names from URLs and existing metadata
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/migrate_add_organization_name.php';

function extractOrganizationNameFromUrl($url) {
    if (!$url) return null;
    
    try {
        $parsed = parse_url($url);
        $hostname = isset($parsed['host']) ? $parsed['host'] : '';
        
        if (!$hostname) return null;
        
        // Remove www. prefix
        $hostname = preg_replace('/^www\./', '', $hostname);
        
        // Extract domain name
        $parts = explode('.', $hostname);
        
        // Common patterns for Israeli municipalities
        $municipalityPatterns = [
            '/^([^\.]+)\.mun\.il$/i',
            '/^([^\.]+)\.municipality\.il$/i',
            '/^([^\.]+)\.city\.il$/i',
            '/^([^\.]+)\.gov\.il$/i',
        ];
        
        foreach ($municipalityPatterns as $pattern) {
            if (preg_match($pattern, $hostname, $matches)) {
                $name = $matches[1];
                $name = str_replace('-', ' ', $name);
                $name = ucwords($name);
                return $name;
            }
        }
        
        // Try to extract from subdomain
        if (count($parts) >= 3) {
            $subdomain = $parts[0];
            $commonWords = ['www', 'mail', 'ftp', 'admin', 'dev', 'test', 'staging', 'old', 'new'];
            if (!in_array(strtolower($subdomain), $commonWords)) {
                $name = str_replace('-', ' ', $subdomain);
                $name = ucwords($name);
                return $name;
            }
        }
        
        // Extract main domain name
        if (count($parts) >= 2) {
            $domain = $parts[count($parts) - 2];
            $name = str_replace('-', ' ', $domain);
            $name = ucwords($name);
            return $name;
        }
        
        return null;
    } catch (Exception $e) {
        return null;
    }
}

function extractOrganizationNameFromMetadata($record) {
    if (!empty($record['geographic_scope'])) {
        $scope = trim($record['geographic_scope']);
        if (strlen($scope) <= 50 && preg_match('/^[\p{Hebrew}\s\-]+$/u', $scope)) {
            return $scope;
        }
    }
    
    if (!empty($record['organization_type']) && !empty($record['geographic_scope'])) {
        $orgType = $record['organization_type'];
        $scope = trim($record['geographic_scope']);
        
        if ($orgType === 'municipality' && strlen($scope) <= 50) {
            return $scope;
        }
    }
    
    return null;
}

function callExternalAPI($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 3,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$html) {
        return null;
    }
    
    // Extract from title tag
    if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
        $title = strip_tags($matches[1]);
        $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $title = trim($title);
        
        $patterns = [
            '/עיריית\s+([^\s\-]+)/u',
            '/מועצת\s+([^\s\-]+)/u',
            '/רשות\s+([^\s\-]+)/u',
            '/^([^\s\-]+)\s+עירייה/u',
            '/^([^\s\-]+)\s+מועצה/u',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $title, $m)) {
                return trim($m[1]);
            }
        }
        
        if (preg_match('/^[\p{Hebrew}\s\-]+$/u', $title) && strlen($title) <= 50) {
            return $title;
        }
    }
    
    // Extract from meta description
    if (preg_match('/<meta[^>]*name=["\']description["\'][^>]*content=["\']([^"\']*)["\']/is', $html, $matches)) {
        $meta = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $meta = trim($meta);
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $meta, $m)) {
                return trim($m[1]);
            }
        }
    }
    
    return null;
}

function populateOrganizationNames($limit = null, $onlyEmpty = true) {
    $conn = getDbConnection();
    
    $where = $onlyEmpty ? "WHERE organization_name IS NULL OR organization_name = ''" : "";
    $limitClause = $limit ? "LIMIT " . (int)$limit : "";
    
    $query = "SELECT id, url, organization_type, geographic_scope, organization_name 
              FROM ranking_urls 
              $where 
              ORDER BY id 
              $limitClause";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $total = $result->num_rows;
    $updated = 0;
    $skipped = 0;
    $errors = 0;
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $url = $row['url'];
        $currentName = $row['organization_name'];
        
        if ($onlyEmpty && !empty($currentName)) {
            $skipped++;
            continue;
        }
        
        $organizationName = null;
        
        // Strategy 1: Extract from URL
        $organizationName = extractOrganizationNameFromUrl($url);
        
        // Strategy 2: Extract from existing metadata
        if (!$organizationName) {
            $organizationName = extractOrganizationNameFromMetadata($row);
        }
        
        // Strategy 3: Call external API
        if (!$organizationName) {
            try {
                $organizationName = callExternalAPI($url);
            } catch (Exception $e) {
                // Silently continue
            }
        }
        
        // Update database if we found a name
        if ($organizationName) {
            $organizationName = trim($organizationName);
            if (mb_strlen($organizationName) > 255) {
                $organizationName = mb_substr($organizationName, 0, 255);
            }
            
            $updateQuery = "UPDATE ranking_urls SET organization_name = ? WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("si", $organizationName, $id);
            
            if ($stmt->execute()) {
                $updated++;
            } else {
                $errors++;
            }
            $stmt->close();
        } else {
            $skipped++;
        }
        
        usleep(100000); // 0.1 seconds
    }
    
    closeDbConnection($conn);
    
    return [
        'total' => $total,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors
    ];
}





