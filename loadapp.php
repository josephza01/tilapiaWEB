<?php  
// กำหนดชื่อไฟล์ที่ต้องการให้ดาวน์โหลด (เปลี่ยนชื่อไฟล์ให้ตรงกับที่มีในเซิร์ฟเวอร์ของคุณ)  
$file = './assets/oxylo.apk'; // ใส่ชื่อไฟล์ APK ของคุณ  

// ตรวจสอบว่าไฟล์มีอยู่จริง  
if (file_exists($file)) {  
    // บังคับให้เบราว์เซอร์ดาวน์โหลดไฟล์  
    header('Content-Description: File Transfer');  
    header('Content-Type: application/vnd.android.package-archive');  
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');  
    header('Expires: 0');  
    header('Cache-Control: must-revalidate');  
    header('Pragma: public');  
    header('Content-Length: ' . filesize($file));  
    flush(); // Flush system output buffer  
    readfile($file);  
    exit;  
} else {  
    // ถ้าไม่พบไฟล์  
    http_response_code(404);  
    echo "File not found.";  
}  
?>