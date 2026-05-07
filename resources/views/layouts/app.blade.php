<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <!-- @yield('title') cho phép các trang con truyền tên của nó vào đây -->
    <title>@yield('title', 'Chợ Đồ Cũ')</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />

    <!-- Cấu hình màu sắc mặc định -->
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-variant": "#e5e1e7",
                        "primary-fixed": "#e1dfff",
                        "on-secondary": "#ffffff",
                        "error-container": "#ffdad6",
                        "primary": "#020037",
                        "error": "#ba1a1a",
                        "primary-fixed-dim": "#c1c1ff",
                        "on-surface-variant": "#47464f",
                        "on-error": "#ffffff",
                        "on-secondary-fixed": "#2f1400",
                        "on-error-container": "#93000a",
                        "secondary-container": "#ffab69",
                        "surface-container-lowest": "#ffffff",
                        "outline": "#777680",
                        "surface-container-low": "#f6f2f8",
                        "on-tertiary-container": "#b37c59",
                        "outline-variant": "#c8c5d0",
                        "on-tertiary-fixed": "#311300",
                        "surface-dim": "#dcd9de",
                        "on-primary": "#ffffff",
                        "secondary": "#8e4e14",
                        "tertiary-fixed": "#ffdbc7",
                        "on-primary-fixed": "#141448",
                        "surface-container-highest": "#e5e1e7",
                        "on-tertiary": "#ffffff",
                        "surface-tint": "#585990",
                        "on-secondary-container": "#783d01",
                        "secondary-fixed-dim": "#ffb780",
                        "inverse-primary": "#c1c1ff",
                        "primary-container": "#1a1a4e",
                        "surface-bright": "#fcf8fd",
                        "surface-container-high": "#eae7ec",
                        "surface-container": "#f0edf2",
                        "background": "#fcf8fd",
                        "on-tertiary-fixed-variant": "#673c1e",
                        "tertiary-container": "#391700",
                        "secondary-fixed": "#ffdcc4",
                        "inverse-on-surface": "#f3eff5",
                        "tertiary": "#150500",
                        "on-secondary-fixed-variant": "#6f3800",
                        "on-background": "#1c1b1f",
                        "inverse-surface": "#313034",
                        "tertiary-fixed-dim": "#f8b992",
                        "on-primary-container": "#8383bd",
                        "on-primary-fixed-variant": "#414176",
                        "on-surface": "#1c1b1f",
                        "surface": "#fcf8fd"
                    },
                    "fontFamily": {
                        "headline": ["Manrope"],
                        "body": ["Manrope"],
                        "label": ["Manrope"]
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Manrope', sans-serif;
        }
    </style>

    <!-- Cấu hình Vite cho Custom CSS -->
    @vite(['resources/css/custom.css'])

    <!-- Nơi các trang con có thể nhúng thêm CSS riêng nếu cần -->
    @yield('styles')
</head>

<body class="bg-surface text-on-surface min-h-screen flex flex-col">
    <!-- Lắp ghép thanh Header vào -->
    @include('components.header')

    <!-- Phần ruột thay đổi theo từng trang -->
    <main class="flex-grow flex flex-col items-center justify-center p-4 relative">
        @yield('content')
    </main>

    <!-- Lắp ghép Footer vào -->
    @include('components.footer')

    <!-- Nơi nhúng các đoạn script (Javascript) của từng trang con -->
    @yield('scripts')

</body>

</html>
