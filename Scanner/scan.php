<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan Barcode Produk</title>
  <script src="js/html5-qrcode.min.js"></script>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background: #f0f2f5;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }

    h2 {
      margin-top: 40px;
      font-size: 28px;
      color: #2c3e50;
    }

    #reader {
      width: 100%;
      max-width: 380px;
      border-radius: 12px;
      overflow: hidden;
      margin-top: 20px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.08);
      background-color: #fff;
      border: 2px solid #e0e0e0;
    }

    form {
      background: #ffffff;
      margin-top: 30px;
      padding: 25px 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      width: 100%;
      max-width: 400px;
      text-align: center;
    }

    label {
      display: block;
      margin-bottom: 10px;
      font-weight: 600;
      color: #555;
    }

    input[type="text"] {
      width: 90%;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
      margin-bottom: 20px;
      background-color: #f9f9f9;
      text-align: center;
    }

    button {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #218838;
    }

    .message {
      margin-top: 15px;
      font-size: 14px;
      color: #666;
    }

    @media (max-width: 480px) {
      form {
        padding: 20px;
      }

      input[type="text"] {
        width: 100%;
      }
    }
  </style>
</head>
<body>

  <h2>Scan Barcode Produk</h2>

  <!-- Area Kamera -->
  <div id="reader"></div>

  <!-- Form hasil scan -->
  <form action="proses_scan.php" method="POST" id="barcodeForm">
    <label for="barcode">Kode Barcode</label>
    <input type="text" id="barcode" name="barcode" readonly required>
    <button type="submit">Cari Produk</button>
    <div class="message" id="feedbackMessage"></div>
  </form>

  <script>
    const html5QrCode = new Html5Qrcode("reader");

    const config = {
      fps: 10,
      qrbox: { width: 250, height: 250 },
      supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
      formatsToSupport: [
        Html5QrcodeSupportedFormats.QR_CODE,
        Html5QrcodeSupportedFormats.CODE_128,
        Html5QrcodeSupportedFormats.EAN_13,
        Html5QrcodeSupportedFormats.CODE_39
      ]
    };

    html5QrCode.start(
      { facingMode: "environment" },
      config,
      (decodedText, decodedResult) => {
        console.log("Kode berhasil dipindai:", decodedText);
        document.getElementById("barcode").value = decodedText;
        document.getElementById("feedbackMessage").textContent = "✅ Kode berhasil dipindai.";

        html5QrCode.stop().then(() => {
          console.log("Scanner dihentikan.");
        }).catch(err => {
          console.error("Gagal menghentikan scanner:", err);
        });
      },
      (errorMessage) => {
        // console.log("Scan error:", errorMessage);
      }
    );

    document.getElementById("barcodeForm").addEventListener("submit", function(event) {
      const barcode = document.getElementById("barcode").value;
      if (!barcode) {
        event.preventDefault();
        alert("❗ Tidak ada barcode yang dipindai.");
      }
    });
  </script>

</body>
</html>
