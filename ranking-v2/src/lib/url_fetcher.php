<?php
/**
 * Shared utility for fetching webpage content
 * Extracted from extract_metadata.php for reuse
 */

/**
 * משיכת תוכן דף אינטרנט
 * 
 * @param string $url כתובת ה-URL
 * @return string תוכן הדף או מחרוזת ריקה במקרה של שגיאה
 * @throws Exception במקרה של שגיאת cURL או HTTP
 */
function fetchWebpageContent($url) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HEADER => true, // לקבל גם headers
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL error: $error");
    }
    
    if ($http_code !== 200) {
        throw new Exception("HTTP error: $http_code");
    }
    
    // הפרדת headers מתוכן
    $headers = substr($response, 0, $header_size);
    $content = substr($response, $header_size);
    
    // בדיקה אם זה קובץ PDF
    if (stripos($content_type, 'application/pdf') !== false || 
        substr($content, 0, 4) === '%PDF') {
        // זה קובץ PDF - נחזיר רק מידע על הקובץ
        return "[PDF File] - Cannot extract text content from PDF. File type detected from Content-Type: $content_type";
    }
    
    // בדיקה אם התוכן נראה כמו בינארי
    if (!mb_check_encoding($content, 'UTF-8')) {
        // נסה לזהות את סוג הקובץ לפי ה-headers
        if (preg_match('/Content-Type:\s*([^\r\n]+)/i', $headers, $matches)) {
            $detected_type = trim($matches[1]);
            return "[Binary File - $detected_type] - Cannot extract text content from binary file";
        }
        return "[Binary File] - Cannot extract text content from binary file";
    }
    
    // ניקוי HTML - הסרת תגיות ושאריות
    $content = strip_tags($content);
    $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);
    
    // הגבלת אורך התוכן (OpenAI יש מגבלות)
    if (mb_strlen($content) > 8000) {
        $content = mb_substr($content, 0, 8000) . '...';
    }
    
    return $content;
}





