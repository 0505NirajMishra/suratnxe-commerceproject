<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/Surat_nx_logo_texts.png') }}">
    <title>@yield('title', 'Surat NX – The Textile House')</title>
    <meta name="description" content="Premium fabrics from Surat. Shop designer fabrics by the metre.">
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}"> 
   

    {{-- <link rel="stylesheet" href="https://lanky-ambulance-dosage.ngrok-free.dev/css/style.css?v={{ filemtime(public_path('css/style.css')) }}">
    <link rel="stylesheet" href="https://lanky-ambulance-dosage.ngrok-free.dev/css/admin.css"> --}}

    <style>
        .flash {
            max-width: 1200px;
            margin: 14px auto;
            padding: 12px 16px;
            border: 1px solid #c9b38e;
            background: #fbf5e9;
            color: #352b1f;
            border-radius: 5px
        }

        .flash.error {
            border-color: #c66;
            background: #fff0f0;
            color: #8a2020
        }

        .field {
            display: grid;
            gap: 6px;
            margin: 12px 0
        }

        .field label {
            font-weight: 600;
            font-size: 13px
        }

        .field input,
        .field select,
        .field textarea,
        select,
        input[type=text],
        input[type=email],
        input[type=password],
        input[type=number],
        input[type=tel],
        input[type=date],
        textarea {
            max-width: 100%;
            padding: 11px 12px;
            border: 1px solid #d8d0c4;
            border-radius: 4px;
            background: #fff;
            color: #201c16
        }

        .form-card,
        .panel-card {
            background: #fff;
            border: 1px solid #e5ded2;
            padding: 22px;
            border-radius: 6px
        }

        .table-wrap {
            overflow: auto;
            background: white;
            border: 1px solid #e5ded2
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #eee6da;
            text-align: left;
            font-size: 14px
        }

        .data-table th {
            background: #f7f2e9
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            background: #f3ead9;
            border-radius: 99px;
            font-size: 12px
        }

        .two-col {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px
        }

        .product-img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            background: #eee
        }

        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 35px
        }

        .admin-main {
            padding: 28px;
            min-width: 0
        }

        .admin-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 22px
        }

        .admin-main h1 {
            font-family: var(--serif);
            font-size: 30px
        }

        .admin-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px
        }

        .stat-card {
            background: white;
            border: 1px solid #e8dfd1;
            padding: 20px;
            border-radius: 6px
        }

        .stat-card span {
            display: block;
            color: #786e60;
            font-size: 13px
        }

        .stat-card strong {
            display: block;
            font-size: 26px;
            margin-top: 8px
        }

        .admin-content {
            background: #fff;
            border: 1px solid #e8dfd1;
            border-radius: 6px;
            padding: 20px
        }

        .admin-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px
        }

        .admin-form-grid .full {
            grid-column: 1/-1
        }

        .pagination {
            display: flex;
            gap: 8px;
            margin: 20px 0;
            flex-wrap: wrap
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ded4c4;
            border-radius: 4px
        }

        .muted {
            color: #766d61
        }

        .btn:disabled {
            opacity: .5;
            cursor: not-allowed
        }

        @media(max-width:800px) {

            .two-col,
            .product-layout {
                grid-template-columns: 1fr
            }

            .admin-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr))
            }

            .admin-main {
                padding: 16px
            }

            .admin-form-grid {
                grid-template-columns: 1fr
            }
        }

        @media(max-width:480px) {
            .admin-grid {
                grid-template-columns: 1fr 1fr
            }

            .form-card,
            .panel-card {
                padding: 15px
            }
        }

        /* =========================================
         product images — 6 LEFT + 1 MAIN + 4 BOTTOM
        ========================================= */


            .product-gallery.gal {
                --thumb-size: 110px;
                --gallery-gap: 10px;

                display: grid;
                grid-template-columns: var(--thumb-size) minmax(0, 1fr);
                grid-template-rows: auto auto;
                column-gap: 14px;
                row-gap: 0px;
                width: 100%;
                min-width: 0;
                align-items: start;
                box-sizing: border-box;
            }

            /* MAIN IMAGE — Fixed size, left column empty rahegi agar thumbnails nahi hain */
            .product-gallery.gal > .mainimg {
                grid-column: 2;
                grid-row: 1;
                position: relative;
                width: 100%;
                aspect-ratio: 1 / 1;
                min-width: 0;
                margin: 0;
                padding: 0;
                overflow: hidden;
            }

            .product-gallery.gal > .mainimg > img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            /* LEFT THUMBNAILS — SIX IMAGES */
            .product-gallery.gal > .thumbs-side {
                grid-column: 1;
                grid-row: 1 / span 2;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                height: 100%; /* <--- Yeh main change hai jo left side ki height ko cover karega */
                gap: var(--gallery-gap);
                width: var(--thumb-size);
                margin: 0;
                padding: 0;
                align-self: start;
            }

            /* LEFT THUMBNAIL BUTTONS — Strictly Square */
            .product-gallery.gal > .thumbs-side button {
                display: block;
                width: 100%;
                height: auto;
                flex: 1; /* <--- Yeh thumbnails ko vertically stretch karke space fill kar dega */
                min-width: 0;
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                overflow: hidden;
                border: 1px solid #e5ddd2;
                border-radius: 0;
                background: #fff;

            }

            .product-gallery.gal .thumbs button img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center;
            }

            .product-gallery.gal .thumbs button.on {
                border: 2px solid #92754f;
            }

            /* BOTTOM THUMBNAILS — FOUR IN ONE ROW */
            .product-gallery.gal > .thumbs-bottom {
                grid-column: 2;
                grid-row: 2;
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                width: 100%;
                min-width: 0;
                margin-top: 10px;
                padding: 0;
                align-content: start;
            }

            /* Keep bottom thumbnails exactly square */
            .product-gallery.gal > .thumbs-bottom button {
                height: auto;
                aspect-ratio: 1 / 1;
            }

            /* ZOOM BUTTON */
            .product-gallery.gal > .mainimg > .zoom {
                position: absolute;
                right: 16px;
                bottom: 16px;
                z-index: 2;

                display: flex;
                align-items: center;
                justify-content: center;

                width: 46px;
                height: 46px;
                padding: 0;

                border: 0;
                border-radius: 50%;
                background: #17130f;
                color: #fff;
                cursor: pointer;
            }

            /* DESKTOP */
            @media (min-width: 1200px) {
                .product-gallery.gal {
                    --thumb-size: 110px;
                    --gallery-gap: 12px;
                }
            }

            /* TABLET */
            @media (max-width: 1024px) {
                .product-gallery.gal {
                    --thumb-size: 76px;
                    --gallery-gap: 8px;
                }

                .product-gallery.gal > .thumbs-bottom {
                    grid-template-columns: repeat(4, var(--thumb-size));
                }
            }

            /* MOBILE */
            @media (max-width: 600px) {
                .product-gallery.gal {
                    --thumb-size: 52px;
                    --gallery-gap: 5px;
                }

                .product-gallery.gal.single-image-layout {
                    grid-template-columns: 1fr !important;
                }

                .product-gallery.gal.single-image-layout > .mainimg {
                    grid-column: 1 !important;
                }

                .product-gallery.gal > .thumbs-bottom {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 6px; /* <--- Mobile view ke liye proper gap taaki images aapas mein na chipke */
                }

                .product-gallery.gal > .mainimg > .zoom {
                    right: 8px;
                    bottom: 8px;
                    width: 34px;
                    height: 34px;
                }
            }
            
            /* MOBILE VIEW FIX FOR BOTTOM THUMBNAILS */
            @media (max-width: 600px) {
                .product-gallery.gal > .thumbs-bottom {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 6px;
                    background: transparent !important; /* Grey background hatane ke liye */
                    margin-top: 8px;
                    padding: 0;
                }

                .product-gallery.gal > .thumbs-bottom button {
                    height: auto !important;
                    aspect-ratio: 1 / 1 !important;
                    background: #fff;
                    border: 1px solid #e5ddd2;
                    padding: 0;
                    margin: 0;
                }

                .product-gallery.gal > .thumbs-bottom button img {
                    display: block;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
            }

            .pw {
                position: relative;
                display: flex;
                align-items: center;
            }

            .pw input {
                width: 100%;
                padding-right: 54px;
            }

            .pw .password-toggle {
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                display: flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                padding: 0;
                background: transparent;
                border: 0;
                color: #80684f;
                cursor: pointer;
                z-index: 2;
            }

            .pw .password-toggle svg {
                width: 20px;
                height: 20px;
                flex-shrink: 0;
            }

            .pw .password-toggle svg[hidden] {
                display: none !important;
            }

            .field-error-message {
                display: block !important;
                width: 100%;
                margin: 6px 0 0;
                color: #dc2626;
                font-size: 13px;
                line-height: 1.5;
                clear: both;
            }

            .field .pw {
                position: relative;
                display: block;
                width: 100%;
            }

            .field .pw input {
                display: block;
                width: 100%;
                box-sizing: border-box;
                padding-right: 58px;
            }

            .field .pw .password-toggle {
                position: absolute;
                top: 50%;
                right: 8px;
                transform: translateY(-50%);
                display: flex;
                align-items: center;
                justify-content: center;
                width: 42px;
                height: 42px;
                padding: 0;
                background: transparent;
                border: none;
                color: #80684f;
                cursor: pointer;
                z-index: 5;
            }

            .field .pw .password-toggle svg {
                display: block;
                width: 20px;
                height: 20px;
                flex-shrink: 0;
            }

            .field .pw .password-toggle svg[hidden] {
                display: none !important;
            }

            .field .pw .password-toggle:focus-visible {
                outline: 2px solid #a77a48;
                outline-offset: 2px;
            }


            .account-password-wrapper {
                position: relative;
                display: block;
                width: 100%;
            }

            .account-password-wrapper input {
                display: block;
                width: 100%;
                box-sizing: border-box;
                padding-right: 58px;
            }

            .account-password-eye {
                position: absolute;
                top: 50%;
                right: 8px;
                transform: translateY(-50%);
                width: 42px;
                height: 42px;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                background: transparent;
                border: 0;
                color: #80684f;
                cursor: pointer;
                z-index: 5;
            }

            .account-password-eye svg {
                display: block;
                width: 20px;
                height: 20px;
                flex-shrink: 0;
                pointer-events: none;
            }

            .account-password-eye:focus-visible {
                outline: 2px solid #a77a48;
                outline-offset: 2px;
            }

    </style>
</head>

<body>
    @include('partials.header-dynamic')
    @if (session('success'))
        <div id="toasts" aria-live="polite">
            <div class="toast" role="status"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="m5 12 4 4L19 6" />
                </svg><span>{{ session('success') }}</span></div>
        </div>
    @endif
    <script>
        window.laravelValidationErrors = @json($errors->messages());
    </script>
    @yield('content')
    @include('partials.footer-dynamic')
    <script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}" defer></script>
    {{-- <script src="https://lanky-ambulance-dosage.ngrok-free.dev/js/app.js?v={{ filemtime(public_path('js/app.js')) }}" defer></script> --}}
</body>

</html>
