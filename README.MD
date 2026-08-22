# PhpWebStorage

A secure, lightweight PHP REST API service for file storage management. Supports **File Upload**, **File Replacement**, and **File Deletion** with built-in security protections.

---

## 🌟 Key Features

- **Upload File (`upload`)**: Uploads files up to 8MB with unique random hex filenames.
- **Replace File (`replace`)**: Replaces an existing stored file with a new file in a single request.
- **Delete File (`delete`)**: Permanently removes a stored file.
- **API Key Security**: Validates requests using an `X-API-KEY` header with secure string comparison (`hash_equals`).
- **Path Traversal Protection**: Uses `parse_url()` and `basename()` sanitization to block malicious file path manipulation.
- **File Type Allowlist**: Accepts safe file extensions (`jpg`, `jpeg`, `png`, `gif`, `webp`, `pdf`, `docx`, `xlsx`, `pptx`, `txt`, `zip`, `mp4`, `mp3`).
- **Execution Prevention**: Includes `.htaccess` in `/storage/` directory to block PHP execution on uploaded files.

---

## 🚀 Installation & Setup

1. **Clone / Copy to Server**:
   Place the project files inside your web server directory (e.g., `XAMPP/htdocs/PhpWebStorage`).

2. **Environment Configuration (`.env`)**:
   Create or edit the `.env` file in the root directory:
   ```ini
   API_KEY=4566545
   ```

3. **Storage Directory Permissions**:
   Ensure the `storage/` directory has write permissions:
   ```bash
   chmod 777 storage/
   ```

---

## 📑 API Reference & Code Examples

**Endpoint**: `POST /index.php`  
**Required Header**: `X-API-KEY: <YOUR_API_KEY>`

---

### 1. Upload File

Uploads a new file to the storage folder.

#### 🖥️ cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -H "X-API-KEY: 4566545" \
  -F "action=upload" \
  -F "file=@/path/to/my_image.png"
```

#### 🐍 Python Example (`requests`)
```python
import requests

API_URL = "http://localhost/PhpWebStorage/index.php"
HEADERS = {"X-API-KEY": "4566545"}

with open("my_image.png", "rb") as f:
    files = {"file": f}
    data = {"action": "upload"}
    response = requests.post(API_URL, headers=HEADERS, data=data, files=files)
    print(response.json())
```

#### 🐘 PHP Example (`cURL`)
```php
<?php
$apiUrl = "http://localhost/PhpWebStorage/index.php";
$apiKey = "4566545";

$ch = curl_init();
$file = new CURLFile('my_image.png', 'image/png', 'my_image.png');
$data = [
    'action' => 'upload',
    'file' => $file
];

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-KEY: $apiKey"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
```

#### 🟨 JavaScript Example (Fetch API)
```javascript
const formData = new FormData();
formData.append('action', 'upload');
formData.append('file', fileInput.files[0]);

fetch('http://localhost/PhpWebStorage/index.php', {
    method: 'POST',
    headers: { 'X-API-KEY': '4566545' },
    body: formData
})
.then(res => res.json())
.then(data => console.log(data));
```

#### Response Success (200 OK)
```json
{
  "status": true,
  "message": "File uploaded successfully",
  "data": {
    "original_name": "my_image.png",
    "saved_name": "a1b2c3d4e5f67890123456789abcdef0.png",
    "size": 102450,
    "url": "http://localhost/PhpWebStorage/storage/a1b2c3d4e5f67890123456789abcdef0.png"
  }
}
```

---

### 2. Replace File

Deletes the existing file and uploads a new file.

#### 🖥️ cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -H "X-API-KEY: 4566545" \
  -F "action=replace" \
  -F "old_file=a1b2c3d4e5f67890123456789abcdef0.png" \
  -F "file=@/path/to/new_image.png"
```

#### 🐍 Python Example (`requests`)
```python
import requests

API_URL = "http://localhost/PhpWebStorage/index.php"
HEADERS = {"X-API-KEY": "4566545"}

with open("new_image.png", "rb") as f:
    files = {"file": f}
    data = {
        "action": "replace",
        "old_file": "a1b2c3d4e5f67890123456789abcdef0.png" # or full URL
    }
    response = requests.post(API_URL, headers=HEADERS, data=data, files=files)
    print(response.json())
```

#### 🐘 PHP Example (`cURL`)
```php
<?php
$apiUrl = "http://localhost/PhpWebStorage/index.php";
$apiKey = "4566545";

$ch = curl_init();
$newFile = new CURLFile('new_image.png', 'image/png', 'new_image.png');
$data = [
    'action' => 'replace',
    'old_file' => 'a1b2c3d4e5f67890123456789abcdef0.png',
    'file' => $newFile
];

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-KEY: $apiKey"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
```

#### Response Success (200 OK)
```json
{
  "status": true,
  "message": "File replaced successfully",
  "old_file_deleted": true,
  "data": {
    "original_name": "new_image.png",
    "saved_name": "9876543210fedcba0987654321abcdef.png",
    "size": 204800,
    "url": "http://localhost/PhpWebStorage/storage/9876543210fedcba0987654321abcdef.png"
  }
}
```

---

### 3. Delete File

Permanently removes a file from the server storage.

#### 🖥️ cURL Command
```bash
curl -X POST "http://localhost/PhpWebStorage/index.php" \
  -H "X-API-KEY: 4566545" \
  -F "action=delete" \
  -F "file_name=9876543210fedcba0987654321abcdef.png"
```

#### 🐍 Python Example (`requests`)
```python
import requests

API_URL = "http://localhost/PhpWebStorage/index.php"
HEADERS = {"X-API-KEY": "4566545"}

data = {
    "action": "delete",
    "file_name": "9876543210fedcba0987654321abcdef.png" # or full URL
}
response = requests.post(API_URL, headers=HEADERS, data=data)
print(response.json())
```

#### 🐘 PHP Example (`cURL`)
```php
<?php
$apiUrl = "http://localhost/PhpWebStorage/index.php";
$apiKey = "4566545";

$ch = curl_init();
$data = [
    'action' => 'delete',
    'file_name' => '9876543210fedcba0987654321abcdef.png'
];

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["X-API-KEY: $apiKey"]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);
echo $response;
```

#### Response Success (200 OK)
```json
{
  "status": true,
  "message": "File deleted successfully",
  "deleted_file": "9876543210fedcba0987654321abcdef.png"
}
```

---

## 🧪 Testing Interface

An interactive test page is included in the project:
Open `http://localhost/PhpWebStorage/test.html` in your web browser to test **Upload**, **Replace**, and **Delete** actions visually with live JSON output.

---

## 🔒 Security Best Practices

1. Change the `API_KEY` value in `.env` for production use.
2. Keep `.env` protected and out of public version control repositories.
3. Ensure server PHP settings (`upload_max_filesize` and `post_max_size` in `php.ini`) are set to at least `8M`.
