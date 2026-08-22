# PhpWebStorage

A secure, lightweight PHP REST API service for file storage management supporting **GET Query Parameters**, **Smart Auto-Detection**, and **1-Line Client Helper**.

---

## 🌟 GET Query Parameter Support

You can pass `api_key`, `action`, and file identifiers directly in the URL GET query string!

### URL Query Structure:
- **Upload**: `POST https://storage.xo.je/index.php?api_key=4566545&action=upload`
- **Replace**: `POST https://storage.xo.je/index.php?api_key=4566545&action=replace&old_file=MY_OLD_FILE.jpg`
- **Delete**: `GET https://storage.xo.je/index.php?api_key=4566545&action=delete&file_name=MY_FILE.jpg`

---

## 🚀 Ultra-Simple Client Usage (`storage.js`)

```javascript
// Initialize Client
const storage = new PhpWebStorage("https://storage.xo.je/index.php", "4566545");

// 1️⃣ Upload File
const url = await storage.upload(fileInput.files[0]);

// 2️⃣ Replace File
const newUrl = await storage.replace(oldUrl, newFileInput.files[0]);

// 3️⃣ Delete File
await storage.delete(fileUrl);
```

---

## 📑 Raw Code Examples (cURL, Python, PHP, JS)

### 1. Upload File

#### 🖥️ cURL
```bash
curl -X POST "https://storage.xo.je/index.php?api_key=4566545&action=upload" \
  -F "file=@/path/to/photo.jpg"
```

#### 🐍 Python (`requests`)
```python
import requests

url = "https://storage.xo.je/index.php?api_key=4566545&action=upload"
res = requests.post(url, files={"file": open("photo.jpg", "rb")})
print("URL:", res.json()["url"])
```

#### 🐘 PHP (`cURL`)
```php
<?php
$url = "https://storage.xo.je/index.php?api_key=4566545&action=upload";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['file' => new CURLFile('photo.jpg')]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = json_decode(curl_exec($ch), true);
curl_close($ch);

echo "URL: " . $res['url'];
```

---

### 2. Replace File

#### 🖥️ cURL
```bash
curl -X POST "https://storage.xo.je/index.php?api_key=4566545&action=replace&old_file=a1b2c3d4e5f67890123456789abcdef0.jpg" \
  -F "file=@/path/to/new_photo.jpg"
```

#### 🐍 Python
```python
url = "https://storage.xo.je/index.php?api_key=4566545&action=replace&old_file=a1b2c3d4e5f67890123456789abcdef0.jpg"
res = requests.post(url, files={"file": open("new_photo.jpg", "rb")})
print("New URL:", res.json()["url"])
```

---

### 3. Delete File (Works via GET request!)

#### 🖥️ cURL
```bash
curl "https://storage.xo.je/index.php?api_key=4566545&action=delete&file_name=a1b2c3d4e5f67890123456789abcdef0.jpg"
```

#### 🐍 Python
```python
url = "https://storage.xo.je/index.php?api_key=4566545&action=delete&file_name=a1b2c3d4e5f67890123456789abcdef0.jpg"
res = requests.get(url)
print("Deleted:", res.json()["status"])
```

---

## 🔒 Security Best Practices

1. Change the `API_KEY` value in `.env` for production use.
2. Keep `.env` protected and out of public version control repositories.
3. Path traversal attacks are automatically blocked using `parse_url()` and `basename()`.
