<?php include_once(__DIR__ . '/utils/index.php'); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>5Tel - Reports Import</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .slide-up {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .btn {
            @apply px-6 py-3 rounded-lg font-medium text-white transition-all duration-300 hover:shadow-md hover:-translate-y-1 flex items-center justify-center gap-2;
        }

        .btn-user {
            @apply bg-blue-600 hover:bg-blue-700;
        }

        .btn-company {
            @apply bg-purple-600 hover:bg-purple-700;
        }

        .btn-transaction {
            @apply bg-teal-600 hover:bg-teal-700;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen text-gray-900 antialiased flex flex-col">
    <!-- Header (Fixed at Top) -->
    <header class="w-full bg-white shadow-sm fixed top-0 left-0 z-10">
        <div class="container mx-auto px-4 py-4 max-w-[90vw] flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-semibold text-gray-900">5Tel</h1>
                <span class="text-gray-500">|</span>
                <span class="text-sm text-gray-500">Reports Import</span>
            </div>
        </div>
    </header>

    <!-- Main Content (Centered) -->
    <main class="flex-grow flex items-center justify-center py-16">
        <div class="flex flex-col gap-6 items-center w-full max-w-md mx-auto">
            <a href="import-xlsx.php?type=user" class="btn btn-user slide-up border border-blue-200 bg-blue-600/90 hover:bg-blue-700 hover:border-blue-300 shadow-sm hover:shadow-md transform hover:-translate-y-1 transition-all duration-300 rounded-xl px-8 py-4 text-white font-semibold flex items-center justify-start gap-4 w-full" onclick="importXLSX()">
                <i class="fas fa-users text-xl"></i>
                <span>Import Users XLSX</span>
            </a>
            <a href="import-xlsx.php?type=company" class="btn btn-company slide-up border border-purple-200 bg-purple-600/90 hover:bg-purple-700 hover:border-purple-300 shadow-sm hover:shadow-md transform hover:-translate-y-1 transition-all duration-300 rounded-xl px-8 py-4 text-white font-semibold flex items-center justify-start gap-4 w-full" onclick="importXLSX()">
                <i class="fas fa-building text-xl"></i>
                <span>Import Company XLSX</span>
            </a>
            <a href="import-xlsx.php?type=transaction" class="btn btn-transaction slide-up border border-teal-200 bg-teal-600/90 hover:bg-teal-700 hover:border-teal-300 shadow-sm hover:shadow-md transform hover:-translate-y-1 transition-all duration-300 rounded-xl px-8 py-4 text-white font-semibold flex items-center justify-start gap-4 w-full" onclick="importXLSX()">
                <i class="fas fa-exchange-alt text-xl"></i>
                <span>Import Transaction XLSX</span>
            </a>
        </div>
    </main>

    <!-- Footer (Fixed at Bottom) -->
    <footer class="w-full py-4 text-center text-sm text-gray-500 bg-gray-100 shadow-inner fixed bottom-0 left-0">
        © <?php echo date('Y'); ?> Powered by <a href="https://vortexweb.cloud" class="hover:underline" target="_blank">VortexWeb</a>.
    </footer>
</body>

</html>