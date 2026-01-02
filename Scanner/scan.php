<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Barcode Scanner - TimelessWatch.co</title>
  <script src="js/html5-qrcode.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #1a237e;
      --primary-light: #534bae;
      --primary-dark: #000051;
      --secondary: #ff9800;
      --accent: #00bcd4;
      --success: #4caf50;
      --warning: #ff9800;
      --danger: #f44336;
      --light: #f5f5f5;
      --dark: #212121;
      --gray: #757575;
      --gray-light: #e0e0e0;
      --border-radius: 16px;
      --box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', 'Roboto', sans-serif;
    }

    body {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: var(--dark);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
    }

    /* Header Container */
    .header-container {
      text-align: center;
      margin-bottom: 30px;
      max-width: 600px;
      width: 100%;
    }

    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 15px;
      margin-bottom: 20px;
    }

    .logo-icon {
      width: 50px;
      height: 50px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 24px;
      box-shadow: var(--box-shadow);
    }

    .logo-text h1 {
      font-size: 28px;
      font-weight: 700;
      color: white;
      margin-bottom: 5px;
    }

    .logo-text p {
      color: rgba(255, 255, 255, 0.8);
      font-size: 14px;
    }

    /* Main Content */
    .main-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      max-width: 900px;
      gap: 30px;
    }

    /* Scanner Container */
    .scanner-container {
      background: white;
      border-radius: var(--border-radius);
      padding: 30px;
      box-shadow: var(--box-shadow);
      width: 100%;
      max-width: 500px;
      position: relative;
      overflow: hidden;
    }

    .scanner-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 25px;
    }

    .scanner-title {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .scanner-title i {
      color: var(--primary);
      font-size: 24px;
    }

    .scanner-title h2 {
      font-size: 22px;
      color: var(--primary);
      font-weight: 700;
    }

    .scanner-status {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
      background: rgba(26, 35, 126, 0.1);
      border-radius: 20px;
      font-size: 14px;
      color: var(--primary);
      font-weight: 600;
    }

    .scanner-status i {
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    #reader {
      width: 100%;
      border-radius: 12px;
      overflow: hidden;
      position: relative;
      background: #000;
      min-height: 300px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .scanner-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: 1;
    }

    .scanner-frame {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 70%;
      height: 200px;
      border: 3px solid var(--success);
      border-radius: 12px;
      box-shadow: 0 0 0 1000px rgba(0, 0, 0, 0.5);
    }

    .scanner-guide {
      position: absolute;
      top: calc(50% - 100px);
      left: 50%;
      transform: translateX(-50%);
      color: white;
      text-align: center;
      width: 100%;
      font-size: 14px;
      z-index: 2;
    }

    /* Barcode Form */
    .form-container {
      background: white;
      border-radius: var(--border-radius);
      padding: 30px;
      box-shadow: var(--box-shadow);
      width: 100%;
      max-width: 500px;
    }

    .form-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 25px;
    }

    .form-header i {
      color: var(--secondary);
      font-size: 24px;
    }

    .form-header h3 {
      font-size: 20px;
      color: var(--primary);
      font-weight: 700;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: var(--dark);
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .input-wrapper {
      position: relative;
    }

    .barcode-input {
      width: 100%;
      padding: 16px 20px 16px 50px;
      border: 2px solid var(--gray-light);
      border-radius: var(--border-radius);
      font-size: 18px;
      font-family: 'Courier New', monospace;
      letter-spacing: 2px;
      transition: var(--transition);
      background: var(--light);
    }

    .barcode-input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
      background: white;
    }

    .input-icon {
      position: absolute;
      left: 20px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--gray);
      font-size: 20px;
    }

    /* Buttons */
    .btn {
      padding: 16px 32px;
      border: none;
      border-radius: var(--border-radius);
      font-weight: 600;
      font-size: 16px;
      cursor: pointer;
      transition: var(--transition);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      width: 100%;
    }

    .btn-primary {
      background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(26, 35, 126, 0.3);
    }

    .btn-primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(26, 35, 126, 0.4);
    }

    .btn-success {
      background: linear-gradient(135deg, var(--success) 0%, #2e7d32 100%);
      color: white;
      box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
    }

    .btn-success:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
    }

    /* Messages */
    .message-container {
      background: white;
      border-radius: var(--border-radius);
      padding: 20px;
      box-shadow: var(--box-shadow);
      width: 100%;
      max-width: 500px;
      display: none;
    }

    .message-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }

    .message-header i {
      font-size: 20px;
    }

    .message-text {
      font-size: 14px;
      color: var(--gray);
      line-height: 1.6;
    }

    /* Back Button */
    .back-button {
      position: fixed;
      top: 25px;
      left: 25px;
      background: rgba(255, 255, 255, 0.9);
      color: var(--primary);
      padding: 12px 20px;
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 1000;
      transition: var(--transition);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      backdrop-filter: blur(10px);
    }

    .back-button:hover {
      background: white;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    /* Instructions */
    .instructions {
      background: rgba(255, 255, 255, 0.9);
      border-radius: var(--border-radius);
      padding: 20px;
      box-shadow: var(--box-shadow);
      width: 100%;
      max-width: 500px;
      backdrop-filter: blur(10px);
    }

    .instructions-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }

    .instructions-header i {
      color: var(--accent);
      font-size: 20px;
    }

    .instructions-list {
      list-style: none;
      padding-left: 0;
    }

    .instructions-list li {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
      font-size: 14px;
      color: var(--gray);
    }

    .instructions-list li i {
      color: var(--success);
      font-size: 12px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .main-content {
        flex-direction: column;
      }
      
      .scanner-container,
      .form-container,
      .instructions {
        max-width: 100%;
      }
      
      .back-button {
        top: 15px;
        left: 15px;
        padding: 10px 15px;
        font-size: 14px;
      }
      
      .logo-text h1 {
        font-size: 24px;
      }
    }

    @media (max-width: 480px) {
      .scanner-title h2 {
        font-size: 18px;
      }
      
      .barcode-input {
        font-size: 16px;
        padding: 14px 20px 14px 45px;
      }
      
      .btn {
        padding: 14px 24px;
        font-size: 15px;
      }
    }

    /* Animation for scan success */
    @keyframes scanSuccess {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    .scan-success {
      animation: scanSuccess 0.5s ease;
    }
  </style>
</head>
<body>

<a href="../admin/produk.php" class="back-button">
  <i class="fas fa-arrow-left"></i> Kembali ke Produk
</a>

<div class="header-container">
  <div class="logo">
    <div class="logo-icon">
      <i class="fas fa-barcode"></i>
    </div>
    <div class="logo-text">
      <h1>Barcode Scanner Pro</h1>
      <p>TimelessWatch.co - Professional Inventory System</p>
    </div>
  </div>
</div>

<div class="main-content">
  <!-- Scanner Container -->
  <div class="scanner-container">
    <div class="scanner-header">
      <div class="scanner-title">
        <i class="fas fa-camera"></i>
        <h2>Scanner Kamera</h2>
      </div>
      <div class="scanner-status" id="scannerStatus">
        <i class="fas fa-circle"></i>
        <span>Scanner Aktif</span>
      </div>
    </div>
    
    <div id="reader"></div>
    <div class="scanner-overlay">
      <div class="scanner-frame"></div>
      <div class="scanner-guide">
        <i class="fas fa-expand-alt"></i> Arahkan kamera ke barcode produk
      </div>
    </div>
  </div>

  <!-- Instructions -->
  <div class="instructions">
    <div class="instructions-header">
      <i class="fas fa-info-circle"></i>
      <h4>Panduan Scanning</h4>
    </div>
    <ul class="instructions-list">
      <li><i class="fas fa-check-circle"></i> Pastikan barcode dalam kondisi baik dan terbaca jelas</li>
      <li><i class="fas fa-check-circle"></i> Jaga jarak optimal 15-30 cm dari kamera</li>
      <li><i class="fas fa-check-circle"></i> Gunakan pencahayaan yang cukup untuk hasil terbaik</li>
      <li><i class="fas fa-check-circle"></i> Atau gunakan input manual di bawah ini</li>
    </ul>
  </div>

  <!-- Barcode Form -->
  <div class="form-container">
    <div class="form-header">
      <i class="fas fa-keyboard"></i>
      <h3>Input Manual / Scanner Eksternal</h3>
    </div>
    
    <form action="proses_scan.php" method="POST" id="barcodeForm">
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-barcode"></i> Kode Barcode
        </label>
        <div class="input-wrapper">
          <i class="fas fa-qrcode input-icon"></i>
          <input type="text" 
                 id="barcode" 
                 name="barcode" 
                 class="barcode-input" 
                 placeholder="Ketik atau scan barcode di sini..." 
                 required 
                 autofocus />
        </div>
      </div>
      
      <div class="form-group">
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-search"></i> Cari Produk
        </button>
      </div>
    </form>
    
    <div id="feedbackMessage" class="message-container" style="display: none;">
      <div class="message-header">
        <i class="fas fa-check-circle" style="color: var(--success);"></i>
        <strong>Scan Berhasil!</strong>
      </div>
      <p class="message-text" id="messageText"></p>
    </div>
  </div>
</div>

<!-- Suara beep -->
<audio id="beepSound" src="sounds/beep.mp3" preload="auto"></audio>

<script>
  const html5QrCode = new Html5Qrcode("reader");

  const config = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    formatsToSupport: [
      Html5QrcodeSupportedFormats.QR_CODE,
      Html5QrcodeSupportedFormats.CODE_128,
      Html5QrcodeSupportedFormats.EAN_13,
      Html5QrcodeSupportedFormats.CODE_39
    ]
  };

  // Fungsi mainkan suara beep
  function playBeep() {
    const beep = document.getElementById("beepSound");
    if (beep) {
      beep.currentTime = 0;
      beep.play().catch(err => console.warn("Beep tidak dapat diputar:", err));
    }
  }

  // Fungsi tampilkan pesan feedback
  function showFeedback(message, isSuccess = true) {
    const feedbackEl = document.getElementById("feedbackMessage");
    const messageTextEl = document.getElementById("messageText");
    
    messageTextEl.textContent = message;
    feedbackEl.style.display = 'block';
    
    const icon = feedbackEl.querySelector('i');
    icon.className = isSuccess ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    icon.style.color = isSuccess ? 'var(--success)' : 'var(--warning)';
    
    // Hilangkan pesan setelah 3 detik
    setTimeout(() => {
      feedbackEl.style.display = 'none';
    }, 3000);
  }

  // Fungsi update status scanner
  function updateScannerStatus(status, isActive = true) {
    const statusEl = document.getElementById("scannerStatus");
    const statusIcon = statusEl.querySelector('i');
    const statusText = statusEl.querySelector('span');
    
    statusText.textContent = status;
    statusIcon.style.color = isActive ? 'var(--success)' : 'var(--warning)';
    statusEl.style.background = isActive ? 'rgba(76, 175, 80, 0.1)' : 'rgba(255, 152, 0, 0.1)';
    statusEl.style.color = isActive ? 'var(--success)' : 'var(--warning)';
  }

  // Start kamera scanner
  function startScanner() {
    html5QrCode.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        console.log("Kode berhasil dipindai:", decodedText);
        
        // Update UI
        document.getElementById("barcode").value = decodedText;
        document.getElementById("barcode").classList.add('scan-success');
        
        // Tampilkan feedback
        showFeedback(`✅ Barcode berhasil dipindai: ${decodedText}`, true);
        updateScannerStatus("Scan Berhasil!", true);
        playBeep();

        // Stop scanner setelah berhasil
        setTimeout(() => {
          html5QrCode.stop().then(() => {
            console.log("Scanner kamera dihentikan.");
            updateScannerStatus("Scanner Siap", false);
            
            // Submit form setelah delay
            setTimeout(() => {
              document.getElementById("barcodeForm").submit();
            }, 1000);
          }).catch(err => {
            console.error("Gagal menghentikan kamera scanner:", err);
          });
        }, 500);
      },
      (errorMessage) => {
        // Error handling
        console.log("Scanner error:", errorMessage);
        if (errorMessage.includes("NotAllowedError")) {
          updateScannerStatus("Izin Kamera Ditolak", false);
          showFeedback("Izin kamera diperlukan untuk scanning. Silakan aktifkan izin kamera di browser.", false);
        }
      }
    ).catch(err => {
      console.error("Gagal memulai scanner:", err);
      updateScannerStatus("Scanner Error", false);
      showFeedback("Tidak dapat mengakses kamera. Pastikan kamera tersedia dan izin diberikan.", false);
    });
  }

  // Initialize scanner
  document.addEventListener('DOMContentLoaded', function() {
    // Start scanner
    startScanner();
    
    // Focus on input
    const barcodeInput = document.getElementById("barcode");
    barcodeInput.focus();
    barcodeInput.select();
    
    // Handle manual input change
    barcodeInput.addEventListener("change", function () {
      const barcode = this.value.trim();
      if (barcode !== "") {
        showFeedback(`✅ Barcode diterima: ${barcode}`, true);
        playBeep();
        
        // Submit form
        setTimeout(() => {
          document.getElementById("barcodeForm").submit();
        }, 500);
      }
    });
    
    // Handle form submission
    document.getElementById("barcodeForm").addEventListener("submit", function(event) {
      const barcode = document.getElementById("barcode").value.trim();
      if (!barcode) {
        event.preventDefault();
        showFeedback("❗ Tidak ada barcode yang dipindai.", false);
        return false;
      }
      
      // Show loading state
      const submitBtn = this.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
      submitBtn.disabled = true;
      
      // Re-enable button after 3 seconds if still on page
      setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }, 3000);
    });
    
    // Handle Enter key on input
    barcodeInput.addEventListener("keypress", function(e) {
      if (e.key === "Enter") {
        e.preventDefault();
        this.form.submit();
      }
    });
    
    // Handle visibility change (when tab switches)
    document.addEventListener("visibilitychange", function() {
      if (document.hidden) {
        // Pause scanner when tab is not active
        html5QrCode.pause();
        updateScannerStatus("Scanner Dijeda", false);
      } else {
        // Resume scanner when tab becomes active
        html5QrCode.resume();
        updateScannerStatus("Scanner Aktif", true);
      }
    });
    
    // Add vibration on mobile if supported
    if ("vibrate" in navigator) {
      function vibrateFeedback() {
        navigator.vibrate(100);
      }
      window.vibrateFeedback = vibrateFeedback;
    }
  });

  // Error handling for audio
  window.addEventListener('load', function() {
    const beep = document.getElementById('beepSound');
    if (beep) {
      beep.load(); // Preload audio
    }
  });
</script>

</body>
</html>