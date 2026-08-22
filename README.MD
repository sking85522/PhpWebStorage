# PhpWebStorage API Documentation & Usage Guide

A lightweight, secure, and feature-rich PHP REST API service for file storage management. It supports local file uploads, downloading remote files by URL, replacing files (local & remote), and deleting stored files.

---

## 🔑 Authentication

Every API request requires the `apikey` parameter.

- **Default API Key**: `4566545`
- **Parameter Name**: `apikey`
- **Supported Pass Methods**: GET Query String, POST Form-Data, or `$_REQUEST`

---

## 🚀 Supported Actions & API Reference

**Endpoint**: `POST` or `GET` to `http://localhost/PhpWebStorage/index.php`

---

### 1. Upload File (`action=upload`)

Uploads a local file from the client to the server storage folder.

#### Required Parameters:
- `apikey`: `4566545`
- `action`: `upload`
- `file`: `[Binary File]` (sent via `multipart/form-data`)

#### PHP cURL Example
```php
<?php
$url = "http://localhost/PhpWebStorage/index.php";
$ch = curl_init();
$data = [
    'apikey' => '4566545',
    'action' => 'upload',
    'file'   => new CURLFile('sample.png', 'image/png', 'sample.png')
];
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

#### cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -F "apikey=4566545" \
  -F "action=upload" \
  -F "file=@/path/to/sample.png"
```

#### Python Example
```python
import requests

url = "http://localhost/PhpWebStorage/index.php"
data = {"apikey": "4566545", "action": "upload"}
files = {"file": open("sample.png", "rb")}

response = requests.post(url, data=data, files=files)
print(response.json())
```

#### Success Response
```json
{
  "status": true,
  "message": "File add success fully",
  "fileurl": "http://localhost/PhpWebStorage/storage/a1b2c3d4e5f67890123456789abcdef0.png",
  "filename": "a1b2c3d4e5f67890123456789abcdef0.png"
}
```

---

### 2. Download File by URL (`action=byurl`)

Downloads a file from a remote web URL and saves it into the storage folder with a 32-character random hex name.

#### Required Parameters:
- `apikey`: `4566545`
- `action`: `byurl`
- `byurl`: `https://example.com/image.jpg` (Remote URL)

#### PHP cURL Example
```php
<?php
$url = "http://localhost/PhpWebStorage/index.php";
$ch = curl_init();
$data = [
    'apikey' => '4566545',
    'action' => 'byurl',
    'byurl'  => 'https://example.com/images/sample.jpg'
];
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

#### cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -F "apikey=4566545" \
  -F "action=byurl" \
  -F "byurl=https://example.com/images/sample.jpg"
```

#### Python Example
```python
import requests

url = "http://localhost/PhpWebStorage/index.php"
data = {
    "apikey": "4566545",
    "action": "byurl",
    "byurl": "https://example.com/images/sample.jpg"
}
response = requests.post(url, data=data)
print(response.json())
```

---

### 3. Replace File Locally (`action=replace`)

Deletes an existing file from the storage folder and uploads a new file.

#### Required Parameters:
- `apikey`: `4566545`
- `action`: `replace`
- `filename` OR `fileurl`: Name or full URL of the existing file to delete.
- `file`: `[New Binary File]`

#### PHP cURL Example
```php
<?php
$url = "http://localhost/PhpWebStorage/index.php";
$ch = curl_init();
$data = [
    'apikey'   => '4566545',
    'action'   => 'replace',
    'filename' => 'old_file_name.png',
    'file'     => new CURLFile('new_sample.png', 'image/png', 'new_sample.png')
];
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

#### cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -F "apikey=4566545" \
  -F "action=replace" \
  -F "filename=old_file_name.png" \
  -F "file=@/path/to/new_sample.png"
```

---

### 4. Replace File by Remote URL (`action=replacebyurl`)

Deletes an existing file from storage and downloads a new file from a remote URL.

#### Required Parameters:
- `apikey`: `4566545`
- `action`: `replacebyurl`
- `filename` OR `fileurl`: Name or full URL of the existing file to delete.
- `byurl`: `https://example.com/new_image.jpg` (New Remote URL)

#### PHP cURL Example
```php
<?php
$url = "http://localhost/PhpWebStorage/index.php";
$ch = curl_init();
$data = [
    'apikey'   => '4566545',
    'action'   => 'replacebyurl',
    'filename' => 'old_file_name.png',
    'byurl'    => 'https://example.com/images/new_photo.jpg'
];
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

#### cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -F "apikey=4566545" \
  -F "action=replacebyurl" \
  -F "filename=old_file_name.png" \
  -F "byurl=https://example.com/images/new_photo.jpg"
```

---

### 5. Delete File (`action=delete`)

Permanently removes a file from the server storage.

#### Required Parameters:
- `apikey`: `4566545`
- `action`: `delete`
- `filename` OR `fileurl`: Name or URL of the file to delete.

#### PHP cURL Example
```php
<?php
$url = "http://localhost/PhpWebStorage/index.php";
$ch = curl_init();
$data = [
    'apikey'   => '4566545',
    'action'   => 'delete',
    'filename' => 'a1b2c3d4e5f67890123456789abcdef0.png'
];
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
```

#### cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -F "apikey=4566545" \
  -F "action=delete" \
  -F "filename=a1b2c3d4e5f67890123456789abcdef0.png"
```

---

## 🛠️ Complete Code Review Report for `index.php`

### ⭐ Overall Rating: **9.0 / 10** (Highly Functional & Feature-Rich)

---

### 🟢 Strengths (Behtareen Baatein)

1. **Rich Functionality (5 Actions)**:
   - Having `upload`, `byurl`, `replace`, `replacebyurl`, and `delete` makes this storage API versatile for both local uploads and remote web scrapers / file mirrors.
2. **Modular Functions**:
   - Clean separation into dedicated PHP functions: `uploadfile()`, `byurl()`, `deletefile()`, `replacefile()`, and `replacebyurl()`.
3. **Flexible Input (`$_REQUEST`)**:
   - Supports GET, POST, or multipart form data parameters easily.
4. **Smart Fallback for Filename**:
   - `$filename = $_REQUEST["filename"] ?? basename(parse_url($fileurl, PHP_URL_PATH));` allows deleting/replacing by passing either the filename OR the full URL!
5. **Secure Random Filenames**:
   - `bin2hex(random_bytes(16))` produces 32-character un-guessable hex filenames.

---

### ⚠️ Recommendations & Things to Keep in Mind (Dhyan Rakhne Yogya Baatein)

1. **Line 41 Validation Check (`byurl` action)**:
   - In Line 41:
     ```php
     if($action == "byurl"){
         if(empty($_REQUEST["filename"])){ ... }
     }
     ```
     *Note*: For `byurl`, the required field is `$_REQUEST["byurl"]`. Checking `empty($_REQUEST["filename"])` means `byurl` action currently requires `filename` to be passed as well.
2. **Folder Creation Check**:
   - Ensure the `downloads/` directory exists or create it automatically before `file_put_contents("downloads/...", ...)` is called in `byurl()`.
3. **Double Extension Handling in `byurl()`**:
   - If downloading URLs with query parameters (e.g. `image.jpg?version=1`), `pathinfo()` might include query string in the extension. Using `parse_url()` before `pathinfo()` handles this cleanly.
