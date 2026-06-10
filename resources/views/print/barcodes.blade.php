<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>طباعة ملصقات الباركود</title>
    <style>
        /* Reset margins and paddings for print */
        @page {
            margin: 0;
            size: {{ $width }}mm {{ $height }}mm;
        }
        
        html, body {
            margin: 0;
            padding: 0;
            width: {{ $width }}mm;
            height: {{ $height }}mm;
            overflow: hidden;
            font-family: 'JannaLT', sans-serif;
            background-color: white;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Label Container */
        .label-container {
            width: {{ $width }}mm;
            height: {{ $height }}mm;
            box-sizing: border-box;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            page-break-after: always;
            position: relative;
        }

        /* Element styles matching recommended specifications */
        .product-name {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            line-height: 1.2;
            word-break: break-word;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            width: 100%;
            margin-top: 1mm;
        }

        .product-price {
            font-size: 15px; /* Recommended 14px - 18px Bold */
            font-weight: 800;
            color: #000;
            margin: auto 0;
        }

        .barcode-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-bottom: 1mm;
        }

        .barcode-svg {
            max-width: 95%;
            height: auto;
            max-height: 10mm; /* Constrain barcode height to fit the label size */
        }

        .barcode-number {
            font-size: 9px; /* Recommended 8px - 10px */
            font-family: monospace;
            font-weight: bold;
            color: #000;
            margin-top: 0.5mm;
        }

        /* Screen-only preview helper to guide user */
        @media screen {
            body {
                background-color: #f3f4f6;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 20px;
                padding: 30px;
                overflow: auto;
                height: auto;
                width: auto;
            }
            .no-print {
                display: flex;
                gap: 10px;
                margin-bottom: 10px;
            }
            .print-btn {
                padding: 12px 24px;
                background-color: #f59e0b; /* primary Amber color */
                color: #1f2937;
                border: none;
                border-radius: 8px;
                font-weight: 800;
                font-size: 14px;
                cursor: pointer;
                box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                transition: background-color 0.2s;
            }
            .print-btn:hover {
                background-color: #d97706;
            }
            .close-btn {
                padding: 12px 24px;
                background-color: #e5e7eb;
                color: #374151;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                font-weight: bold;
                font-size: 14px;
                cursor: pointer;
                transition: background-color 0.2s;
            }
            .close-btn:hover {
                background-color: #d1d5db;
            }
            .label-container {
                background-color: #fff;
                border: 1px dashed #9ca3af;
                box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
                border-radius: 6px;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
    <!-- Load JsBarcode library -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
</head>
<body>

    <div class="no-print">
        <button class="print-btn" onclick="window.print()">طباعة الملصقات (Print)</button>
        <button class="close-btn" onclick="window.close()">إغلاق النافذة (Close)</button>
    </div>

    @foreach($items as $item)
        @for($i = 0; $i < $qty; $i++)
            <div class="label-container">
                <div class="product-name">{{ $item['name'] }}</div>
                <div class="product-price">{{ number_format($item['price'], 2) }} ر.س</div>
                <div class="barcode-wrapper">
                    <svg class="barcode-svg" id="barcode-{{ $item['id'] }}-{{ $i }}"
                         data-code="{{ $item['barcode'] }}"></svg>
                    <div class="barcode-number">{{ $item['barcode'] }}</div>
                </div>
            </div>
        @endfor
    @endforeach

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const barcodes = document.querySelectorAll(".barcode-svg");
            barcodes.forEach(function(svg) {
                const code = svg.getAttribute("data-code");
                if (code) {
                    try {
                        JsBarcode(svg, code, {
                            format: "CODE128",
                            width: 1.5,
                            height: 40,
                            displayValue: false, // Customized using HTML .barcode-number for size and font control
                            margin: 0
                        });
                    } catch (e) {
                        console.error("Failed to generate barcode for:", code, e);
                    }
                }
            });

            // Auto-trigger printing when page has finished rendering
            setTimeout(function() {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
